<template>
  <BarberLayout>
    <div class="max-w-6xl mx-auto space-y-8 animate-fade-in pb-16 pt-2 px-4 sm:px-6 lg:px-8">
      
      <!-- Top Hero Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-gold/20 bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-8 lg:p-10 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-gold/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <p class="text-[11px] uppercase tracking-[0.25em] font-bold text-gold">Official Salon Financial Channel</p>
            </div>
            <h1 class="font-display text-3xl lg:text-4xl font-bold tracking-tight text-slate-900 dark:text-white drop-shadow-sm">
              Official Salon <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-500 to-amber-600 dark:via-amber-300 dark:to-gold">Bank Details</span>
            </h1>
            <p class="mt-2 text-xs lg:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
              These account details are configured centrally by salon administrators for all client transfers and service settlements. Stylists have view-only access to reference and provide payment instructions to clients.
            </p>
          </div>

          <div class="flex items-center gap-3 shrink-0">
            <button
              type="button"
              @click="loadAccount"
              :disabled="loading"
              class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.04] px-4 py-3 text-xs font-semibold text-slate-700 dark:text-white/80 hover:bg-slate-100 dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white transition-all flex items-center gap-2 disabled:opacity-50"
              title="Refresh live bank details from database"
            >
              <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
              <span>Refresh</span>
            </button>
            <div class="inline-flex items-center gap-2 rounded-xl bg-amber-500/10 border border-amber-500/30 px-4 py-3 text-xs font-bold text-amber-700 dark:text-amber-300 shadow-sm">
              <LockClosedIcon class="h-4 w-4 text-amber-500 dark:text-amber-400" />
              <span>View-Only Privilege</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Column: Virtual Glass Card & Explanation (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
          <div class="relative group">
            <div class="relative rounded-3xl overflow-hidden border border-gold/30 bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#0f3460] p-8 shadow-2xl backdrop-blur-2xl transition-all duration-300 hover:scale-[1.01]">
              <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-gold/20 blur-3xl pointer-events-none"></div>

              <!-- Top Row: Chip & Contactless -->
              <div class="relative z-10 flex justify-between items-center mb-8">
                <div class="h-9 w-12 rounded-lg bg-gradient-to-r from-amber-300 via-amber-400 to-amber-500 shadow-md border border-amber-200/50 flex flex-col justify-around p-1.5 opacity-90">
                  <div class="h-0.5 w-full bg-amber-700/40 rounded-full"></div>
                  <div class="h-0.5 w-3/4 bg-amber-700/40 rounded-full"></div>
                  <div class="h-0.5 w-full bg-amber-700/40 rounded-full"></div>
                </div>

                <div class="flex items-center gap-2">
                  <span class="text-[9px] uppercase tracking-widest font-mono text-gold bg-gold/15 border border-gold/30 px-2.5 py-0.5 rounded-full font-bold">
                    Salon Payout
                  </span>
                  <svg class="h-5 w-5 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 16.5a5 5 0 010-9m3.5 11a8 8 0 000-13m3.5 15a11 11 0 000-17" />
                  </svg>
                </div>
              </div>

              <!-- Account Number -->
              <div class="relative z-10 space-y-2 mb-8">
                <p class="text-[10px] uppercase tracking-[0.25em] text-white/50 font-bold font-mono">Official Account Number</p>
                <div class="flex items-center justify-between bg-black/40 rounded-2xl px-4 py-3 border border-white/10">
                  <span class="font-mono text-xl sm:text-2xl font-bold tracking-[0.18em] text-gold drop-shadow-md">
                    {{ formatAccountNumber(details.account_number) || '•••• •••• ••' }}
                  </span>
                  <button 
                    type="button"
                    @click="copyText(details.account_number, 'Account Number')"
                    class="p-2 text-gold hover:bg-gold/20 rounded-xl transition-all active:scale-90"
                    title="Copy Account Number"
                  >
                    <DocumentDuplicateIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Bottom Row: Account Holder & Bank -->
              <div class="relative z-10 flex items-end justify-between border-t border-white/10 pt-4">
                <div class="space-y-0.5 max-w-[60%]">
                  <p class="text-[9px] uppercase tracking-wider text-white/40 font-bold">Account Holder</p>
                  <p class="font-display text-sm font-bold text-white tracking-wide truncate">
                    {{ details.account_name || 'Candy Cutz Luxury Grooming' }}
                  </p>
                </div>
                <div class="text-right space-y-0.5 max-w-[40%]">
                  <p class="text-[9px] uppercase tracking-wider text-white/40 font-bold">Bank</p>
                  <p class="text-xs font-bold text-gold tracking-wider uppercase truncate">
                    {{ details.bank_name || 'Zenith Bank' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Stylist Guidance -->
          <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-xl space-y-3 text-xs text-slate-600 dark:text-slate-400">
            <h3 class="text-xs uppercase tracking-widest font-bold text-slate-900 dark:text-white flex items-center gap-2">
              <InformationCircleIcon class="h-4 w-4 text-gold" />
              Stylist Protocol & Usage
            </h3>
            <p><strong class="text-slate-900 dark:text-white">1. Direct Transfers:</strong> Clients booking your appointments are automatically shown this verified account on their booking screen.</p>
            <p><strong class="text-slate-900 dark:text-white">2. In-Person Payments:</strong> When a walk-in or chair client requests to pay via bank transfer, you can provide this exact account number.</p>
            <p><strong class="text-slate-900 dark:text-white">3. Verification:</strong> Once the transfer is completed, verify their payment receipt in your <RouterLink to="/barber/payments" class="text-gold font-bold hover:underline">Payments Portal</RouterLink>.</p>
          </div>
        </div>

        <!-- Right Column: Read-Only Account Details Display (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
          <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-8 shadow-xl space-y-6">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.08]">
              <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                  <BuildingLibraryIcon class="h-5 w-5 text-gold" />
                  Salon Bank Account Information
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                  Live account details stored in the salon database.
                </p>
              </div>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20">
                <LockClosedIcon class="h-3 w-3" />
                Read-Only
              </span>
            </div>

            <!-- Field 1: Bank Name -->
            <div class="space-y-1.5">
              <label class="block text-[11px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">
                Bank Name
              </label>
              <div class="flex items-center justify-between rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-black/40 px-4 py-3.5">
                <span class="text-sm font-semibold text-slate-900 dark:text-white">
                  {{ details.bank_name || 'Zenith Bank' }}
                </span>
                <button
                  type="button"
                  @click="copyText(details.bank_name, 'Bank Name')"
                  class="inline-flex items-center gap-1.5 text-xs text-gold hover:text-amber-600 dark:hover:text-white transition-colors"
                >
                  <DocumentDuplicateIcon class="h-4 w-4" />
                  <span>Copy</span>
                </button>
              </div>
            </div>

            <!-- Field 2: Account Holder Name -->
            <div class="space-y-1.5">
              <label class="block text-[11px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">
                Account Holder Name
              </label>
              <div class="flex items-center justify-between rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-black/40 px-4 py-3.5">
                <span class="text-sm font-semibold text-slate-900 dark:text-white">
                  {{ details.account_name || 'Candy Cutz Luxury Grooming' }}
                </span>
                <button
                  type="button"
                  @click="copyText(details.account_name, 'Account Name')"
                  class="inline-flex items-center gap-1.5 text-xs text-gold hover:text-amber-600 dark:hover:text-white transition-colors"
                >
                  <DocumentDuplicateIcon class="h-4 w-4" />
                  <span>Copy</span>
                </button>
              </div>
            </div>

            <!-- Field 3: Account Number (NUBAN) -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block text-[11px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">
                  Account Number (NUBAN)
                </label>
                <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md">
                  Verified NUBAN
                </span>
              </div>
              <div class="flex items-center justify-between rounded-2xl border border-gold/30 bg-amber-500/5 dark:bg-black/50 px-4 py-3.5 shadow-inner">
                <span class="font-mono text-lg sm:text-xl font-bold tracking-widest text-gold">
                  {{ details.account_number || '1012345678' }}
                </span>
                <button
                  type="button"
                  @click="copyText(details.account_number, 'Account Number')"
                  class="inline-flex items-center gap-1.5 rounded-xl bg-gold/15 border border-gold/30 px-3 py-1.5 text-xs font-bold text-gold hover:bg-gold hover:text-obsidian transition-all shadow-sm active:scale-95"
                >
                  <DocumentDuplicateIcon class="h-4 w-4" />
                  <span>Copy Number</span>
                </button>
              </div>
            </div>

            <!-- Field 4: Payment Instructions -->
            <div class="space-y-1.5">
              <label class="block text-[11px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">
                Payment & Transfer Instructions
              </label>
              <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-black/40 p-4 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                {{ details.instructions || 'Please include your Booking Reference (e.g. CC-1234) in your transfer remarks to speed up instant confirmation.' }}
              </div>
            </div>

            <!-- Privilege Notice Box -->
            <div class="rounded-2xl border border-amber-500/25 bg-amber-500/[0.06] p-4 flex items-start gap-3">
              <ShieldCheckIcon class="h-5 w-5 text-amber-500 dark:text-amber-400 shrink-0 mt-0.5" />
              <div class="text-xs space-y-1">
                <p class="font-bold text-amber-800 dark:text-amber-300">Stylist View-Only Privilege</p>
                <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                  To protect salon finances and prevent billing discrepancies, only Salon Administrators can modify official payment account information. If these details need to be updated, please contact salon administration.
                </p>
              </div>
            </div>

            <!-- Footer Quick Actions -->
            <div class="pt-4 border-t border-slate-100 dark:border-white/[0.08] flex flex-wrap items-center justify-between gap-3">
              <RouterLink
                to="/barber/dashboard"
                class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
              >
                ← Return to Dashboard
              </RouterLink>

              <RouterLink
                to="/barber/payments"
                class="inline-flex items-center gap-2 rounded-xl bg-gold/15 border border-gold/30 px-5 py-2.5 text-xs font-bold text-gold hover:bg-gold hover:text-obsidian transition-all shadow-sm"
              >
                <span>Go to Payments Portal</span>
                <span>→</span>
              </RouterLink>
            </div>

          </div>
        </div>

      </div>
    </div>
  </BarberLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import {
  BuildingLibraryIcon,
  DocumentDuplicateIcon,
  ArrowPathIcon,
  InformationCircleIcon,
  LockClosedIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();
const loading = ref(false);

const details = reactive({
  bank_name: 'Zenith Bank',
  account_name: 'Candy Cutz Luxury Grooming',
  account_number: '1012345678',
  instructions: 'Please include your Booking Reference (e.g. CC-1234) in your transfer remarks to speed up instant confirmation.',
});

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
  } catch (err) {
    toast.error('Failed to copy');
  }
}

async function loadAccount() {
  loading.value = true;
  try {
    // Attempt dedicated barber bank-details endpoint first
    const res = await api.get('/barber/bank-details');
    const data = res.data?.data || res.data || {};
    if (data.bank_name || data.account_number) {
      details.bank_name = data.bank_name || 'Zenith Bank';
      details.account_name = data.account_name || 'Candy Cutz Luxury Grooming';
      details.account_number = data.account_number || '1012345678';
      details.instructions = data.instructions || details.instructions;
      return;
    }
  } catch (err) {
    // Fallback to public settings endpoint
    try {
      const setRes = await api.get('/settings');
      const s = setRes.data?.data || setRes.data || {};
      details.bank_name = s.bank_name || s.manual_transfer_bank || 'Zenith Bank';
      details.account_name = s.account_name || s.manual_transfer_account_name || 'Candy Cutz Luxury Grooming';
      details.account_number = s.account_number || s.manual_transfer_account_number || '1012345678';
      details.instructions = s.payment_instructions || details.instructions;
    } catch (fallbackErr) {
      console.error('Failed to load bank settings', fallbackErr);
      toast.error('Failed to load account details');
    }
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadAccount();
});
</script>
