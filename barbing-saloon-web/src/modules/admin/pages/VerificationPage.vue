<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#1a0f00] p-8 lg:p-10 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-emerald-500/10 blur-[80px]"></div>
        <div class="absolute top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,rgba(255,103,0,0.06),transparent_60%)]"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-3">
              <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-admin/20 text-admin border border-admin/30 shadow-[0_0_15px_rgba(255,103,0,0.2)]">
                <ShieldCheckIcon class="h-4 w-4" />
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

          <!-- Quick Verify Input -->
          <div class="relative w-full md:w-80 group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-admin/30 to-emerald-500/30 rounded-2xl blur-sm opacity-0 group-focus-within:opacity-100 transition-opacity duration-300"></div>
            <div class="relative flex items-center bg-black/40 border border-white/10 rounded-xl overflow-hidden group-focus-within:border-admin/40 transition-colors">
              <QrCodeIcon class="h-5 w-5 text-white/30 ml-4 flex-shrink-0" />
              <input
                v-model="quickCode"
                @keydown.enter="quickVerify"
                type="text"
                placeholder="Paste code to quick-verify..."
                class="flex-1 bg-transparent text-sm text-white py-3.5 px-3 outline-none placeholder:text-white/25"
              />
              <button 
                @click="quickVerify"
                class="px-4 py-2 mr-1.5 bg-emerald-500/20 text-emerald-400 text-xs font-bold rounded-lg hover:bg-emerald-500/30 transition-colors border border-emerald-500/20"
              >
                Verify
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Codes -->
        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-admin/30 hover:bg-black/40">
          <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-admin/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
              <div class="h-9 w-9 rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center">
                <HashtagIcon class="h-4 w-4 text-admin" />
              </div>
              <div class="h-6 w-6 rounded-full bg-white/5 flex items-center justify-center">
                <ChevronRightIcon class="h-3 w-3 text-white/20" />
              </div>
            </div>
            <p class="font-display text-3xl text-white mb-1">{{ stats.total }}</p>
            <p class="text-[10px] uppercase tracking-widest text-white/40 font-bold">Total Codes</p>
          </div>
        </div>

        <!-- Verified Today -->
        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-emerald-500/30 hover:bg-black/40">
          <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-500/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
              <div class="h-9 w-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                <CheckBadgeIcon class="h-4 w-4 text-emerald-400" />
              </div>
              <span v-if="stats.verified_today > 0" class="flex h-2 w-2">
                <span class="animate-ping absolute h-2 w-2 rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
            </div>
            <p class="font-display text-3xl text-emerald-400 mb-1">{{ stats.verified_today }}</p>
            <p class="text-[10px] uppercase tracking-widest text-emerald-400/50 font-bold">Verified Today</p>
          </div>
        </div>

        <!-- Pending -->
        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-amber-500/30 hover:bg-black/40">
          <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-amber-500/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
              <div class="h-9 w-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
                <ClockIcon class="h-4 w-4 text-amber-400" />
              </div>
              <span v-if="stats.pending > 0" class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20">{{ stats.pending }} waiting</span>
            </div>
            <p class="font-display text-3xl text-amber-400 mb-1">{{ stats.pending }}</p>
            <p class="text-[10px] uppercase tracking-widest text-amber-400/50 font-bold">Pending</p>
          </div>
        </div>

        <!-- Expired -->
        <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-red-500/30 hover:bg-black/40">
          <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-red-500/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
              <div class="h-9 w-9 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                <XCircleIcon class="h-4 w-4 text-red-400" />
              </div>
            </div>
            <p class="font-display text-3xl text-red-400 mb-1">{{ stats.expired }}</p>
            <p class="text-[10px] uppercase tracking-widest text-red-400/50 font-bold">Expired</p>
          </div>
        </div>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-black/30 p-3 rounded-2xl border border-white/5">
        <div class="flex items-center gap-1.5 overflow-x-auto hide-scrollbar">
          <button 
            v-for="tab in tabs" 
            :key="tab.value"
            @click="setFilter(tab.value)"
            class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap"
            :class="currentFilter === tab.value 
              ? 'bg-gradient-to-r from-admin/20 to-admin/10 text-admin border border-admin/30 shadow-[0_0_12px_rgba(255,103,0,0.15)]' 
              : 'text-white/40 hover:text-white/70 hover:bg-white/5'"
          >
            <span class="relative z-10 flex items-center gap-2">
              {{ tab.label }}
              <span v-if="tab.value === 'confirmed' && stats.pending > 0" class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
            </span>
          </button>
        </div>
        
        <div class="relative w-full md:w-80">
          <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
          <input
            v-model="search"
            @input="handleSearch"
            type="text"
            placeholder="Search code or name..."
            class="w-full bg-white/[0.03] border border-white/[0.08] text-sm text-white rounded-xl py-3 pl-11 pr-4 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05]"
          />
        </div>
      </div>

      <!-- List -->
      <div class="rounded-3xl border border-white/[0.05] bg-[#141414]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
        <!-- Loading State -->
        <div v-if="loading" class="p-16 text-center">
          <div class="inline-flex items-center gap-3 text-white/40">
            <div class="h-5 w-5 rounded-full border-2 border-white/20 border-t-admin animate-spin"></div>
            <span class="text-sm font-medium">Loading verification data...</span>
          </div>
        </div>
        
        <template v-else>
          <div v-if="verifications.length > 0" class="divide-y divide-white/[0.04]">
            <!-- Table Header -->
            <div class="hidden md:grid grid-cols-[1.5fr_1fr_1.5fr_1.2fr_auto] gap-6 px-8 py-4 text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold bg-black/40 border-b border-white/[0.05]">
              <span>Customer</span>
              <span>Barber</span>
              <span>Service Details</span>
              <span>Verification Code</span>
              <span class="text-right">Action</span>
            </div>

            <!-- Rows -->
            <div 
              v-for="(item, idx) in verifications" 
              :key="item.id" 
              class="grid grid-cols-1 md:grid-cols-[1.5fr_1fr_1.5fr_1.2fr_auto] items-center gap-4 md:gap-6 px-6 md:px-8 py-5 transition-all duration-300 hover:bg-white/[0.025] group"
              :class="{ 'animate-row-in': true }"
              :style="{ animationDelay: idx * 30 + 'ms' }"
            >
              <!-- Customer -->
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl overflow-hidden flex-shrink-0 border border-white/10 flex items-center justify-center shadow-lg"
                     :class="item.customer_avatar ? '' : 'bg-gradient-to-br from-admin/20 to-amber-500/20'">
                  <img v-if="item.customer_avatar" :src="item.customer_avatar" class="h-full w-full object-cover" />
                  <span v-else class="text-admin font-bold text-xs">{{ initials(item.customer_name) }}</span>
                </div>
                <div>
                  <p class="text-sm font-bold text-white group-hover:text-admin transition-colors">{{ item.customer_name || 'Walk-in' }}</p>
                  <p class="text-[10px] text-white/30 md:hidden font-mono">{{ item.verification_code }}</p>
                </div>
              </div>

              <!-- Barber -->
              <div class="hidden md:flex items-center gap-2">
                <div class="h-6 w-6 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 flex-shrink-0">
                  <span class="text-[9px] font-bold text-white/60">{{ initials(item.barber_name) }}</span>
                </div>
                <span class="text-sm text-white/70 font-medium">{{ item.barber_name || 'Unassigned' }}</span>
              </div>

              <!-- Details -->
              <div>
                <p class="text-sm font-semibold text-white mb-0.5">{{ item.service_name || 'Custom Service' }}</p>
                <div class="flex items-center gap-2">
                  <CalendarIcon class="h-3 w-3 text-white/30 flex-shrink-0" />
                  <p class="text-[11px] text-white/40">{{ formatDate(item.appointment_date) }} at {{ item.appointment_time }}</p>
                </div>
              </div>

              <!-- Code -->
              <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border font-mono text-sm tracking-wider font-bold transition-all duration-300"
                     :class="getCodeStyle(item.status, item.appointment_date)">
                  <CheckBadgeIcon v-if="item.status === 'completed'" class="h-4 w-4 flex-shrink-0" />
                  <ExclamationTriangleIcon v-else-if="item.status === 'cancelled'" class="h-4 w-4 flex-shrink-0" />
                  <ClockIcon v-else-if="isExpired(item.appointment_date) && item.status === 'confirmed'" class="h-4 w-4 flex-shrink-0" />
                  <QrCodeIcon v-else class="h-4 w-4 flex-shrink-0" />
                  {{ item.verification_code }}
                </div>
              </div>

              <!-- Action -->
              <div class="flex justify-end">
                <button v-if="item.status === 'confirmed' && !isExpired(item.appointment_date)" 
                        @click="confirmVerify(item)"
                        class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl text-xs font-bold transition-all duration-200 hover:shadow-[0_0_15px_rgba(16,185,129,0.2)] hover:scale-105 active:scale-95">
                  <ShieldCheckIcon class="h-3.5 w-3.5" />
                  Verify
                </button>
                <span v-else-if="item.status === 'completed'" 
                      class="flex items-center gap-1.5 text-[10px] uppercase tracking-wider text-emerald-500/70 font-bold px-3 py-1.5 bg-emerald-500/5 rounded-lg border border-emerald-500/10">
                  <CheckBadgeIcon class="h-3 w-3" />
                  Verified
                </span>
                <span v-else-if="item.status === 'cancelled'"
                      class="flex items-center gap-1.5 text-[10px] uppercase tracking-wider text-red-400/60 font-bold px-3 py-1.5 bg-red-500/5 rounded-lg border border-red-500/10">
                  <XCircleIcon class="h-3 w-3" />
                  Cancelled
                </span>
                <span v-else class="text-[10px] uppercase tracking-wider text-white/30 font-bold px-3 py-1.5 bg-white/5 rounded-lg border border-white/5">{{ item.status }}</span>
              </div>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else class="p-20 text-center">
            <div class="inline-flex items-center justify-center h-20 w-20 rounded-2xl bg-white/[0.03] border border-white/[0.05] mb-5">
              <ShieldCheckIcon class="h-10 w-10 text-white/10" />
            </div>
            <h3 class="text-lg font-bold text-white mb-2">No verification codes found</h3>
            <p class="text-sm text-white/40 max-w-sm mx-auto">Try adjusting your filters or search query to find the codes you're looking for.</p>
          </div>
        </template>
      </div>
    </section>

    <!-- Confirm Modal -->
    <BaseModal :isOpen="isModalOpen" @close="closeModal">
      <template #title>Manual Verification</template>
      <template #content>
        <div class="text-center mb-6">
          <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 mb-4">
            <ShieldCheckIcon class="h-8 w-8 text-emerald-400" />
          </div>
          <p class="text-sm text-white/70">
            You are about to manually verify this appointment. This will bypass the barber verification and mark the appointment as <strong class="text-emerald-400">completed</strong>.
          </p>
        </div>

        <div class="bg-black/30 border border-white/5 rounded-2xl p-5 mb-6">
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Customer</p>
              <p class="text-white font-semibold">{{ selectedItem?.customer_name || 'Walk-in' }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Barber</p>
              <p class="text-white font-semibold">{{ selectedItem?.barber_name || 'Unassigned' }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Service</p>
              <p class="text-white font-semibold">{{ selectedItem?.service_name }}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Verification Code</p>
              <p class="font-mono text-lg text-emerald-400 tracking-widest font-bold">{{ selectedItem?.verification_code }}</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <BaseButton variant="secondary" @click="closeModal">Cancel</BaseButton>
          <BaseButton @click="submitVerification" :isLoading="isVerifying" class="!bg-emerald-500 hover:!bg-emerald-600 !text-white !border-none !shadow-[0_0_20px_rgba(16,185,129,0.3)]">
            <ShieldCheckIcon class="h-4 w-4 mr-1" />
            Verify Now
          </BaseButton>
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
  QrCodeIcon,
  XCircleIcon,
  CalendarIcon,
  ChevronRightIcon,
  HashtagIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const loading = ref(true);
const verifications = ref([]);
const stats = ref({ total: 0, verified_today: 0, pending: 0, expired: 0 });
const currentFilter = ref('all');
const search = ref('');
const quickCode = ref('');
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

function quickVerify() {
  if (!quickCode.value.trim()) return;
  search.value = quickCode.value.trim();
  currentFilter.value = 'all';
  loadData();
}

function isExpired(dateStr) {
  if (!dateStr) return false;
  return new Date(dateStr) < new Date(new Date().setHours(0,0,0,0));
}

function getCodeStyle(status, dateStr) {
  if (status === 'completed') return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.1)]';
  if (status === 'cancelled') return 'bg-red-500/10 border-red-500/30 text-red-400 line-through opacity-60';
  if (status === 'confirmed' && isExpired(dateStr)) return 'bg-red-500/10 border-red-500/30 text-red-400 opacity-60 line-through';
  return 'bg-amber-500/10 border-amber-500/30 text-amber-400 shadow-[0_0_10px_rgba(245,158,11,0.1)]';
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

@keyframes rowIn {
  from { opacity: 0; transform: translateX(-8px); }
  to { opacity: 1; transform: translateX(0); }
}

.animate-row-in {
  animation: rowIn 0.35s ease-out both;
}

.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
