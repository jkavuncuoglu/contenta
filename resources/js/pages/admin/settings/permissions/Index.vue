<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Permission, Role } from '@/types';
import { Icon } from '@iconify/vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ucWords } from '@/lib/utils';

interface Props {
    roles: Role[];
    permissions: Permission[];
}
const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { label: 'Settings', href: '/admin/settings/site' },
    { label: 'Permissions', href: '/admin/settings/permissions' },
];

// Local copies
const roles = ref<Role[]>([...props.roles]);
const allPermissions = ref<Permission[]>([...props.permissions]);

// Modal / form state
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const formErrors = ref<Record<string, string[]>>({});

interface RoleFormState {
    id: number | null;
    name: string;
    guard_name: string;
    permissions: number[];
}
const roleForm = ref<RoleFormState>({ id: null, name: '', guard_name: 'web', permissions: [] });

// Filter permissions by selected guard
const filteredPermissions = computed(() => {
    return allPermissions.value.filter(
        (p) => p.guard_name === roleForm.value.guard_name
    );
});

// Group permissions (filtered by guard)
const permissionGroups = computed(() => {
    const groups: Record<string, Permission[]> = {};
    filteredPermissions.value.forEach((p) => {
        // Support both old dot-notation and new space-separated format
        let seg: string;
        if (p.name.includes('.')) {
            // Old format: "posts.view" -> "posts"
            seg = p.name.split('.')[0] || 'general';
        } else {
            // New format: "view posts" -> "posts" (last word becomes the group)
            const parts = p.name.split(' ');
            seg = parts.length > 1 ? parts[parts.length - 1] : 'general';
        }
        const key = seg.toLowerCase();
        (groups[key] ||= []).push(p);
    });
    return Object.entries(groups)
        .sort(([a], [b]) => a.localeCompare(b))
        .map(([key, perms]) => ({
            key,
            label: key
                .replace(/[-_]/g, ' ')
                .replace(/\b\w/g, (c) => c.toUpperCase()),
            permissions: perms.sort((a, b) => a.name.localeCompare(b.name)),
        }));
});

// Watch for guard changes and filter out invalid permissions
watch(() => roleForm.value.guard_name, (newGuard, oldGuard) => {
    if (newGuard !== oldGuard) {
        // Get all valid permission IDs for the new guard
        const validPermissionIds = new Set(
            allPermissions.value
                .filter(p => p.guard_name === newGuard)
                .map(p => p.id)
        );

        // Filter out any permissions that don't belong to the new guard
        roleForm.value.permissions = roleForm.value.permissions.filter(
            id => validPermissionIds.has(id)
        );
    }
});

const resetForm = () => {
    roleForm.value = { id: null, name: '', guard_name: 'web', permissions: [] };
    formErrors.value = {};
    isEditing.value = false;
};
const openCreate = () => {
    resetForm();
    showModal.value = true;
};
const openEdit = (role: Role) => {
    // Only load permissions that match the role's guard
    const validPermissions = (role.permissions || [])
        .filter((p) => p.guard_name === role.guard_name)
        .map((p) => p.id);

    roleForm.value = {
        id: role.id,
        name: role.name,
        guard_name: role.guard_name,
        permissions: validPermissions,
    };
    formErrors.value = {};
    isEditing.value = true;
    showModal.value = true;
};
const closeModal = () => {
    if (saving.value) return;
    showModal.value = false;
    resetForm();
};

const isGroupFullySelected = (ids: number[]) =>
    ids.every((id) => roleForm.value.permissions.includes(id));
const toggleGroup = (ids: number[]) => {
    if (isGroupFullySelected(ids)) {
        roleForm.value.permissions = roleForm.value.permissions.filter(
            (id) => !ids.includes(id),
        );
    } else {
        roleForm.value.permissions = Array.from(
            new Set([...roleForm.value.permissions, ...ids]),
        );
    }
};
const togglePermission = (id: number) => {
    const list = roleForm.value.permissions;
    roleForm.value.permissions = list.includes(id)
        ? list.filter((p) => p !== id)
        : [...list, id];
};

const submit = () => {
    saving.value = true;
    formErrors.value = {};

    // Filter permissions to only include those matching the role's guard
    const validPermissionIds = new Set(
        allPermissions.value
            .filter((p) => p.guard_name === roleForm.value.guard_name)
            .map((p) => p.id)
    );
    const filteredPermissions = roleForm.value.permissions.filter((id) =>
        validPermissionIds.has(id)
    );

    const payload = {
        name: roleForm.value.name,
        guard_name: roleForm.value.guard_name,
        permissions: filteredPermissions,
    };
    const creating = roleForm.value.id === null;
    const url = creating
        ? '/admin/settings/permissions/roles'
        : `/admin/settings/permissions/roles/${roleForm.value.id}`;
    const method: 'post' | 'put' = creating ? 'post' : 'put';
    router[method](url, payload, {
        preserveScroll: true,
        onError: (e) => {
            formErrors.value = e as Record<string, string[]>;
        },
        onSuccess: () => {
            router.get(
                '/admin/settings/permissions',
                {},
                { preserveScroll: true, preserveState: false },
            );
            closeModal();
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};

const confirmDelete = (role: Role) => {
    if (role.name === 'super-admin') return;
    if (!confirm(`Delete role "${role.name}"? This action cannot be undone.`))
        return;
    router.delete(`/admin/settings/permissions/roles/${role.id}`, {
        preserveScroll: true,
        onSuccess: () =>
            router.get(
                '/admin/settings/permissions',
                {},
                { preserveScroll: true, preserveState: false },
            ),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Roles & Permissions" />
        <div class="space-y-8">
            <HeadingSmall
                title="Roles & Permissions"
                description="Create roles, assign permissions, and manage access control."
            />

            <div class="flex items-center justify-between">
                <div class="text-sm text-neutral-600 dark:text-neutral-400">
                    {{ roles.length }} role(s) •
                    {{ allPermissions.length }} permission(s)
                </div>
                <button
                    type="button"
                    @click="openCreate"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                >
                    <Icon icon="lucide:plus" class="mr-2 h-4 w-4" /> New Role
                </button>
            </div>

            <div
                v-if="!roles.length"
                class="rounded-lg border border-dashed border-neutral-300 p-10 text-center dark:border-neutral-600"
            >
                <Icon
                    icon="lucide:shield"
                    class="mx-auto mb-4 h-10 w-10 text-neutral-400"
                />
                <p class="mb-4 text-sm text-neutral-500 dark:text-neutral-400">
                    No roles have been created yet.
                </p>
                <button
                    type="button"
                    @click="openCreate"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <Icon icon="lucide:plus" class="mr-2 h-4 w-4" /> Create
                    First Role
                </button>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="flex flex-col justify-between rounded-lg border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-800"
                >
                    <div>
                        <div class="mb-3 flex items-start justify-between">
                            <div>
                                <h3
                                    class="flex items-center gap-2 text-base font-semibold text-neutral-900 dark:text-white"
                                >
                                    <Icon
                                        icon="lucide:shield"
                                        class="h-5 w-5 text-blue-500"
                                    />
                                    {{ role.name }}
                                </h3>
                                <p
                                    class="mt-1 text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    {{ role.users_count || 0 }} user<span
                                        v-if="(role.users_count || 0) !== 1"
                                        >s</span
                                    >
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    @click="openEdit(role)"
                                    class="rounded p-2 text-neutral-500 hover:bg-neutral-100 hover:text-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-200"
                                    title="Edit role"
                                >
                                    <Icon
                                        icon="lucide:pencil"
                                        class="h-4 w-4"
                                    />
                                </button>
                                <button
                                    @click="confirmDelete(role)"
                                    :disabled="role.name === 'super-admin'"
                                    class="rounded p-2 text-neutral-500 hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-900/30"
                                    title="Delete role"
                                >
                                    <Icon icon="lucide:trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex min-h-[2rem] flex-wrap gap-2">
                            <span
                                v-for="perm in (role.permissions || []).slice(
                                    0,
                                    8,
                                )"
                                :key="perm.id"
                                class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-200"
                                >{{
                                    perm.name.split('.').slice(1).join(' ') ||
                                    perm.name
                                }}</span
                            >
                            <span
                                v-if="
                                    role.permissions &&
                                    role.permissions.length > 8
                                "
                                class="rounded-full bg-neutral-200 px-2 py-0.5 text-xs font-medium text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300"
                                >+{{ role.permissions.length - 8 }} more</span
                            >
                            <span
                                v-if="
                                    !role.permissions ||
                                    !role.permissions.length
                                "
                                class="text-xs text-neutral-400 italic"
                                >No permissions</span
                            >
                        </div>
                    </div>
                    <div class="mt-4">
                        <button
                            @click="openEdit(role)"
                            class="inline-flex w-full items-center justify-center gap-1 rounded-md border border-neutral-300 px-3 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700"
                        >
                            <Icon icon="lucide:wand" class="h-4 w-4" /> Manage
                            Permissions
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 md:items-center md:p-8"
            >
                <div
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm"
                    @click="closeModal"
                />
                <div
                    class="relative z-10 w-full max-w-3xl rounded-lg bg-white p-6 shadow-xl md:p-8 dark:bg-neutral-800"
                >
                    <div class="mb-6 flex items-start justify-between">
                        <div>
                            <h2
                                class="text-lg font-semibold text-neutral-900 dark:text-white"
                            >
                                {{ isEditing ? 'Edit Role' : 'Create Role' }}
                            </h2>
                            <p
                                v-if="
                                    isEditing && roleForm.name === 'super-admin'
                                "
                                class="mt-1 text-xs text-neutral-500"
                            >
                                The super-admin role name cannot be changed.
                            </p>
                        </div>
                        <button
                            @click="closeModal"
                            class="rounded p-2 text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-700"
                        >
                            <Icon icon="lucide:x" class="h-4 w-4" />
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="space-y-8">
                        <div>
                            <label
                                for="role-name"
                                class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >Role Name</label
                            >
                            <input
                                id="role-name"
                                v-model="roleForm.name"
                                type="text"
                                :disabled="
                                    roleForm.name === 'super-admin' && isEditing
                                "
                                class="w-full rounded-md px-3 py-2 border-neutral-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                placeholder="e.g. editor"
                                required
                            />
                            <p
                                v-if="formErrors.name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ ucWords(formErrors.name[0]) }}
                            </p>
                        </div>
                        <!-- Guard Name Selector -->
                        <div>
                            <label
                                for="guard-name"
                                class="mb-1 block text-sm font-medium text-neutral-700 dark:text-neutral-300"
                                >Guard</label
                            >
                            <select
                                v-if="!isEditing"
                                id="guard-name"
                                v-model="roleForm.guard_name"
                                class="w-full rounded-md px-3 py-2 border-neutral-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                            >
                                <option value="web">Web</option>
                                <option value="api">API</option>
                            </select>
                            <div
                                v-else
                                class="w-full rounded-md px-3 py-2 border border-neutral-300 text-sm shadow-sm bg-neutral-100 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-400"
                            >
                                {{ roleForm.guard_name === 'web' ? 'Web' : 'API' }}
                            </div>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Guard type for this role. Cannot be changed after creation.
                            </p>
                            <p
                                v-if="formErrors.guard_name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ ucWords(formErrors.guard_name[0]) }}
                            </p>
                        </div>
                        <div>
                            <div class="mb-4 flex items-center justify-between">
                                <h3
                                    class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
                                >
                                    Assign Permissions
                                </h3>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="text-xs text-blue-600 hover:underline disabled:opacity-40"
                                        @click="
                                            roleForm.permissions =
                                                filteredPermissions.map((p) => p.id)
                                        "
                                        :disabled="
                                            roleForm.permissions.length ===
                                            filteredPermissions.length
                                        "
                                    >
                                        Select All
                                    </button>
                                    <button
                                        type="button"
                                        class="text-xs text-blue-600 hover:underline disabled:opacity-40"
                                        @click="roleForm.permissions = []"
                                        :disabled="
                                            roleForm.permissions.length === 0
                                        "
                                    >
                                        Clear All
                                    </button>
                                </div>
                            </div>
                            <div
                                class="custom-scroll max-h-[50vh] space-y-5 overflow-y-auto pr-1"
                            >
                                <div
                                    v-for="group in permissionGroups"
                                    :key="group.key"
                                    class="rounded-md border border-neutral-200 dark:border-neutral-700"
                                >
                                    <div
                                        class="flex items-center justify-between bg-neutral-50 px-3 py-2 dark:bg-neutral-700/50"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Icon
                                                icon="lucide:folder"
                                                class="h-4 w-4 text-neutral-500"
                                            />
                                            <span
                                                class="text-xs font-semibold tracking-wide text-neutral-700 uppercase dark:text-neutral-300"
                                                >{{ group.label }}</span
                                            >
                                        </div>
                                        <button
                                            type="button"
                                            class="text-xs text-blue-600 hover:underline"
                                            @click="
                                                toggleGroup(
                                                    group.permissions.map(
                                                        (p) => p.id,
                                                    ),
                                                )
                                            "
                                        >
                                            {{
                                                isGroupFullySelected(
                                                    group.permissions.map(
                                                        (p) => p.id,
                                                    ),
                                                )
                                                    ? 'Deselect'
                                                    : 'Select'
                                            }}
                                        </button>
                                    </div>
                                    <div class="grid gap-2 p-3 sm:grid-cols-2">
                                        <label
                                            v-for="perm in group.permissions"
                                            :key="perm.id"
                                            class="inline-flex items-center gap-2 text-xs text-neutral-700 dark:text-neutral-300"
                                        >
                                            <input
                                                type="checkbox"
                                                class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-600"
                                                :value="perm.id"
                                                :checked="
                                                    roleForm.permissions.includes(
                                                        perm.id,
                                                    )
                                                "
                                                @change="
                                                    togglePermission(perm.id)
                                                "
                                            />
                                            <span>{{
                                                ucWords(perm.name
                                                    .split('.')
                                                    .slice(1)
                                                    .join(' ') || perm.name)
                                            }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <p
                                v-if="formErrors.permissions"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ formErrors.permissions[0] }}
                            </p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="closeModal"
                                class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600"
                                :disabled="saving"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="saving"
                                class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                <Icon
                                    v-if="saving"
                                    icon="lucide:loader-2"
                                    class="h-4 w-4 animate-spin"
                                />
                                {{
                                    saving
                                        ? isEditing
                                            ? 'Saving Changes'
                                            : 'Creating Role'
                                        : isEditing
                                          ? 'Save Changes'
                                          : 'Create Role'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: rgba(100, 116, 139, 0.35);
    border-radius: 3px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.55);
}
</style>
