<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in pb-12">
      <!-- Luxury Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-[var(--color-accent)]/5 blur-[100px] pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-emerald-500/5 blur-[80px] pointer-events-none"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-2.5 mb-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--color-accent)]/15 text-[var(--color-accent)] border border-[var(--color-accent)]/20 shadow-inner">
              <QrCodeIcon class="h-4 w-4" />
            </span>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold">Verification Directory</p>
          </div>
          <h1 class="font-display text-3xl lg:text-4xl text-slate-900 dark:text-white drop-shadow-sm leading-tight">
              Service <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Passcodes</span>
          </h1>
          <p class="mt-2 text-sm text-[var(--color-text-secondary)] max-w-xl leading-relaxed">
            Present these unique 6-digit verification codes to your barber upon haircut completion to authenticate your session.
          </p>
        </div>

        <button 
          @click="loadData"
          class="relative z-10 flex items-center justify-center gap-2 rounded-xl bg-slate-100 dark:bg-white/[0.04] px-4 py-2.5 text-xs font-bold text-[var(--color-text-secondary)] hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-all border border-slate-200 dark:border-white/[0.08] shrink-0"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          <span>Refresh Codes</span>
        </button>
      </div>

      <!-- Zero-CLS Skeleton State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="h-72 rounded-3xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
      </div>
      
      <!-- Codes Grid -->
      <template v-else>
        <div v-if="codes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="item in codes" 
            :key="item.id" 
            class="relative rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-xl transition-all duration-300 hover:border-[var(--color-accent)]/30 hover:-translate-y-1 group flex flex-col justify-between"
          >
            <!-- Code Top Section -->
            <div 
              class="p-6 flex flex-col items-center justify-center border-b border-slate-100 dark:border-white/[0.06] relative"
              :class="item.status === 'completed' ? 'bg-emerald-500/[0.03]' : 'bg-[var(--color-accent)]/[0.03]'"
            >
              <p 
                class="text-[10px] font-bold uppercase tracking-[0.25em] mb-3"
                :class="item.status === 'completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--color-accent)]'"
              >
                Verification Passcode
              </p>
              
              <div class="flex items-center justify-between w-full max-w-[260px] bg-slate-100 dark:bg-black/40 px-4 py-3 rounded-2xl border border-slate-200 dark:border-white/[0.08] shadow-inner group-hover:border-[var(--color-accent)]/30 transition-all">
                <QrCodeIcon class="h-5 w-5 shrink-0" :class="item.status === 'completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--color-accent)]'" />
                <span 
                  class="font-mono text-xl font-bold tracking-[0.25em] uppercase text-center flex-1"
                  :class="[
                    item.status === 'completed' ? 'text-emerald-600 dark:text-emerald-300 line-through opacity-60' : 'text-slate-900 dark:text-white'
                  ]"
                >
                  {{ item.verification_code }}
                </span>
                <button 
                  @click="copyCode(item.verification_code)"
                  class="p-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.05] hover:bg-slate-50 dark:hover:bg-white/10 transition-colors shrink-0"
                  :class="item.status === 'completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--color-accent)]'"
                  title="Copy Code"
                >
                  <DocumentDuplicateIcon class="h-4 w-4" />
                </button>
              </div>

              <!-- Status Badge -->
              <div 
                class="mt-3.5 px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold border"
                :class="item.status === 'completed' 
                  ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' 
                  : 'bg-[var(--color-accent)]/10 text-[var(--color-accent)] border-[var(--color-accent)]/30 shadow-[0_0_12px_rgba(255,103,0,0.15)]'"
              >
                {{ item.status === 'completed' ? 'Verified & Completed' : 'Active Passcode' }}
              </div>
            </div>

            <!-- Details Section -->
            <div class="p-5 space-y-4">
              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <p class="text-sm font-bold text-slate-900 dark:text-white truncate mb-1">{{ item.service_name || 'Grooming Service' }}</p>
                  <p class="text-xs text-[var(--color-text-secondary)] flex items-center gap-1.5">
                    <ClockIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
                    {{ formatDate(item.appointment_date) }} at {{ item.appointment_time }}
                  </p>
                </div>
                <div class="h-11 w-11 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] overflow-hidden shrink-0 flex items-center justify-center">
                  <img v-if="item.service_image" :src="item.service_image" class="h-full w-full object-cover" />
                  <ScissorsIcon v-else class="h-5 w-5 text-slate-400 dark:text-white/40" />
                </div>
              </div>

              <div class="pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-white/[0.05] border border-slate-200 dark:border-white/10 overflow-hidden flex items-center justify-center text-slate-700 dark:text-white/70 text-xs font-bold">
                  <img v-if="item.barber_avatar" :src="item.barber_avatar" class="h-full w-full object-cover" />
                  <span v-else>{{ initials(item.barber_name) }}</span>
                </div>
                <div>
                  <p class="text-[9px] uppercase tracking-wider text-[var(--color-text-muted)] font-bold">Assigned Specialist</p>
                  <p class="text-xs font-medium text-slate-900 dark:text-white">{{ item.barber_name || 'Assigned Barber' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-else class="p-16 text-center border border-dashed border-slate-200 dark:border-white/10 rounded-3xl bg-slate-50/50 dark:bg-white/[0.02] shadow-sm">
          <div class="h-16 w-16 mx-auto mb-4 rounded-2xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 flex items-center justify-center text-[var(--color-accent)]/60">
            <QrCodeIcon class="h-8 w-8" />
          </div>
          <h3 class="text-xl font-display text-slate-900 dark:text-white mb-2">No Active Passcodes</h3>
          <p class="text-xs text-[var(--color-text-secondary)] max-w-md mx-auto mb-6">
            You will receive a unique service verification passcode whenever your appointment is confirmed and ready for your visit.
          </p>
          <RouterLink 
            to="/customer/dashboard/services"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 transition-all"
          >
            Explore Services &rarr;
          </RouterLink>
        </div>
      </template>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';
import { 
  QrCodeIcon,
  ClockIcon,
  ScissorsIcon,
  DocumentDuplicateIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const loading = ref(true);
const codes = ref([]);

async function loadData() {
  loading.value = true;
  try {
    const res = await client.get('/customer/my-codes');
    codes.value = res.data?.data || res.data || [];
  } catch (err) {
    toast.error('Failed to load your verification codes');
  } finally {
    loading.value = false;
  }
}

function initials(name) {
  if (!name) return 'BA';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

async function copyCode(code) {
  if (!code) return;
  try {
    await navigator.clipboard.writeText(code);
    toast.success('Passcode copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy code');
  }
}

onMounted(loadData);
</script>
