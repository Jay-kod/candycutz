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
      <VerificationStats :stats="stats" />

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
      <VerificationList 
        :verifications="verifications" 
        :loading="loading" 
        @verify="confirmVerify" 
      />
    </section>

    <!-- Confirm Modal -->
    <VerificationConfirmModal 
      :isOpen="isModalOpen" 
      :item="selectedItem" 
      :isVerifying="isVerifying"
      @close="closeModal" 
      @confirm="onVerifyConfirm" 
    />
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { 
  ShieldCheckIcon,
  MagnifyingGlassIcon,
  QrCodeIcon
} from '@heroicons/vue/24/outline';
import { useVerification } from '../composables/useVerification';
import VerificationStats from '../components/VerificationStats.vue';
import VerificationList from '../components/VerificationList.vue';
import VerificationConfirmModal from '../components/VerificationConfirmModal.vue';

const {
  loading,
  isVerifying,
  verifications,
  stats,
  currentFilter,
  search,
  quickCode,
  loadData,
  handleSearch,
  setFilter,
  quickVerify,
  submitVerification
} = useVerification();

const isModalOpen = ref(false);
const selectedItem = ref(null);

const tabs = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'confirmed' },
  { label: 'Verified', value: 'completed' },
  { label: 'Expired', value: 'expired' }
];

function confirmVerify(item) {
  selectedItem.value = item;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedItem.value = null;
}

async function onVerifyConfirm(id) {
  const success = await submitVerification(id);
  if (success) {
    closeModal();
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
