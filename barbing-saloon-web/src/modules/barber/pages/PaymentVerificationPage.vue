<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in pb-20">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-blue-500/30 bg-gradient-to-br from-obsidian via-charcoal to-blue-900/20 p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-blue-400 font-medium">Payments & Account</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text drop-shadow-lg">
              Payment <span class="text-blue-400">Verification</span>
            </h1>
            <p class="mt-2 text-sm text-ivory/50">Review receipts and manage your bank account details.</p>
          </div>
          <div class="flex flex-col sm:flex-row items-center gap-3">
            <button
              @click="loadData"
              :disabled="loading"
              class="flex items-center gap-2 rounded-xl bg-blue-500/10 border border-blue-500/20 px-4 py-2.5 text-sm font-bold text-blue-400 hover:bg-blue-500 hover:text-obsidian transition-all shrink-0 disabled:opacity-50"
            >
              <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
              Refresh
            </button>

            <button
              @click="openAccountModal"
              class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-light px-4 py-2.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] shrink-0"
            >
              <BanknotesIcon class="h-4 w-4" />
              Manage Bank Details
            </button>
            <div class="flex items-center gap-2 rounded-xl bg-white/[0.04] border border-theme-border px-4 py-2.5">
              <MagnifyingGlassIcon class="h-4 w-4 text-ivory/30" />
              <input
                v-model="search"
                type="text"
                placeholder="Search by customer..."
                class="bg-transparent text-sm text-theme-text placeholder:text-ivory/25 outline-none w-40"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Payments List -->
      <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden shadow-xl">
        <!-- Table Header -->
        <div class="hidden md:grid grid-cols-[1fr_1fr_0.7fr_auto] gap-4 border-b border-theme-border px-6 py-3.5 text-[11px] uppercase tracking-wider text-ivory/30 font-semibold bg-black/20">
          <span>Customer & Service</span>
          <span>Date & Time</span>
          <span>Amount & Status</span>
          <span class="text-right">Action</span>
        </div>

        <div v-if="loading" class="py-16 flex justify-center">
          <div class="h-8 w-8 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></div>
        </div>

        <div v-else class="divide-y divide-white/[0.04]">
          <div
            v-for="booking in paymentsList"
            :key="booking.id"
            class="group md:grid md:grid-cols-[1fr_1fr_0.7fr_auto] md:items-center gap-4 px-6 py-5 transition-colors hover:bg-theme-surface/50"
          >
            <!-- Customer -->
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-500/10 text-blue-400 text-sm font-bold border border-blue-500/20">
                {{ initials(booking.customer_name) }}
              </div>
              <div>
                <p class="text-sm font-bold text-theme-text">{{ booking.customer_name }}</p>
                <p class="text-xs text-ivory/40">{{ booking.service_name || 'General Service' }}</p>
              </div>
            </div>

            <!-- Date & Time -->
            <div class="mt-2 md:mt-0">
              <p class="text-sm text-theme-text">{{ new Date(booking.appointment_date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' }) }}</p>
              <div class="flex items-center gap-1.5 mt-0.5">
                <ClockIcon class="h-3.5 w-3.5 text-gold/60" />
                <p class="text-xs text-gold/80 font-medium">{{ booking.appointment_time }}</p>
              </div>
            </div>

            <!-- Amount -->
            <div class="mt-2 md:mt-0 flex flex-col gap-1 items-start md:items-start">
              <span class="text-sm font-bold text-emerald-400">₦{{ (booking.price || 0).toLocaleString() }}</span>
              <span v-if="booking.payment_status === 'awaiting_verification'" class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border bg-amber-500/10 text-amber-400 border-amber-500/20">
                Awaiting Verification
              </span>
              <span v-else-if="booking.payment_status === 'completed' || booking.payment_status === 'successful'" class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">
                {{ booking.payment_status }}
              </span>
              <span v-else class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border bg-red-500/10 text-red-400 border-red-500/20">
                {{ booking.payment_status || 'Pending' }}
              </span>
            </div>

            <!-- Actions -->
            <div class="mt-4 md:mt-0 flex items-center justify-end gap-3">
              <div v-if="booking.verified_by" class="flex flex-col items-end gap-1">
                <span class="text-[10px] font-bold text-emerald-400 border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 rounded-md uppercase tracking-wider">
                  Cleared by {{ booking.verified_by === 'admin' ? 'Admin' : 'You' }}
                </span>
                <button @click="viewReceipt(booking)" class="text-[11px] text-blue-400 hover:text-blue-300 underline">View Receipt</button>
              </div>
              <div v-else-if="!booking.payment_status || ['pending', 'awaiting_verification'].includes(booking.payment_status.toLowerCase())" class="flex items-center gap-2">
                <button
                  @click="viewReceipt(booking)"
                  class="inline-flex items-center gap-1.5 rounded-xl bg-blue-500/10 border border-blue-500/20 px-3 py-2 text-xs font-bold text-blue-400 hover:bg-blue-500 hover:text-obsidian hover:shadow-[0_0_15px_rgba(59,130,246,0.3)] transition-all"
                >
                  <DocumentTextIcon class="h-3.5 w-3.5" />
                  View Receipt
                </button>
              </div>
              <div v-else class="flex items-center justify-end">
                <button
                  @click="viewReceipt(booking)"
                  class="inline-flex items-center gap-1.5 rounded-xl bg-white/5 border border-white/10 px-3 py-2 text-xs font-bold text-ivory/70 hover:bg-white/10 hover:text-white transition-all"
                >
                  <DocumentTextIcon class="h-3.5 w-3.5" />
                  View Receipt
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && paymentsList.length === 0" class="px-6 py-20 text-center">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-500/5 border border-dashed border-blue-500/20 mb-4">
            <CheckBadgeIcon class="h-8 w-8 text-blue-400/50" />
          </div>
          <p class="text-base font-display text-theme-text mb-1">You're all caught up!</p>
          <p class="text-sm text-ivory/40">There are no pending payments requiring your verification.</p>
        </div>
      </div>
    </section>

    <!-- Decomposed Account Details Modal -->
    <BarberBankDetailsModal
      :show="showAccountModal"
      :loading="loadingAccount"
      :saving="savingAccount"
      :form="accountForm"
      @close="showAccountModal = false"
      @save="saveAccount"
    />
  </BarberLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { barberApi } from '@/shared/api/old_barberApi';
import { useToast } from '../../../core/composables/useToast';
import BarberBankDetailsModal from '../components/verification/BarberBankDetailsModal.vue';
import {
  MagnifyingGlassIcon,
  ClockIcon,
  DocumentTextIcon,
  CheckBadgeIcon,
  BanknotesIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();
const router = useRouter();
const loading = ref(true);
const appointments = ref([]);
const search = ref('');

const showAccountModal = ref(false);
const loadingAccount = ref(false);
const savingAccount = ref(false);
const accountForm = ref({
  bank_name: '',
  account_name: '',
  account_number: ''
});

const paymentsList = computed(() => {
  const list = appointments.value || [];
  let filtered = list.filter(a => 
    a.receipt_image || 
    a.payment_status || 
    a.status === 'pending'
  );
  
  if (search.value) {
    const q = search.value.toLowerCase();
    filtered = filtered.filter(a => (a.customer_name || '').toLowerCase().includes(q));
  }
  
  return filtered;
});

function initials(name) {
  return (name || 'C').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

let pollingInterval = null;

async function loadData() {
  try {
    const timestamp = new Date().getTime();
    const response = await barberApi.getBookings({ _t: timestamp });
    appointments.value = response.data.data;
  } catch (e) {
    toast.error('Failed to load pending payments');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadData();
  pollingInterval = setInterval(() => {
    loadData();
  }, 10000);
});

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
});

const viewReceipt = (booking) => {
  router.push(`/barber/payments/${booking.id}/receipt`);
};

const openAccountModal = async () => {
  showAccountModal.value = true;
  loadingAccount.value = true;
  try {
    const res = await barberApi.getAccountDetails();
    if (res.data?.data) {
      accountForm.value.bank_name = res.data.data.bank_name || '';
      accountForm.value.account_name = res.data.data.account_name || '';
      accountForm.value.account_number = res.data.data.account_number || '';
    }
  } catch (error) {
    toast.error('Failed to load account details');
  } finally {
    loadingAccount.value = false;
  }
};

const saveAccount = async () => {
  savingAccount.value = true;
  try {
    await barberApi.updateAccountDetails(accountForm.value);
    toast.success('Account details updated successfully');
    showAccountModal.value = false;
  } catch (error) {
    toast.error('Failed to update account details');
  } finally {
    savingAccount.value = false;
  }
};
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
