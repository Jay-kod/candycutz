<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/70 backdrop-blur-md" @click="$emit('close')"></div>
      <div class="relative w-full max-w-xl rounded-3xl border border-gold/30 bg-white dark:bg-[#12161C] shadow-[0_25px_60px_rgba(0,0,0,0.6)] overflow-hidden animate-slide-up">
        
        <!-- Header with gradient -->
        <div class="relative bg-slate-50 dark:bg-gradient-to-r dark:from-[#161B22] dark:via-[#0E1217] dark:to-[#161B22] border-b border-slate-200 dark:border-white/[0.08] px-8 py-5 overflow-hidden">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(212,175,55,0.08),transparent_70%)]"></div>
          <div class="relative z-10 flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
                <BuildingLibraryIcon class="h-5 w-5 text-gold" />
              </div>
              <div>
                <h3 class="font-display text-lg text-slate-900 dark:text-white font-bold">Official Salon Bank Details</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Centrally managed payment destination</p>
              </div>
            </div>
            <button @click="$emit('close')" class="text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/50 dark:hover:bg-white/[0.06] rounded-full p-2 transition-all">
              <XMarkIcon class="h-5 w-5"/>
            </button>
          </div>
        </div>

        <div class="p-8 relative space-y-6">
          <div v-if="loading" class="py-16 flex justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-2 border-gold border-t-transparent shadow-[0_0_15px_rgba(212,175,55,0.3)]"></div>
          </div>
          
          <template v-else>
            <!-- Live Bank Card Preview -->
            <div class="relative group">
              <div class="relative rounded-2xl overflow-hidden border border-gold/25 bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#0f3460] p-6 shadow-xl">
                <div class="flex justify-between items-center mb-6">
                  <div class="h-7 w-10 rounded-md bg-gradient-to-r from-amber-300 via-amber-400 to-amber-500 border border-amber-200/50 flex flex-col justify-around p-1 shadow-sm">
                    <div class="h-0.5 w-full bg-amber-800/40 rounded-full"></div>
                    <div class="h-0.5 w-3/4 bg-amber-800/40 rounded-full"></div>
                    <div class="h-0.5 w-full bg-amber-800/40 rounded-full"></div>
                  </div>

                  <span class="inline-flex items-center gap-1 text-[9px] uppercase tracking-wider font-bold px-2.5 py-0.5 rounded-full border bg-gold/15 border-gold/30 text-gold">
                    <LockClosedIcon class="h-3 w-3" />
                    <span>View-Only</span>
                  </span>
                </div>

                <div class="space-y-1 mb-6">
                  <p class="text-[9px] uppercase tracking-[0.2em] text-white/50 font-mono font-bold">Official Account Number</p>
                  <div class="flex items-center justify-between bg-black/40 rounded-xl px-3.5 py-2 border border-white/10">
                    <p class="text-lg sm:text-xl font-mono text-gold tracking-[0.16em] font-bold">
                      {{ formatAccountNumber(form.account_number) || '1012 345 678' }}
                    </p>
                    <button 
                      type="button"
                      @click="copyText(form.account_number, 'Account Number')"
                      class="p-1.5 text-gold hover:bg-gold/20 rounded-lg transition-all"
                      title="Copy Number"
                    >
                      <DocumentDuplicateIcon class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div class="flex items-end justify-between border-t border-white/10 pt-3">
                  <div class="space-y-0.5 max-w-[60%]">
                    <p class="text-[8px] uppercase tracking-wider text-white/40 font-bold">Account Holder</p>
                    <p class="text-xs font-bold text-white truncate">{{ form.account_name || 'Candy Cutz Luxury Grooming' }}</p>
                  </div>
                  <div class="text-right space-y-0.5 max-w-[40%]">
                    <p class="text-[8px] uppercase tracking-wider text-white/40 font-bold">Bank</p>
                    <p class="text-xs font-bold text-gold uppercase truncate">{{ form.bank_name || 'Zenith Bank' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Read-Only Info Fields -->
            <div class="space-y-3">
              <div class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-black/30 px-4 py-2.5">
                <div>
                  <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Bank Name</span>
                  <span class="text-xs font-semibold text-slate-900 dark:text-white">{{ form.bank_name || 'Zenith Bank' }}</span>
                </div>
                <button
                  type="button"
                  @click="copyText(form.bank_name, 'Bank Name')"
                  class="text-xs text-amber-800 dark:text-gold hover:text-gold-dark dark:hover:text-white inline-flex items-center gap-1 font-semibold"
                >
                  <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                  <span>Copy</span>
                </button>
              </div>

              <div class="flex items-center justify-between rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-black/30 px-4 py-2.5">
                <div>
                  <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Account Holder</span>
                  <span class="text-xs font-semibold text-slate-900 dark:text-white">{{ form.account_name || 'Candy Cutz Luxury Grooming' }}</span>
                </div>
                <button
                  type="button"
                  @click="copyText(form.account_name, 'Account Name')"
                  class="text-xs text-amber-800 dark:text-gold hover:text-gold-dark dark:hover:text-white inline-flex items-center gap-1 font-semibold"
                >
                  <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                  <span>Copy</span>
                </button>
              </div>
            </div>

            <!-- Stylist Notice -->
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/[0.06] p-3.5 text-xs text-slate-600 dark:text-slate-400 flex items-start gap-2.5">
              <LockClosedIcon class="h-4 w-4 text-amber-500 dark:text-amber-400 shrink-0 mt-0.5" />
              <p>
                This account is centrally configured by Salon Administration for all client payments. Stylists have view-only privileges and cannot edit it.
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-white/[0.08]">
              <RouterLink
                to="/barber/bank-details"
                @click="$emit('close')"
                class="text-xs font-bold text-amber-800 dark:text-gold hover:underline inline-flex items-center gap-1"
              >
                <span>Open Full Account Page</span>
                <span>→</span>
              </RouterLink>

              <button 
                type="button" 
                @click="$emit('close')"
                class="rounded-xl border border-slate-300 dark:border-white/10 bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.1] px-5 py-2 text-xs font-bold text-slate-700 dark:text-white transition-all"
              >
                Close
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useToast } from '@/core/composables/useToast';
import {
  XMarkIcon,
  BuildingLibraryIcon,
  DocumentDuplicateIcon,
  LockClosedIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  show: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  form: { type: Object, required: true },
});

defineEmits(['close']);

const toast = useToast();

function formatAccountNumber(num) {
  if (!num) return '';
  const clean = String(num).replace(/\D/g, '');
  if (clean.length === 10) {
    return `${clean.slice(0, 3)} ${clean.slice(3, 6)} ${clean.slice(6)}`;
  }
  return clean;
}

async function copyText(text, label = 'Account detail') {
  if (!text) return;
  try {
    await navigator.clipboard.writeText(String(text));
    toast.success(`${label} copied to clipboard!`);
  } catch {
    toast.error('Failed to copy');
  }
}
</script>
