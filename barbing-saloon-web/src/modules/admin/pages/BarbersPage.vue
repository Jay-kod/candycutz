<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-amber-500/10 blur-[80px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.05),transparent_60%)]"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
              <ScissorsIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">Staff Management</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Barber <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Team</span>
          </h1>
          <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
            Manage your professional barbers, assign specialties, control access, and review their performance.
          </p>
        </div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4 shrink-0 w-full md:w-auto">
          <router-link to="/admin/barbers/new" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 px-6 py-3.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.3)] shrink-0 group">
            <UserPlusIcon class="h-5 w-5 group-hover:scale-110 transition-transform" />
            Add New Barber
          </router-link>
        </div>
      </div>

      <!-- Main Layout -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
        
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
          <div v-for="i in 6" :key="i" class="rounded-2xl border border-white/[0.02] bg-white/[0.01] p-6 animate-pulse">
            <div class="flex items-center gap-4 mb-4">
              <div class="h-14 w-14 rounded-full bg-white/[0.03]"></div>
              <div class="space-y-2 flex-1"><div class="h-4 w-3/4 bg-white/[0.03] rounded"></div><div class="h-3 w-1/2 bg-white/[0.02] rounded"></div></div>
            </div>
            <div class="space-y-2 mt-4"><div class="h-3 w-full bg-white/[0.02] rounded"></div><div class="h-3 w-5/6 bg-white/[0.02] rounded"></div></div>
          </div>
        </div>

        <div v-else-if="barbers.length === 0" class="flex flex-col items-center justify-center py-24 px-6 text-center">
          <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] mb-6">
            <div class="absolute inset-0 rounded-full border border-dashed border-white/10 animate-[spin_10s_linear_infinite]"></div>
            <UsersIcon class="h-8 w-8 text-white/20" />
          </div>
          <h3 class="text-xl font-bold text-white mb-2">No Barbers Found</h3>
          <p class="text-sm text-white/40 max-w-sm mx-auto mb-6">
            You haven't added any staff members yet. Add your first barber to start taking bookings.
          </p>
          <router-link to="/admin/barbers/new" class="text-admin hover:text-admin-light text-sm font-semibold transition-colors flex items-center gap-2">
            <UserPlusIcon class="h-4 w-4" /> Add your first barber
          </router-link>
        </div>

        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            
            <!-- Barber Card -->
            <div
              v-for="barber in barbers"
              :key="barber.id"
              class="group relative flex flex-col rounded-3xl border border-white/[0.05] bg-gradient-to-br from-white/[0.02] to-transparent hover:border-admin/20 hover:from-white/[0.04] transition-all duration-300 overflow-hidden"
            >
              <!-- Glowing accent line -->
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-admin to-amber-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
              
              <div class="p-6 flex-1 flex flex-col">
                <!-- Header -->
                <div class="flex items-start justify-between mb-5">
                  <div class="flex items-center gap-4">
                    <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-[1.25rem] bg-gradient-to-br from-admin/20 to-purple-500/20 flex items-center justify-center text-white font-display text-2xl border border-white/10 shadow-inner group-hover:scale-105 transition-transform">
                      <img v-if="barber.avatar" :src="barber.avatar" class="h-full w-full object-cover" />
                      <span v-else>{{ barber.name.charAt(0) }}</span>
                    </div>
                    <div>
                      <h3 class="font-bold text-white text-lg leading-tight">{{ barber.name }}</h3>
                      <div class="flex items-center gap-1.5 mt-1">
                        <span class="rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border shadow-sm" :class="statusStyle(barber.status)">
                          {{ statusLabel(barber.status) }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Quick Actions Menu -->
                  <div class="flex items-center gap-1 bg-white/5 rounded-xl p-1 border border-white/5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <router-link :to="`/admin/barbers/${barber.id}/edit`" class="p-1.5 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors" title="Edit Barber">
                      <PencilSquareIcon class="h-4 w-4" />
                    </router-link>
                    <button @click="deleteBarber(barber)" class="p-1.5 rounded-lg text-white/40 hover:text-red-400 hover:bg-red-500/20 transition-colors" title="Delete Barber">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <!-- Details -->
                <div class="space-y-3 flex-1">
                  <div class="flex items-center gap-2 text-xs text-white/50">
                    <EnvelopeIcon class="h-4 w-4 text-white/20" />
                    <span class="truncate">{{ barber.email }}</span>
                  </div>
                  <div class="flex items-start gap-2 text-xs text-white/50">
                    <AcademicCapIcon class="h-4 w-4 text-white/20 shrink-0" />
                    <span>{{ barber.experience_years }} Years Experience</span>
                  </div>
                </div>

                <!-- Specialties Tags -->
                <div class="mt-5 pt-4 border-t border-white/[0.05]">
                  <div class="flex flex-wrap gap-2">
                    <span v-for="spec in (barber.specialties || []).slice(0,3)" :key="spec" class="rounded-lg bg-white/[0.03] px-2.5 py-1 text-[10px] uppercase tracking-wider font-semibold text-white/60 border border-white/[0.05] flex items-center gap-1.5">
                      <SparklesIcon class="h-3 w-3 text-admin/70" />
                      {{ spec }}
                    </span>
                    <span v-if="(barber.specialties || []).length > 3" class="rounded-lg bg-white/[0.03] px-2.5 py-1 text-[10px] font-semibold text-white/40 border border-white/[0.05]">
                      +{{ barber.specialties.length - 3 }} more
                    </span>
                    <span v-if="!(barber.specialties || []).length" class="text-[10px] text-white/30 italic">No specialties listed</span>
                  </div>
                </div>
                
                <!-- Status Control -->
                <div class="mt-4 flex items-center justify-between bg-black/40 rounded-xl p-2 border border-white/[0.03]">
                  <span class="text-[10px] uppercase tracking-wider text-white/30 font-bold ml-2">Access Level</span>
                  <select
                    :value="barber.status"
                    @change="changeStatus(barber, ($event.target).value)"
                    class="rounded-lg border border-white/10 bg-[#1a1a1a] px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white/80 outline-none focus:border-admin/50 cursor-pointer transition-all hover:bg-white/5"
                  >
                    <option value="active">🟢 Active</option>
                    <option value="pending_approval">🟡 Pending</option>
                    <option value="suspended">🔴 Suspended</option>
                    <option value="on_leave">⚪ On Leave</option>
                  </select>
                </div>
              </div>
            </div>
            
          </div>
        </div>
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
import { 
  UserPlusIcon, 
  UsersIcon, 
  EnvelopeIcon, 
  SparklesIcon, 
  AcademicCapIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  ScissorsIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();

const barbers = ref([]);
const loading = ref(true);

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
  on_leave: 'On Leave',
};

function statusStyle(s) { return statusStyles[s] || statusStyles.active; }
function statusLabel(s) { return statusLabels[s] || s; }

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

async function deleteBarber(barber) {
  const ok = await confirm('Delete Barber', `Are you sure you want to permanently remove ${barber.name}? Their login account will also be deleted. This action cannot be undone.`);

  if (!ok) return;

  try {
    await adminApi.deleteBarber(barber.id);
    toast.success('Barber removed successfully');
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
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
