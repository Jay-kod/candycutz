<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-3xl border border-theme-border bg-theme-surface p-8">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Staff Management</p>
          <h1 class="mt-3 font-display text-4xl text-admin">Barbers</h1>
          <p class="mt-2 text-sm text-ivory/50">Manage your team of barbers, their specialties, and login credentials.</p>
        </div>
        <button v-if="!showForm" @click="openCreate" class="shrink-0 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(255,255,255,0.15)] transition-all hover:scale-[1.02] hover:shadow-[0_0_30px_rgba(255,255,255,0.3)]">
          + Add New Barber
        </button>
      </div>

      <div class="grid gap-6" :class="showForm ? 'lg:grid-cols-[1fr_420px]' : 'lg:grid-cols-1'">
        <!-- Barbers List -->
        <div class="rounded-3xl border border-theme-border bg-theme-surface p-8 overflow-hidden">
          <div v-if="loading" class="flex justify-center py-12">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-admin border-t-transparent"></div>
          </div>

          <div v-else-if="barbers.length === 0" class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-ivory/10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="mt-4 text-theme-muted">No barbers found. Add your first staff member to get started.</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="barber in barbers"
              :key="barber.id"
              class="group relative flex items-center gap-4 rounded-2xl border p-5 transition-all duration-300 cursor-default"
              :class="editingBarberId === barber.id
                ? 'border-admin/40 bg-admin/5 shadow-[0_0_25px_rgba(255,255,255,0.05)]'
                : 'border-theme-border bg-theme-bg/30 hover:border-theme-border hover:bg-theme-bg/60'"
            >
              <!-- Avatar -->
              <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full bg-theme-bg flex items-center justify-center text-admin font-display text-xl border border-admin/20">
                {{ barber.name.charAt(0) }}
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-bold text-theme-text truncate">{{ barber.name }}</p>
                  <span v-if="editingBarberId === barber.id" class="rounded bg-admin/20 px-2 py-0.5 text-[10px] font-bold text-admin uppercase">Editing</span>
                  <span
                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase border"
                    :class="statusStyle(barber.status)"
                  >{{ statusLabel(barber.status) }}</span>
                </div>
                <p class="text-xs text-theme-muted truncate">{{ barber.email }}</p>
                <div class="mt-2 flex flex-wrap gap-1">
                  <span v-for="spec in barber.specialties" :key="spec" class="rounded bg-admin/10 px-2 py-0.5 text-[10px] uppercase font-bold text-admin border border-admin/20">
                    {{ spec }}
                  </span>
                  <span class="rounded bg-theme-bg px-2 py-0.5 text-[10px] font-medium text-theme-muted border border-theme-border">
                    {{ barber.experience_years }} yrs exp.
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-1 shrink-0">
                <select
                  :value="barber.status"
                  @change="changeStatus(barber, ($event.target).value)"
                  class="rounded-lg border border-theme-border bg-theme-bg px-2 py-1.5 text-[11px] text-theme-text outline-none focus:border-admin/50 cursor-pointer"
                  title="Change status"
                >
                  <option value="active">Active</option>
                  <option value="pending_approval">Pending</option>
                  <option value="suspended">Suspended</option>
                  <option value="on_leave">Not Active</option>
                </select>
                <button @click="openEdit(barber)" class="rounded-lg p-2 text-ivory/30 hover:bg-gold/10 hover:text-gold transition-colors" title="Edit Barber">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                </button>
                <button @click="deleteBarber(barber)" class="rounded-lg p-2 text-ivory/30 hover:bg-red-500/10 hover:text-red-400 transition-colors" title="Delete Barber">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Inline Create/Edit Form Panel -->
        <transition name="slide">
          <div v-if="showForm" class="rounded-3xl border border-theme-border bg-theme-surface overflow-hidden h-fit lg:sticky lg:top-6">
            <!-- Panel Header -->
            <div class="flex items-center justify-between border-b border-theme-border px-6 py-5 bg-theme-bg/30">
              <div>
                <p class="text-[10px] uppercase tracking-widest text-admin font-bold">{{ isEditing ? 'Edit' : 'New' }} Barber</p>
                <h3 class="font-display text-lg text-theme-text mt-1">{{ isEditing ? form.name || 'Editing...' : 'Add to your team' }}</h3>
              </div>
              <button @click="closeForm" class="rounded-lg p-2 text-theme-muted hover:bg-red-500/10 hover:text-red-400 transition-colors" title="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <form @submit.prevent="submitForm" class="p-6 space-y-6">
              <!-- Account Credentials -->
              <div class="space-y-4">
                <h4 class="text-xs uppercase tracking-widest text-admin/70 font-bold flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  Account Credentials
                </h4>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">Full Name</label>
                  <input v-model="form.name" required type="text" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20" placeholder="e.g. John Doe" />
                </div>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">Email Address</label>
                  <input v-model="form.email" required type="email" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20" placeholder="john@candycutz.com" />
                </div>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">{{ isEditing ? 'New Password (optional)' : 'Initial Password' }}</label>
                  <input v-model="form.password" :required="!isEditing" type="password" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20" placeholder="••••••••" />
                  <p class="text-[10px] text-theme-muted ml-1">{{ isEditing ? 'Leave blank to keep current password.' : 'They will use this to log into their Barber Dashboard.' }}</p>
                </div>
              </div>

              <div class="border-t border-dashed border-theme-border"></div>

              <!-- Professional Details -->
              <div class="space-y-4">
                <h4 class="text-xs uppercase tracking-widest text-admin/70 font-bold flex items-center gap-2">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                  Professional Details
                </h4>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">Years of Experience</label>
                  <input v-model.number="form.experience_years" required type="number" min="0" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20" placeholder="e.g. 5" />
                </div>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">Specialties (comma separated)</label>
                  <input v-model="form.specialties" type="text" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20" placeholder="Fades, Beard Trims, Lineups" />
                </div>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">Short Bio</label>
                  <textarea v-model="form.bio" rows="3" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20 resize-none" placeholder="A brief description about this barber..."></textarea>
                </div>
                <div class="space-y-1.5">
                  <label class="text-xs text-theme-muted ml-1">Status</label>
                  <select v-model="form.status" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/20">
                    <option value="active">Active</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="suspended">Suspended</option>
                    <option value="on_leave">Not Active</option>
                  </select>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3 pt-2">
                <button type="button" @click="closeForm" class="flex-1 rounded-xl bg-theme-bg py-3 text-sm font-bold text-theme-text border border-theme-border hover:bg-theme-border transition-colors">
                  Cancel
                </button>
                <button type="submit" :disabled="submitting" class="flex-1 rounded-xl bg-gradient-to-r from-admin to-admin-light py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(255,255,255,0.2)] disabled:opacity-50">
                  {{ submitting ? (isEditing ? 'Saving...' : 'Creating...') : (isEditing ? 'Save Changes' : 'Create Barber') }}
                </button>
              </div>
            </form>
          </div>
        </transition>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();

const barbers = ref([]);
const loading = ref(true);
const showForm = ref(false);
const submitting = ref(false);
const isEditing = ref(false);
const editingBarberId = ref(null);

const statusStyles = {
  active: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
  pending_approval: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
  suspended: 'bg-red-500/10 text-red-400 border-red-500/20',
  on_leave: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
};
const statusLabels = {
  active: 'Active',
  pending_approval: 'Pending',
  suspended: 'Suspended',
  on_leave: 'Not Active',
};

function statusStyle(s) { return statusStyles[s] || statusStyles.active; }
function statusLabel(s) { return statusLabels[s] || s; }

const form = ref({
  name: '',
  email: '',
  password: '',
  experience_years: '',
  specialties: '',
  bio: '',
  status: 'pending_approval'
});

async function loadBarbers() {
  loading.value = true;
  try {
    const res = await adminApi.barbers();
    barbers.value = res.data.data || [];
  } catch (error) {
    toast.error('Failed to load barbers');
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  isEditing.value = false;
  editingBarberId.value = null;
  form.value = {
    name: '',
    email: '',
    password: '',
    experience_years: '',
    specialties: '',
    bio: '',
    status: 'pending_approval'
  };
  showForm.value = true;
}

function openEdit(barber) {
  isEditing.value = true;
  editingBarberId.value = barber.id;
  form.value = {
    name: barber.name || '',
    email: barber.email || '',
    password: '',
    experience_years: barber.experience_years || 0,
    specialties: (barber.specialties || []).join(', '),
    bio: barber.bio || '',
    status: barber.status || 'active'
  };
  showForm.value = true;
}

function closeForm() {
  showForm.value = false;
  editingBarberId.value = null;
}

async function submitForm() {
  submitting.value = true;
  try {
    if (isEditing.value) {
      await adminApi.updateBarber(editingBarberId.value, form.value);
      toast.success('Barber updated successfully!');
    } else {
      await adminApi.createBarber(form.value);
      toast.success('Barber created successfully!');
    }
    closeForm();
    await loadBarbers();
  } catch (error) {
    if (error.response?.status === 409) {
      toast.error('A user with this email already exists');
    } else {
      toast.error(error.response?.data?.error || `Failed to ${isEditing.value ? 'update' : 'create'} barber`);
    }
  } finally {
    submitting.value = false;
  }
}

async function deleteBarber(barber) {
  const ok = await confirm({
    title: 'Delete Barber',
    message: `Are you sure you want to permanently remove ${barber.name}? Their login account will also be deleted. This action cannot be undone.`,
    confirmText: 'Delete Barber'
  });

  if (!ok) return;

  try {
    await adminApi.deleteBarber(barber.id);
    toast.success('Barber removed successfully');
    if (editingBarberId.value === barber.id) closeForm();
    await loadBarbers();
  } catch (error) {
    toast.error(error.response?.data?.error || 'Failed to delete barber');
  }
}

async function changeStatus(barber, newStatus) {
  try {
    await adminApi.updateBarberStatus(barber.id, newStatus);
    barber.status = newStatus;
    toast.success(`${barber.name} is now ${statusLabels[newStatus]}`);
  } catch (error) {
    toast.error('Failed to update status');
    await loadBarbers();
  }
}

onMounted(() => {
  loadBarbers();
});
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.slide-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
