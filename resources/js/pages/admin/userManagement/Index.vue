<template>
    <Head title="Manage Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="mb-4 text-2xl font-bold">Manage Users</h1>
            <div class="mb-4 flex items-center justify-between">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search users by name or email..."
                    class="w-1/3 rounded border px-3 py-2"
                />
            </div>
            <table
                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
            >
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                        >
                            Name
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                        >
                            Email
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                        >
                            Roles
                        </th>
                        <th
                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900"
                >
                    <tr v-for="user in filteredUsers" :key="user.id">
                        <td class="px-4 py-2">{{ user.name }}</td>
                        <td class="px-4 py-2">{{ user.email }}</td>
                        <td class="px-4 py-2 text-gray-500">
                            {{ (user.roles || []).join(', ') }}
                        </td>
                        <td class="flex gap-2 px-4 py-2">
                            <button
                                @click="editUser(user)"
                                class="rounded bg-blue-500 px-2 py-1 text-xs text-white hover:bg-blue-600"
                            >
                                Edit
                            </button>
                            <button
                                @click="archiveUser(user)"
                                class="rounded bg-yellow-500 px-2 py-1 text-xs text-white hover:bg-yellow-600"
                            >
                                Archive
                            </button>
                            <button
                                @click="deleteUser(user)"
                                class="rounded bg-red-500 px-2 py-1 text-xs text-white hover:bg-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    roles?: string[];
}
interface Props {
    users: User[];
}
const props = defineProps<Props>();
const search = ref('');

const filteredUsers = computed(() => {
    if (!search.value) return props.users;
    const term = search.value.toLowerCase();
    return props.users.filter(
        (u) =>
            u.name.toLowerCase().includes(term) ||
            u.email.toLowerCase().includes(term),
    );
});

function editUser(user: User) {
    router.visit(`/admin/userManagement/${user.id}/edit`);
}
function archiveUser(user: User) {
    if (confirm(`Archive user ${user.name}?`)) {
        // Placeholder: implement archive API call
        alert('User archived (not implemented)');
    }
}
function deleteUser(user: User) {
    if (confirm(`Delete user ${user.name}? This action cannot be undone.`)) {
        // Placeholder: implement delete API call
        alert('User deleted (not implemented)');
    }
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/admin/users',
    },
];
</script>
