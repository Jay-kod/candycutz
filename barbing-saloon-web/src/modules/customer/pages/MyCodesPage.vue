<template>
  <CustomerLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-gold/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-emerald-500/10 blur-[80px]"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gold/20 text-gold border border-gold/30">
              <QrCodeIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-gold/80 font-bold">My Verification Codes</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Service <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-amber-400">Codes</span>
          </h1>
          <p class="mt-3 text-sm text-white/70 max-w-lg leading-relaxed">
            Provide these verification codes to your barber after your service is completed to verify delivery.
          </p>
        </div>
      </div>

      <!-- List -->
      <div class="space-y-4">
        <div v-if="loading" class="p-12 text-center text-theme-muted">Loading your codes...</div>
        
        <template v-else>
          <div v-if="codes.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="item in codes" :key="item.id" 
                 class="relative rounded-3xl border border-theme-border bg-theme-surface/80 overflow-hidden shadow-2xl transition-all hover:bg-theme-bg group">
              
              <!-- Code Section (Top) -->
              <div class="p-8 flex flex-col items-center justify-center border-b border-theme-border"
                   :class="item.status === 'completed' ? 'bg-emerald-500/5' : 'bg-gold/5'">
                <p class="text-xs font-bold uppercase tracking-[0.2em] mb-4"
                   :class="item.status === 'completed' ? 'text-emerald-500/70' : 'text-gold/70'">
                  Verification Code
                </p>
                
                <div class="flex items-center justify-center gap-3 bg-theme-bg/60 px-6 py-4 rounded-2xl border border-theme-border shadow-inner group-hover:scale-105 transition-transform relative">
                  <QrCodeIcon class="h-6 w-6" :class="item.status === 'completed' ? 'text-emerald-400' : 'text-gold'" />
                  <span class="font-mono text-2xl font-bold tracking-[0.3em] uppercase"
                        :class="[
                          item.status === 'completed' ? 'text-emerald-400' : 'text-gold',
                          item.status === 'completed' && 'opacity-50 line-through'
                        ]">
                    {{ item.verification_code }}
                  </span>
                  <button 
                    @click="copyCode(item.verification_code)"
                    class="absolute right-3 p-1.5 rounded-lg border border-theme-border hover:bg-theme-surface transition-colors"
                    :class="item.status === 'completed' ? 'text-emerald-400/50 hover:text-emerald-400' : 'text-gold/50 hover:text-gold'"
                    title="Copy Code"
                  >
                    <DocumentDuplicateIcon class="h-5 w-5" />
                  </button>
                </div>

                <!-- Status Badge -->
                <div class="mt-4 px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold"
                     :class="item.status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-gold/20 text-gold'">
                  {{ item.status === 'completed' ? 'Verified & Completed' : 'Ready to use' }}
                </div>
              </div>

              <!-- Details Section (Bottom) -->
              <div class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-bold text-theme-text mb-1">{{ item.service_name || 'Custom Service' }}</p>
                    <p class="text-xs text-theme-muted flex items-center gap-1.5">
                      <ClockIcon class="h-3.5 w-3.5" />
                      {{ formatDate(item.appointment_date) }} at {{ item.appointment_time }}
                    </p>
                  </div>
                  <div class="h-12 w-12 rounded-xl bg-theme-bg border border-theme-border overflow-hidden shrink-0">
                    <img v-if="item.service_image" :src="item.service_image" class="h-full w-full object-cover" />
                    <ScissorsIcon v-else class="h-6 w-6 text-theme-muted/50 m-3" />
                  </div>
                </div>

                <div class="pt-4 border-t border-theme-border flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-theme-bg border border-theme-border overflow-hidden flex items-center justify-center text-theme-muted text-xs">
                    <img v-if="item.barber_avatar" :src="item.barber_avatar" class="h-full w-full object-cover" />
                    <span v-else>{{ initials(item.barber_name) }}</span>
                  </div>
                  <div>
                    <p class="text-[10px] uppercase tracking-wider text-theme-muted">Barber</p>
                    <p class="text-sm font-medium text-theme-text">{{ item.barber_name || 'Unassigned' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="p-16 text-center border border-theme-border rounded-3xl bg-theme-surface/60">
            <QrCodeIcon class="h-16 w-16 text-theme-muted/30 mx-auto mb-4" />
            <h3 class="text-xl font-bold text-theme-text mb-2">No verification codes yet</h3>
            <p class="text-sm text-theme-muted max-w-md mx-auto">
              You'll receive a verification code here once your appointment payment is verified and your booking is confirmed.
            </p>
          </div>
        </template>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { customerApi } from '../api/customer.api';
import { useToast } from '../../../core/composables/useToast';
import { 
  QrCodeIcon,
  ClockIcon,
  ScissorsIcon,
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const loading = ref(true);
const codes = ref([]);

async function loadData() {
  loading.value = true;
  try {
    const res = await customerApi.myCodes();
    codes.value = res.data.data;
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
    toast.success('Code copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy code');
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
</style>
