<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Roles & Permissions</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Manage user roles and their permissions across the system.
        </p>
      </div>
      <button
        @click="showCreateRole = true"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <Icon icon="material-symbols-light:add" class="w-4 h-4 mr-2" />
        Create Role
      </button>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div v-for="role in roles" :key="role.id" class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <Icon icon="material-symbols-light:shield" class="w-8 h-8 text-blue-500" />
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ role.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ role.users_count || 0 }} users assigned
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="editRole(role)"
                class="p-2 text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
              >
                <Icon icon="material-symbols-light:edit" class="w-4 h-4" />
              </button>
              <button
                @click="confirmDeleteRole(role)"
                :disabled="role.name === 'super-admin'"
                class="p-2 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Icon icon="material-symbols-light:delete" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Permissions Preview -->
          <div class="space-y-3">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Permissions</h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="permission in role.permissions?.slice(0, 6)"
                :key="permission.id"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
              >
                {{ permission.name }}
              </span>
              <span
                v-if="role.permissions && role.permissions.length > 6"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
              >
                +{{ role.permissions.length - 6 }} more
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-4 flex space-x-3">
            <button
              @click="viewRoleDetails(role)"
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              View Details
            </button>
            <button
              @click="editRole(role)"
              class="flex-1 px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Edit Permissions
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Role Modal -->
    <div v-if="showCreateRole || selectedRole" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
          <div class="mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ selectedRole ? 'Edit Role' : 'Create New Role' }}
            </h3>
          </div>

          <form @submit.prevent="saveRole" class="space-y-6">
            <!-- Role Name -->
            <div>
              <label for="role-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Role Name
              </label>
              <input
                id="role-name"
                v-model="roleForm.name"
                type="text"
                required
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                placeholder="Enter role name"
              />
            </div>

            <!-- Permissions -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Permissions
              </label>
              <div class="space-y-4">
                <div v-for="group in permissionGroups" :key="group.name" class="border dark:border-gray-600 rounded-lg p-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ group.name }}</h4>
                    <button
                      type="button"
                      @click="toggleGroupPermissions(group)"
                      class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500"
                    >
                      {{ isGroupSelected(group) ? 'Deselect All' : 'Select All' }}
                    </button>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <label
                      v-for="permission in group.permissions"
                      :key="permission.id"
                      class="flex items-center"
                    >
                      <input
                        v-model="roleForm.permissions"
                        :value="permission.id"
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
                      />
                      <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ permission.display_name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
              >
                {{ loading ? 'Saving...' : (selectedRole ? 'Update Role' : 'Create Role') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import type { Role, Permission } from '@/types';
import { Icon } from '@iconify/vue';

interface PermissionGroup {
  name: string;
  permissions: Permission[];
}

interface RoleForm {
  name: string;
  permissions: number[];
}

const roles = ref<Role[]>([]);
const permissions = ref<Permission[]>([]);
const loading = ref(false);
const showCreateRole = ref(false);
const selectedRole = ref<Role | null>(null);

const roleForm = ref<RoleForm>({
  name: '',
  permissions: []
});

const permissionGroups = computed((): PermissionGroup[] => {
  const groups: { [key: string]: Permission[] } = {};

  permissions.value.forEach(permission => {
    const category = permission.name.split('.')[0] || 'General';
    const categoryName = category.charAt(0).toUpperCase() + category.slice(1);

    if (!groups[categoryName]) {
      groups[categoryName] = [];
    }
    groups[categoryName].push({
      ...permission,
      display_name: permission.name.split('.').slice(1).join(' ').replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    });
  });

  return Object.entries(groups).map(([name, perms]) => ({
    name,
    permissions: perms
  }));
});

const fetchRoles = async () => {
  try {
    loading.value = true;
    // Mock data for now
    roles.value = [
      {
        id: 1,
        name: 'super-admin',
        guard_name: 'web',
        users_count: 1,
        permissions: [
          { id: 1, name: 'users.create', guard_name: 'web', created_at: '', updated_at: '' },
          { id: 2, name: 'users.edit', guard_name: 'web', created_at: '', updated_at: '' },
          { id: 3, name: 'posts.create', guard_name: 'web', created_at: '', updated_at: '' },
        ],
        created_at: '',
        updated_at: ''
      },
      {
        id: 2,
        name: 'admin',
        guard_name: 'web',
        users_count: 3,
        permissions: [
          { id: 3, name: 'posts.create', guard_name: 'web', created_at: '', updated_at: '' },
          { id: 4, name: 'posts.edit', guard_name: 'web', created_at: '', updated_at: '' },
        ],
        created_at: '',
        updated_at: ''
      }
    ];
  } catch (error) {
    console.error('Failed to fetch roles:', error);
  } finally {
    loading.value = false;
  }
};

const fetchPermissions = async () => {
  try {
    // Mock data for now
    permissions.value = [
      { id: 1, name: 'users.create', guard_name: 'web', created_at: '', updated_at: '' },
      { id: 2, name: 'users.edit', guard_name: 'web', created_at: '', updated_at: '' },
      { id: 3, name: 'posts.create', guard_name: 'web', created_at: '', updated_at: '' },
      { id: 4, name: 'posts.edit', guard_name: 'web', created_at: '', updated_at: '' },
      { id: 5, name: 'settings.manage', guard_name: 'web', created_at: '', updated_at: '' },
    ];
  } catch (error) {
    console.error('Failed to fetch permissions:', error);
  }
};

const editRole = (role: Role) => {
  selectedRole.value = role;
  roleForm.value = {
    name: role.name,
    permissions: role.permissions?.map(p => p.id) || []
  };
};

const viewRoleDetails = async (role: Role) => {
  console.log('View role details:', role);
};

const confirmDeleteRole = (role: Role) => {
  console.log('Delete role:', role);
};

const saveRole = async () => {
  try {
    loading.value = true;
    console.log('Saving role:', roleForm.value);
    await fetchRoles();
    closeModal();
  } catch (error) {
    console.error('Failed to save role:', error);
  } finally {
    loading.value = false;
  }
};

const closeModal = () => {
  showCreateRole.value = false;
  selectedRole.value = null;
  roleForm.value = {
    name: '',
    permissions: []
  };
};

const isGroupSelected = (group: PermissionGroup): boolean => {
  return group.permissions.every(p => roleForm.value.permissions.includes(p.id));
};

const toggleGroupPermissions = (group: PermissionGroup) => {
  const groupPermissionIds = group.permissions.map(p => p.id);

  if (isGroupSelected(group)) {
    roleForm.value.permissions = roleForm.value.permissions.filter(id => !groupPermissionIds.includes(id));
  } else {
    const newPermissions = [...new Set([...roleForm.value.permissions, ...groupPermissionIds])];
    roleForm.value.permissions = newPermissions;
  }
};

onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
