<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import type { BreadcrumbItem, Role, Permission } from '@/types';
import { Icon } from '@iconify/vue';

interface Props { roles: Role[]; permissions: Permission[] }
const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
  { title: 'Settings', href: '/admin/settings/site' },
  { title: 'Permissions', href: '/admin/settings/permissions' },
];

// Local copies
const roles = ref<Role[]>([...props.roles]);
const allPermissions = ref<Permission[]>([...props.permissions]);

// Modal / form state
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const formErrors = ref<Record<string, string[]>>({});

interface RoleFormState { id: number | null; name: string; permissions: number[] }
const roleForm = ref<RoleFormState>({ id: null, name: '', permissions: [] });

// Group permissions
const permissionGroups = computed(() => {
  const groups: Record<string, Permission[]> = {};
  allPermissions.value.forEach(p => {
    const seg = p.name.split('.')[0] || 'general';
    const key = seg.toLowerCase();
    (groups[key] ||= []).push(p);
  });
  return Object.entries(groups)
    .sort(([a],[b]) => a.localeCompare(b))
    .map(([key, perms]) => ({
      key,
      label: key.replace(/[-_]/g,' ').replace(/\b\w/g, c => c.toUpperCase()),
      permissions: perms.sort((a,b) => a.name.localeCompare(b.name)),
    }));
});

const resetForm = () => { roleForm.value = { id:null, name:'', permissions:[] }; formErrors.value={}; isEditing.value=false; };
const openCreate = () => { resetForm(); showModal.value = true; };
const openEdit = (role: Role) => { roleForm.value = { id: role.id, name: role.name, permissions: (role.permissions||[]).map(p=>p.id) }; formErrors.value={}; isEditing.value=true; showModal.value=true; };
const closeModal = () => { if (saving.value) return; showModal.value=false; resetForm(); };

const isGroupFullySelected = (ids: number[]) => ids.every(id => roleForm.value.permissions.includes(id));
const toggleGroup = (ids: number[]) => {
  if (isGroupFullySelected(ids)) {
    roleForm.value.permissions = roleForm.value.permissions.filter(id => !ids.includes(id));
  } else {
    roleForm.value.permissions = Array.from(new Set([...roleForm.value.permissions, ...ids]));
  }
};
const togglePermission = (id:number) => {
  const list = roleForm.value.permissions;
  roleForm.value.permissions = list.includes(id) ? list.filter(p=>p!==id) : [...list, id];
};

const submit = () => {
  saving.value = true; formErrors.value={};
  const payload = { name: roleForm.value.name, permissions: roleForm.value.permissions };
  const creating = roleForm.value.id === null;
  const url = creating ? '/admin/settings/permissions/roles' : `/admin/settings/permissions/roles/${roleForm.value.id}`;
  const method: 'post'|'put' = creating ? 'post' : 'put';
  router[method](url, payload, {
    preserveScroll: true,
    onError: (e) => { formErrors.value = e as Record<string,string[]>; },
    onSuccess: () => { router.get('/admin/settings/permissions',{},{ preserveScroll:true, preserveState:false }); closeModal(); },
    onFinish: () => { saving.value=false; },
  });
};

const confirmDelete = (role: Role) => {
  if (role.name === 'super-admin') return;
  if (!confirm(`Delete role "${role.name}"? This action cannot be undone.`)) return;
  router.delete(`/admin/settings/permissions/roles/${role.id}`,{ preserveScroll:true, onSuccess: ()=> router.get('/admin/settings/permissions',{},{ preserveScroll:true, preserveState:false }) });
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Roles & Permissions" />
    <div class="space-y-8">
      <HeadingSmall title="Roles & Permissions" description="Create roles, assign permissions, and manage access control." />

      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">{{ roles.length }} role(s) • {{ allPermissions.length }} permission(s)</div>
        <button type="button" @click="openCreate" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          <Icon icon="lucide:plus" class="w-4 h-4 mr-2" /> New Role
        </button>
      </div>

      <div v-if="!roles.length" class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-10 text-center">
        <Icon icon="lucide:shield" class="w-10 h-10 mx-auto text-gray-400 mb-4" />
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No roles have been created yet.</p>
        <button type="button" @click="openCreate" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
          <Icon icon="lucide:plus" class="w-4 h-4 mr-2" /> Create First Role
        </button>
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="role in roles" :key="role.id" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 flex flex-col justify-between shadow-sm">
          <div>
            <div class="flex items-start justify-between mb-3">
              <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                  <Icon icon="lucide:shield" class="w-5 h-5 text-blue-500" /> {{ role.name }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ role.users_count || 0 }} user<span v-if="(role.users_count||0) !== 1">s</span></p>
              </div>
              <div class="flex items-center gap-1">
                <button @click="openEdit(role)" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" title="Edit role">
                  <Icon icon="lucide:pencil" class="w-4 h-4" />
                </button>
                <button @click="confirmDelete(role)" :disabled="role.name==='super-admin'" class="p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-500 hover:text-red-600 disabled:opacity-40 disabled:cursor-not-allowed" title="Delete role">
                  <Icon icon="lucide:trash" class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-2 min-h-[2rem]">
              <span v-for="perm in (role.permissions||[]).slice(0,8)" :key="perm.id" class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-200 text-xs font-medium rounded-full">{{ perm.name.split('.').slice(1).join(' ') || perm.name }}</span>
              <span v-if="role.permissions && role.permissions.length>8" class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-full">+{{ role.permissions.length - 8 }} more</span>
              <span v-if="!role.permissions || !role.permissions.length" class="text-xs text-gray-400 italic">No permissions</span>
            </div>
          </div>
          <div class="mt-4">
            <button @click="openEdit(role)" class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium border rounded-md border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
              <Icon icon="lucide:wand" class="w-4 h-4" /> Manage Permissions
            </button>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-50 flex items-start md:items-center justify-center p-4 md:p-8 overflow-y-auto">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal" />
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-3xl rounded-lg shadow-xl p-6 md:p-8 z-10">
          <div class="flex items-start justify-between mb-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ isEditing ? 'Edit Role' : 'Create Role' }}</h2>
              <p v-if="isEditing && roleForm.name==='super-admin'" class="text-xs text-gray-500 mt-1">The super-admin role name cannot be changed.</p>
            </div>
            <button @click="closeModal" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
              <Icon icon="lucide:x" class="w-4 h-4" />
            </button>
          </div>
          <form @submit.prevent="submit" class="space-y-8">
            <div>
              <label for="role-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role Name</label>
              <input id="role-name" v-model="roleForm.name" type="text" :disabled="roleForm.name==='super-admin' && isEditing" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="e.g. editor" required />
              <p v-if="formErrors.name" class="mt-1 text-xs text-red-600">{{ formErrors.name[0] }}</p>
            </div>
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Assign Permissions</h3>
                <div class="flex gap-2">
                  <button type="button" class="text-xs text-blue-600 hover:underline disabled:opacity-40" @click="roleForm.permissions = allPermissions.map(p=>p.id)" :disabled="roleForm.permissions.length === allPermissions.length">Select All</button>
                  <button type="button" class="text-xs text-blue-600 hover:underline disabled:opacity-40" @click="roleForm.permissions = []" :disabled="roleForm.permissions.length===0">Clear All</button>
                </div>
              </div>
              <div class="space-y-5 max-h-[50vh] overflow-y-auto pr-1 custom-scroll">
                <div v-for="group in permissionGroups" :key="group.key" class="border border-gray-200 dark:border-gray-700 rounded-md">
                  <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-700/50">
                    <div class="flex items-center gap-2">
                      <Icon icon="lucide:folder" class="w-4 h-4 text-gray-500" />
                      <span class="text-xs font-semibold tracking-wide text-gray-700 dark:text-gray-300 uppercase">{{ group.label }}</span>
                    </div>
                    <button type="button" class="text-xs text-blue-600 hover:underline" @click="toggleGroup(group.permissions.map(p=>p.id))">
                      {{ isGroupFullySelected(group.permissions.map(p=>p.id)) ? 'Deselect' : 'Select' }}
                    </button>
                  </div>
                  <div class="grid sm:grid-cols-2 gap-2 p-3">
                    <label v-for="perm in group.permissions" :key="perm.id" class="inline-flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                      <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" :value="perm.id" :checked="roleForm.permissions.includes(perm.id)" @change="togglePermission(perm.id)" />
                      <span>{{ perm.name.split('.').slice(1).join(' ') || perm.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
              <p v-if="formErrors.permissions" class="mt-2 text-xs text-red-600">{{ formErrors.permissions[0] }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600" :disabled="saving">Cancel</button>
              <button type="submit" :disabled="saving" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50">
                <Icon v-if="saving" icon="lucide:loader-2" class="w-4 h-4 animate-spin" />
                {{ saving ? (isEditing ? 'Saving Changes' : 'Creating Role') : (isEditing ? 'Save Changes' : 'Create Role') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 6px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.35); border-radius: 3px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: rgba(100,116,139,0.55); }
</style>
