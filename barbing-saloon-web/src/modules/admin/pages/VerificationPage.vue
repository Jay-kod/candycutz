<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-blue-500/10 blur-[80px]"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
              <ShieldCheckIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">Verification Center</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Code <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Verification</span>
          </h1>
          <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
            Search, track, and manually verify appointment codes to ensure seamless service delivery.
          </p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-5">
          <p class="text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2">Total Codes</p>
          <p class="font-display text-3xl text-white">{{ stats.total }}</p>
        </div>
        <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-5">
          <p class="text-[10px] uppercase tracking-widest text-emerald-400/70 font-bold mb-2">Verified Today</p>
          <p class="font-display text-3xl text-emerald-400">{{ stats.verified_today }}</p>
        </div>
        <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-5">
          <p class="text-[10px] uppercase tracking-widest text-amber-400/70 font-bold mb-2">Pending</p>
          <p class="font-display text-3xl text-amber-400">{{ stats.pending }}</p>
        </div>
        <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-5">
          <p class="text-[10px] uppercase tracking-widest text-red-400/70 font-bold mb-2">Expired</p>
          <p class="font-display text-3xl text-red-400">{{ stats.expired }}</p>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-black/20 p-4 rounded-2xl border border-white/5">
        <div class="flex items-center gap-2 overflow-x-auto hide-scrollbar">
          <button 
            v-for="tab in tabs" 
            :key="tab.value"
            @click="setFilter(tab.value)"
            class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap"
            :class="currentFilter === tab.value ? 'text-white' : 'text-white/40 hover:text-white hover:bg-white/5'"
          >
            <div v-if="currentFilter === tab.value" class="absolute inset-0 bg-white/10 border border-white/20 rounded-xl"></div>
            <span class="relative z-10">{{ tab.label }}</span>
          </button>
        </div>
        
        <div class="relative w-full md:w-80">
          <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
          <input
            v-model="search"
            @input="handleSearch"
            type="text"
            placeholder="Search code or name..."
            class="w-full bg-white/[0.03] border border-white/[0.08] text-sm text-white rounded-xl py-3 pl-11 pr-4 outline-none transition-all focus:border-admin/50"
          />
        </div>
      </div>

      <!-- List -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
        <div v-if="loading" class="p-12 text-center text-white/40">Loading...</div>
        
        <template v-else>
          <div v-if="verifications.length > 0" class="divide-y divide-white/[0.04]">
            <div class="hidden md:grid grid-cols-[1.5fr_1fr_1.5fr_1fr_auto] gap-6 px-8 py-4 text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold bg-black/40">
              <span>Customer</span>
              <span>Barber</span>
              <span>Service Details</span>
              <span>Verification Code</span>
              <span class="text-right">Action</span>
            </div>

            <div v-for="item in verifications" :key="item.id" class="grid grid-cols-1 md:grid-cols-[1.5fr_1fr_1.5fr_1fr_auto] items-center gap-4 md:gap-6 px-6 md:px-8 py-5 transition-all hover:bg-white/[0.02]">
              <!-- Customer -->
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-white/5 border border-white/10 overflow-hidden flex items-center justify-center text-white font-bold text-xs">
                  <img v-if="item.customer_avatar" :src="item.customer_avatar" class="h-full w-full object-cover" />
                  <span v-else>{{ initials(item.customer_name) }}</span>
                </div>
                <span class="text-sm font-bold text-white">{{ item.customer_name || 'Walk-in' }}</span>
              </div>

              <!-- Barber -->
              <div class="text-sm text-white/70">
                {{ item.barber_name || 'Unassigned' }}
              </div>

              <!-- Details -->
              <div>
                <p class="text-sm font-medium text-white mb-0.5">{{ item.service_name || 'Custom Service' }}</p>
                <p class="text-[11px] text-white/40 font-mono">{{ formatDate(item.appointment_date) }} at {{ item.appointment_time }}</p>
              </div>

              <!-- Code -->
              <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border font-mono text-sm tracking-widest"
                     :class="getCodeStyle(item.status, item.appointment_date)">
                  <CheckBadgeIcon v-if="item.status === 'completed'" class="h-4 w-4" />
                  <ClockIcon v-else-if="isExpired(item.appointment_date) && item.status === 'confirmed'" class="h-4 w-4" />
                  <QrCodeIcon v-else class="h-4 w-4" />
                  {{ item.verification_code }}
                </div>
              </div>

              <!-- Action -->
              <div class="flex justify-end">
                <button v-if="item.status === 'confirmed'" 
                        @click="confirmVerify(item)"
                        class="px-4 py-2 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl text-xs font-bold transition-colors">
                  Manual Verify
                </button>
                <span v-else-if="item.status === 'completed'" class="text-[10px] uppercase tracking-wider text-emerald-500/50 font-bold px-2 py-1">Verified</span>
                <span v-else class="text-[10px] uppercase tracking-wider text-white/30 font-bold px-2 py-1">{{ item.status }}</span>
              </div>
            </div>
          </div>
          
          <div v-else class="p-16 text-center">
            <ShieldCheckIcon class="h-12 w-12 text-white/10 mx-auto mb-4" />
            <h3 class="text-lg font-bold text-white mb-2">No verification codes</h3>
            <p class="text-sm text-white/40">Try adjusting your filters or search query.</p>
          </div>
        </template>
      </div>
    </section>

    <!-- Confirm Modal -->
    <BaseModal :isOpen="isModalOpen" @close="closeModal">
      <template #title>Manual Verification</template>
      <template #content>
        <p class="text-sm text-white/70 mb-4">
          Are you sure you want to manually verify this appointment? This will bypass the barber verification and mark the appointment as completed.
        </p>
        <div class="bg-black/30 border border-white/5 rounded-xl p-4 mb-6">
          <p class="text-xs text-white/40 mb-1">Verification Code</p>
          <p class="font-mono text-xl text-emerald-400 tracking-widest font-bold">{{ selectedItem?.verification_code }}</p>
        </div>
        <div class="flex justify-end gap-3">
          <BaseButton variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton @click="submitVerification" :isLoading="isVerifying" class="!bg-emerald-500 hover:!bg-emerald-600 !text-white !border-none">Verify Now</BaseButton>
        </div>
      </template>
    </BaseModal>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import BaseModal from '../../../core/components/BaseModal.vue';
import BaseButton from '../../../core/components/BaseButton.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { 
  ShieldCheckIcon,
  MagnifyingGlassIcon,
  CheckBadgeIcon,
  ClockIcon,
  QrCodeIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const loading = ref(true);
const verifications = ref([]);
const stats = ref({ total: 0, verified_today: 0, pending: 0, expired: 0 });
const currentFilter = ref('all');
const search = ref('');
let searchTimeout = null;

const isModalOpen = ref(false);
const isVerifying = ref(false);
const selectedItem = ref(null);

const tabs = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'confirmed' },
  { label: 'Verified', value: 'completed' },
  { label: 'Expired', value: 'expired' }
];

async function loadData() {
  loading.value = true;
  try {
    const [statsRes, listRes] = await Promise.all([
      adminApi.verificationStats(),
      adminApi.verifications({ filter: currentFilter.value, search: search.value })
    ]);
    stats.value = statsRes.data.data;
    verifications.value = listRes.data.data;
  } catch (err) {
    toast.error('Failed to load verification data');
  } finally {
    loading.value = false;
  }
}

function handleSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(loadData, 300);
}

function setFilter(filter) {
  currentFilter.value = filter;
  loadData();
}

function isExpired(dateStr) {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date(new Date().setHours(0,0,0,0));
}

function getCodeStyle(status, dateStr) {
  if (status === 'completed') return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400';
  if (status === 'confirmed' && isExpired(dateStr)) return 'bg-red-500/10 border-red-500/30 text-red-400 opacity-60 line-through';
  return 'bg-amber-500/10 border-amber-500/30 text-amber-400';
}

function initials(name) {
  if (!name) return 'U';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function confirmVerify(item) {
  selectedItem.value = item;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedItem.value = null;
}

async function submitVerification() {
  if (!selectedItem.value) return;
  isVerifying.value = true;
  try {
    await adminApi.verifyAppointment(selectedItem.value.id);
    toast.success('Appointment manually verified');
    closeModal();
    loadData();
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to verify appointment');
  } finally {
    isVerifying.value = false;
  }
}

onMounted(loadData);
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
