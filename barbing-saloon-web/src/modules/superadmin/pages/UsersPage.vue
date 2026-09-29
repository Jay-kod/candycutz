<template>
  <SuperAdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Command Banner -->
      <div :class="['rounded-2xl border p-8', bannerClass]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p :class="['text-sm uppercase tracking-[0.3em] font-semibold', mutedText]">Super Admin Governance</p>
            <h1 :class="['mt-2 font-display text-4xl font-bold', headingText]">User Directory</h1>
            <p :class="['mt-1 text-sm', mutedText]">Manage accounts, role assignments, and system access</p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="loadUsers"
              :disabled="loading"
              :class="['flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-200', refreshBtnClass]"
            >
              <ArrowPathIcon :class="['h-4 w-4', loading ? 'animate-spin' : '']" />
              {{ loading ? 'Updating…' : 'Refresh' }}
            </button>
          </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="mt-6 flex flex-col md:flex-row items-center gap-4">
          <div class="relative flex-1 w-full">
            <MagnifyingGlassIcon :class="['absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5', mutedText]" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, email, or username..."
              :class="['w-full rounded-xl border pl-11 pr-4 py-2.5 text-sm outline-none transition-all', inputClass]"
            />
          </div>

          <!-- Role Filter Tabs -->
          <div :class="['flex items-center rounded-xl p-1 border gap-1 self-stretch md:self-auto overflow-x-auto', tabContainerClass]">
            <button
              v-for="tab in roleTabs"
              :key="tab.value"
              @click="selectedRole = tab.value"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all',
                selectedRole === tab.value ? activeTabClass : inactiveTabClass
              ]"
            >
              {{ tab.label }}
              <span class="ml-1 opacity-70">({{ getRoleCount(tab.value) }})</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !userList.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="i in 6" :key="i" :class="['h-40 rounded-2xl animate-pulse border', skeletonClass]"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!filteredUsers.length" :class="['rounded-2xl border p-12 text-center', cardClass]">
        <UsersIcon :class="['mx-auto h-12 w-12 opacity-30', mutedText]" />
        <h3 :class="['mt-4 font-display text-xl font-bold', headingText]">No Users Found</h3>
        <p :class="['mt-1 text-sm', mutedText]">Try adjusting your search query or selected role filter.</p>
      </div>

      <!-- Users Table -->
      <div v-else :class="['rounded-2xl border overflow-hidden', cardClass]">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm whitespace-nowrap">
            <thead :class="['text-xs uppercase tracking-wider border-b', dividerClass, mutedText]">
              <tr>
                <th class="px-6 py-4 font-semibold">User</th>
                <th class="px-6 py-4 font-semibold">Role</th>
                <th class="px-6 py-4 font-semibold">Contact</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y" :class="dividerClass">
              <tr
                v-for="user in filteredUsers"
                :key="user.id"
                :class="['transition-colors group', isDark ? 'hover:bg-red-500/[0.02]' : 'hover:bg-emerald-50/30']"
              >
                <!-- User Column -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-display font-bold text-sm border', avatarClass]">
                      {{ getInitials(user.name) }}
                    </div>
                    <div class="min-w-0">
                      <div :class="['font-display font-bold leading-tight', valueText]">{{ user.name }}</div>
                      <div :class="['text-xs mt-0.5', mutedText]">{{ user.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- Role Column -->
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold border uppercase tracking-wider', getRoleBadgeClass(user.role)]">
                    {{ formatRole(user.role) }}
                  </span>
                </td>

                <!-- Contact Column -->
                <td class="px-6 py-4">
                  <span v-if="user.phone" :class="['text-xs font-mono', mutedText]">{{ user.phone }}</span>
                  <span v-else :class="['text-xs italic', mutedText]">N/A</span>
                </td>

                <!-- Status Column -->
                <td class="px-6 py-4">
                  <div :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold border uppercase tracking-wider', user.is_active ? activeBadgeClass : inactiveBadgeClass]">
                    <span :class="['h-1.5 w-1.5 rounded-full', user.is_active ? 'bg-emerald-400' : 'bg-red-400']"></span>
                    {{ user.is_active ? 'Active' : 'Suspended' }}
                  </div>
                </td>

                <!-- Actions Column -->
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                    
                    <!-- Edit User -->
                    <button
                      @click="placeholderAction('Edit User')"
                      title="Edit Profile"
                      class="p-2 rounded-lg transition-colors duration-200 hover:bg-white/10 hover:text-blue-400"
                      :class="isDark ? 'text-white/60' : 'text-slate-500 hover:text-blue-600 hover:bg-slate-100'"
                    >
                      <PencilSquareIcon class="h-4 w-4" />
                    </button>

                    <!-- Change Role -->
                    <button
                      @click="changeRolePrompt(user)"
                      title="Change Role"
                      class="p-2 rounded-lg transition-colors duration-200 hover:bg-white/10 hover:text-purple-400"
                      :class="isDark ? 'text-white/60' : 'text-slate-500 hover:text-purple-600 hover:bg-slate-100'"
                    >
                      <ShieldCheckIcon class="h-4 w-4" />
                    </button>

                    <!-- Reset Password (Always available) -->
                    <button
                      @click="resetPasswordPrompt(user)"
                      title="Reset Password"
                      class="p-2 rounded-lg transition-colors duration-200 hover:bg-white/10 hover:text-amber-400"
                      :class="isDark ? 'text-white/60' : 'text-slate-500 hover:text-amber-600 hover:bg-slate-100'"
                    >
                      <KeyIcon class="h-4 w-4" />
                    </button>

                    <div class="w-px h-4 mx-1" :class="isDark ? 'bg-white/10' : 'bg-slate-200'"></div>

                    <!-- Suspend / Unsuspend -->
                    <button
                      v-if="user.is_active"
                      @click="deactivate(user.id)"
                      :disabled="actionInProgress === user.id"
                      title="Suspend Account"
                      class="p-2 rounded-lg transition-colors duration-200 hover:bg-white/10 hover:text-red-400"
                      :class="isDark ? 'text-white/60' : 'text-slate-500 hover:text-red-600 hover:bg-slate-100'"
                    >
                      <NoSymbolIcon class="h-4 w-4" :class="actionInProgress === user.id ? 'animate-spin' : ''" />
                    </button>
                    
                    <button
                      v-else
                      @click="activate(user.id)"
                      :disabled="actionInProgress === user.id"
                      title="Unsuspend Account"
                      class="p-2 rounded-lg transition-colors duration-200 hover:bg-white/10 hover:text-emerald-400"
                      :class="isDark ? 'text-white/60' : 'text-slate-500 hover:text-emerald-600 hover:bg-slate-100'"
                    >
                      <CheckCircleIcon class="h-4 w-4" :class="actionInProgress === user.id ? 'animate-spin' : ''" />
                    </button>

                    <!-- Delete -->
                    <button
                      @click="deleteUserPrompt(user)"
                      title="Delete User"
                      class="p-2 rounded-lg transition-colors duration-200 hover:bg-red-500/20 text-red-400"
                      :class="isDark ? 'hover:text-red-300' : 'text-red-500 hover:text-red-600 hover:bg-red-50'"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useDark } from '@vueuse/core';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';
import { useToast } from '@/core/composables/useToast';
import {
  UsersIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
  PencilSquareIcon,
  KeyIcon,
  ShieldCheckIcon,
  NoSymbolIcon,
  CheckCircleIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});

const users = ref([]);
const loading = ref(true);
const actionInProgress = ref(null);
const searchQuery = ref('');
const selectedRole = ref('all');

const roleTabs = [
  { label: 'All Users', value: 'all' },
  { label: 'Admins', value: 'admin' },
  { label: 'Barbers', value: 'barber' },
  { label: 'Customers', value: 'customer' },
];

const userList = computed(() => {
  const data = users.value;
  return Array.isArray(data) ? data : (data?.data || []);
});

const filteredUsers = computed(() => {
  let list = userList.value;

  if (selectedRole.value !== 'all') {
    if (selectedRole.value === 'admin') {
      list = list.filter((u) => u.role === 'admin' || u.role === 'super_admin');
    } else {
      list = list.filter((u) => u.role === selectedRole.value);
    }
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter((u) =>
      (u.name && u.name.toLowerCase().includes(q)) ||
      (u.email && u.email.toLowerCase().includes(q)) ||
      (u.username && u.username.toLowerCase().includes(q))
    );
  }

  return list;
});

const getRoleCount = (role) => {
  const list = userList.value;
  if (role === 'all') return list.length;
  if (role === 'admin') return list.filter((u) => u.role === 'admin' || u.role === 'super_admin').length;
  return list.filter((u) => u.role === role).length;
};

const getInitials = (name) => {
  if (!name) return 'U';
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const formatRole = (role) => {
  if (role === 'super_admin') return 'Super Admin';
  if (role === 'admin') return 'Admin';
  if (role === 'barber') return 'Barber';
  if (role === 'customer') return 'Customer';
  return role || 'User';
};

async function loadUsers() {
  loading.value = true;
  try {
    const response = await superadminApi.users();
    users.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Failed to load users:', err);
    toast.error('Failed to load users');
  } finally {
    loading.value = false;
  }
}

async function activate(id) {
  actionInProgress.value = id;
  try {
    await superadminApi.activateUser(id);
    toast.success('User activated successfully');
    await loadUsers();
  } catch (err) {
    console.error('Failed to activate user:', err);
    toast.error('Failed to activate user');
  } finally {
    actionInProgress.value = null;
  }
}

async function deactivate(id) {
  actionInProgress.value = id;
  try {
    await superadminApi.deactivateUser(id);
    toast.success('User deactivated');
    await loadUsers();
  } catch (err) {
    console.error('Failed to deactivate user:', err);
    toast.error('Failed to deactivate user');
  } finally {
    actionInProgress.value = null;
  }
}

async function resetPasswordPrompt(user) {
  const newPassword = prompt(`Enter new password for ${user.name}:`);
  if (!newPassword) return; // user cancelled

  if (newPassword.length < 8) {
    toast.error('Password must be at least 8 characters');
    return;
  }

  actionInProgress.value = user.id;
  try {
    await superadminApi.resetPassword(user.id, newPassword, newPassword);
    toast.success(`Password for ${user.name} reset successfully`);
  } catch (err) {
    console.error('Failed to reset password:', err);
    toast.error(err.response?.data?.message || 'Failed to reset password');
  } finally {
    actionInProgress.value = null;
  }
}

function placeholderAction(actionName) {
  toast.info(`${actionName} feature coming soon!`);
}

function changeRolePrompt(user) {
  const newRole = prompt(`Current role is '${user.role}'. Enter new role for ${user.name} (admin, barber, customer):`);
  if (!newRole) return;
  toast.info(`Changing role to '${newRole}' is currently mocked. Feature coming soon!`);
}

function deleteUserPrompt(user) {
  const confirmDelete = confirm(`WARNING: Are you sure you want to permanently delete user ${user.name}? This action cannot be undone.`);
  if (!confirmDelete) return;
  toast.info(`Deleting user ${user.name} is currently mocked. Feature coming soon!`);
}

onMounted(loadUsers);

// ─── Theme-Aware Computed Classes (Red/Black Night vs Green/White Light) ───
const bannerClass = computed(() =>
  isDark.value
    ? 'bg-gradient-to-br from-red-950/60 via-black to-black border-red-500/20'
    : 'bg-gradient-to-br from-emerald-50 via-white to-white border-emerald-500/20'
);

const headingText = computed(() => (isDark.value ? 'text-red-400' : 'text-emerald-700'));
const valueText = computed(() => (isDark.value ? 'text-white' : 'text-slate-900'));
const mutedText = computed(() => (isDark.value ? 'text-white/50' : 'text-slate-500'));

const refreshBtnClass = computed(() =>
  isDark.value
    ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
    : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 hover:bg-emerald-500/20'
);

const inputClass = computed(() =>
  isDark.value
    ? 'bg-black/60 border-red-500/20 text-white placeholder-white/30 focus:border-red-500/60 focus:ring-1 focus:ring-red-500/40'
    : 'bg-white border-emerald-500/20 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500/40'
);

const tabContainerClass = computed(() =>
  isDark.value ? 'bg-black/60 border-red-500/20' : 'bg-slate-100 border-emerald-500/20'
);

const activeTabClass = computed(() =>
  isDark.value
    ? 'bg-red-600 text-white shadow-lg shadow-red-600/30'
    : 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
);

const inactiveTabClass = computed(() =>
  isDark.value ? 'text-white/60 hover:text-white' : 'text-slate-600 hover:text-slate-900'
);

const cardClass = computed(() =>
  isDark.value
    ? 'bg-[#0a0202]/80 border-red-500/15 hover:border-red-500/35 hover:bg-red-500/[0.03]'
    : 'bg-white border-emerald-500/15 hover:border-emerald-500/35 hover:bg-emerald-50/40 shadow-sm'
);

const skeletonClass = computed(() =>
  isDark.value ? 'bg-red-500/5 border-red-500/10' : 'bg-emerald-500/5 border-emerald-500/10'
);

const avatarClass = computed(() =>
  isDark.value
    ? 'bg-red-500/15 border-red-500/30 text-red-400'
    : 'bg-emerald-500/15 border-emerald-500/30 text-emerald-700'
);

const activeBadgeClass = computed(() =>
  isDark.value
    ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400'
    : 'bg-emerald-50 border-emerald-200 text-emerald-700'
);

const inactiveBadgeClass = computed(() =>
  isDark.value
    ? 'bg-red-500/15 border-red-500/30 text-red-400'
    : 'bg-red-50 border-red-200 text-red-700'
);

const dividerClass = computed(() =>
  isDark.value ? 'border-red-500/10' : 'border-emerald-500/10'
);

const activateBtnClass = computed(() =>
  isDark.value
    ? 'bg-emerald-600 text-white border-emerald-500 hover:bg-emerald-500 shadow-md shadow-emerald-950/40'
    : 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 shadow-sm'
);

const deactivateBtnClass = computed(() =>
  isDark.value
    ? 'bg-red-500/15 text-red-400 border-red-500/30 hover:bg-red-500/25'
    : 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100'
);

const resetBtnClass = computed(() =>
  isDark.value
    ? 'bg-amber-500/15 text-amber-400 border-amber-500/30 hover:bg-amber-500/25'
    : 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100'
);

const getRoleBadgeClass = (role) => {
  const dark = isDark.value;
  if (role === 'super_admin' || role === 'admin') {
    return dark
      ? 'bg-red-500/15 text-red-400 border-red-500/30'
      : 'bg-emerald-100 text-emerald-800 border-emerald-200';
  }
  if (role === 'barber') {
    return dark
      ? 'bg-amber-500/15 text-amber-400 border-amber-500/30'
      : 'bg-amber-50 text-amber-700 border-amber-200';
  }
  return dark
    ? 'bg-blue-500/15 text-blue-400 border-blue-500/30'
    : 'bg-blue-50 text-blue-700 border-blue-200';
};
</script>
