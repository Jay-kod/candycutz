<template>
  <div class="relative overflow-hidden rounded-3xl border border-gold/30 bg-gradient-to-br from-amber-50/50 via-white to-amber-100/30 dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-6 sm:p-8 shadow-sm dark:shadow-2xl">
    <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gold/15 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
      
      <!-- Virtual Card Preview (Left Column / 5 cols) -->
      <div class="lg:col-span-5">
        <div class="relative rounded-2xl overflow-hidden border border-gold/30 bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#0f3460] p-6 shadow-xl backdrop-blur-xl">
          <div class="flex items-center justify-between mb-4">
            <!-- Chip -->
            <div class="h-7 w-10 rounded-md bg-gradient-to-r from-amber-300 via-amber-400 to-amber-500 border border-amber-200/50 flex flex-col justify-around p-1 shadow-sm">
              <div class="h-0.5 w-full bg-amber-800/40 rounded-full"></div>
              <div class="h-0.5 w-3/4 bg-amber-800/40 rounded-full"></div>
              <div class="h-0.5 w-full bg-amber-800/40 rounded-full"></div>
            </div>

            <!-- Payout badge -->
            <span 
              class="text-[9px] uppercase tracking-wider font-bold px-2.5 py-0.5 rounded-full border bg-gold/15 border-gold/30 text-gold flex items-center gap-1"
            >
              <LockClosedIcon class="h-3 w-3" />
              <span>Salon Official Payout (Admin Managed)</span>
            </span>
          </div>

          <!-- Account Number with Copy -->
          <div class="space-y-1 mb-4">
            <span class="text-[9px] uppercase tracking-widest text-white/50 font-mono font-bold">Account Number</span>
            <div class="flex items-center justify-between bg-black/40 rounded-xl px-3.5 py-2 border border-white/10">
              <span class="font-mono text-base sm:text-xl font-bold tracking-[0.16em] text-gold">
                {{ formatAccountNumber(bankDetails.account_number) || '•••• •••• ••' }}
              </span>
              <button 
                type="button"
                @click="copyText(bankDetails.account_number)"
                class="p-1.5 text-gold hover:bg-gold/20 rounded-lg transition-all active:scale-90"
                title="Copy Account Number"
              >
                <DocumentDuplicateIcon class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Bottom Row: Account Name & Bank Name -->
          <div class="flex items-end justify-between border-t border-white/10 pt-3">
            <div class="space-y-0.5 max-w-[60%]">
              <span class="text-[8px] uppercase tracking-wider text-white/40 font-bold block">Account Holder</span>
              <p class="font-display text-xs font-bold text-white tracking-wide truncate">
                {{ bankDetails.account_name || 'Candy Cutz Luxury Grooming' }}
              </p>
            </div>
            <div class="text-right space-y-0.5 max-w-[40%]">
              <span class="text-[8px] uppercase tracking-wider text-white/40 font-bold block">Bank</span>
              <p class="text-xs font-bold text-gold tracking-wider uppercase truncate">
                {{ bankDetails.bank_name || 'Zenith Bank' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Details & Live Metrics (7 cols) -->
      <div class="lg:col-span-7 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <BuildingLibraryIcon class="h-4 w-4 text-gold" />
              <h2 class="text-xs uppercase tracking-widest font-bold text-amber-700 dark:text-gold">Official Salon Financial Details</h2>
            </div>
            <h3 class="font-display text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
              Payout & <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-400 to-amber-200">Revenue Destination</span>
            </h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-xl leading-relaxed">
              Customer transfer payments for your appointments are directed to this verified salon account. Stylists have view-only access to reference and provide these details.
            </p>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <RouterLink
              to="/barber/bank-details"
              class="inline-flex items-center gap-1.5 rounded-xl border border-gold/30 bg-gold/10 px-3.5 py-2 text-xs font-bold text-amber-800 dark:text-gold hover:bg-gold hover:text-obsidian transition-all shadow-sm"
            >
              <EyeIcon class="h-3.5 w-3.5" />
              <span>View Bank Details</span>
            </RouterLink>
          </div>
        </div>

        <!-- 3 KPI metric tiles -->
        <div class="grid grid-cols-3 gap-3">
          <div class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 p-3.5 space-y-1 shadow-sm">
            <span class="text-[9px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 block">Total Revenue</span>
            <p class="font-mono text-base sm:text-lg font-bold text-amber-700 dark:text-gold truncate">
              ₦{{ Number(totalEarnings || 0).toLocaleString() }}
            </p>
            <span class="text-[10px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1 font-medium">
              <CheckCircleIcon class="h-3 w-3 shrink-0" />
              <span>Paid Services</span>
            </span>
          </div>

          <div class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 p-3.5 space-y-1 shadow-sm">
            <span class="text-[9px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 block">Paid Bookings</span>
            <p class="font-display text-base sm:text-lg font-bold text-slate-900 dark:text-white truncate">
              {{ paidCount || 0 }}
            </p>
            <span class="text-[10px] text-slate-500 dark:text-slate-400">Services Completed</span>
          </div>

          <div class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 p-3.5 space-y-1 shadow-sm">
            <span class="text-[9px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 block">Receipts To Audit</span>
            <p class="font-display text-base sm:text-lg font-bold text-amber-700 dark:text-amber-400 truncate">
              {{ awaitingCount || 0 }}
            </p>
            <span class="text-[10px] text-amber-800 dark:text-amber-400/80 font-medium">Pending Review</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useToast } from '@/core/composables/useToast';
import {
  BuildingLibraryIcon,
  DocumentDuplicateIcon,
  CheckCircleIcon,
  EyeIcon,
  LockClosedIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  bankDetails: {
    type: Object,
    required: true,
    default: () => ({
      bank_name: 'Zenith Bank',
      account_name: 'Candy Cutz Luxury Grooming',
      account_number: '1012345678',
      is_custom: false,
    }),
  },
  totalEarnings: {
    type: Number,
    default: 0,
  },
  paidCount: {
    type: Number,
    default: 0,
  },
  awaitingCount: {
    type: Number,
    default: 0,
  },
});

const toast = useToast();

function formatAccountNumber(num) {
  if (!num) return '';
  const clean = String(num).replace(/\D/g, '');
  if (clean.length === 10) {
    return `${clean.slice(0, 3)} ${clean.slice(3, 6)} ${clean.slice(6)}`;
  }
  return clean;
}

async function copyText(text) {
  if (!text) return;
  try {
    await navigator.clipboard.writeText(String(text));
    toast.success('Account number copied to clipboard!');
  } catch {
    toast.error('Failed to copy account number');
  }
}
</script>
