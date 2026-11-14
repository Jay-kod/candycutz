<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-xl space-y-6">
    <!-- Header with Search & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-white/[0.08]">
      <div>
        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
          <QueueListIcon class="h-5 w-5 text-[var(--color-accent)]" />
          Live Payment Transactions & Barber Control
        </h2>
        <p class="text-xs text-slate-500 dark:text-[var(--color-text-secondary)] mt-0.5">
          Verify payments, audit financial channels, and reassign barbers on live customer bookings.
        </p>
      </div>

      <!-- Search & Refresh -->
      <div class="flex items-center gap-2">
        <input
          v-model="search"
          @input="debouncedFetch"
          type="text"
          placeholder="Search ref or customer..."
          class="rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-black/40 px-3 py-2 text-xs text-slate-900 dark:text-white outline-none focus:border-[var(--color-accent)]"
        />
        <button
          type="button"
          @click="fetchPayments"
          class="p-2 rounded-xl border border-slate-200 dark:border-white/10 text-slate-600 dark:text-zinc-300 hover:text-white hover:bg-white/[0.06] transition-colors cursor-pointer"
          title="Refresh table"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap items-center gap-1.5">
      <button
        v-for="st in statusFilters"
        :key="st.value"
        type="button"
        @click="filterStatus = st.value; fetchPayments()"
        :class="[
          'px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer',
          filterStatus === st.value
            ? 'bg-[var(--color-accent)] text-obsidian shadow-sm'
            : 'bg-slate-100 dark:bg-white/[0.04] text-slate-700 dark:text-zinc-400 hover:bg-slate-200 dark:hover:bg-white/[0.08]'
        ]"
      >
        {{ st.label }}
      </button>
    </div>

    <!-- Preloader -->
    <div v-if="loading && payments.length === 0" class="py-16 text-center">
      <div class="h-8 w-8 mx-auto animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></div>
      <p class="text-xs text-slate-400 mt-2 font-mono">Loading transactions from database...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="payments.length === 0" class="py-16 text-center rounded-2xl border border-dashed border-slate-200 dark:border-white/10">
      <p class="text-sm font-semibold text-slate-700 dark:text-zinc-300">No payment transactions found</p>
      <p class="text-xs text-slate-500 dark:text-zinc-500 mt-1">Payments made through the checkout screen will appear here.</p>
    </div>

    <!-- Transactions Table -->
    <div v-else class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/[0.06]">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 dark:bg-black/40 text-slate-600 dark:text-zinc-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200 dark:border-white/[0.06]">
          <tr>
            <th class="py-3 px-4">Reference</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4">Amount</th>
            <th class="py-3 px-4">Channel</th>
            <th class="py-3 px-4">Assigned Barber</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Admin Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
          <tr v-for="p in payments" :key="p.id" class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
            <!-- Reference & Date -->
            <td class="py-3.5 px-4 font-mono">
              <span class="font-bold text-slate-900 dark:text-white">{{ p.reference || p.transaction_ref || 'PAY-' + p.id }}</span>
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">{{ formatDate(p.created_at) }}</span>
            </td>

            <!-- Customer -->
            <td class="py-3.5 px-4">
              <span class="font-semibold text-slate-900 dark:text-white">{{ p.customer?.name || p.appointment?.client_name || 'Walk-In Customer' }}</span>
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">{{ p.customer?.email || p.appointment?.client_email || 'customer@candycutz.com' }}</span>
            </td>

            <!-- Amount -->
            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
              ₦{{ formatMoney(p.amount) }}
            </td>

            <!-- Channel -->
            <td class="py-3.5 px-4">
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.06] text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-white/10">
                {{ formatChannel(p.channel || p.payment_method || p.provider) }}
              </span>
            </td>

            <!-- Assigned Barber (with change button) -->
            <td class="py-3.5 px-4">
              <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-500 font-bold flex items-center justify-center shrink-0 text-xs">
                  {{ (p.appointment?.barber?.user?.name || 'B').charAt(0) }}
                </div>
                <div>
                  <span class="font-bold text-slate-900 dark:text-white block">{{ p.appointment?.barber?.user?.name || 'Master Barber' }}</span>
                  <button
                    type="button"
                    @click="openReassignModal(p)"
                    class="text-[10px] text-[var(--color-accent)] hover:underline cursor-pointer"
                  >
                    Change Barber
                  </button>
                </div>
              </div>
            </td>

            <!-- Status Badge -->
            <td class="py-3.5 px-4">
              <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider', getStatusClass(p.status)]">
                {{ p.status }}
              </span>
            </td>

            <!-- Admin Actions -->
            <td class="py-3.5 px-4 text-right">
              <div class="flex items-center justify-end gap-1.5">
                <button
                  v-if="p.status !== 'successful' && p.status !== 'verified'"
                  type="button"
                  @click="verifyPayment(p)"
                  :disabled="actioningId === p.id"
                  class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold text-[11px] border border-emerald-500/20 transition-all cursor-pointer"
                  title="Verify and confirm payment"
                >
                  Verify
                </button>
                <button
                  v-if="p.status !== 'failed' && p.status !== 'rejected'"
                  type="button"
                  @click="rejectPayment(p)"
                  :disabled="actioningId === p.id"
                  class="px-2 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-semibold text-[11px] border border-rose-500/20 transition-all cursor-pointer"
                  title="Reject payment"
                >
                  Reject
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Barber Reassign Modal -->
    <div v-if="reassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="w-full max-w-md rounded-3xl border border-white/10 bg-[#161B22] p-6 shadow-2xl space-y-4">
        <h3 class="text-sm font-bold text-white">Reassign Barber for Payment</h3>
        <p class="text-xs text-white/60">
          Select a master barber to handle appointment <strong>{{ selectedPayment?.reference }}</strong>.
        </p>

        <select
          v-model="targetBarberId"
          class="w-full rounded-xl border border-white/10 bg-black/40 px-3 py-2.5 text-xs text-white outline-none focus:border-[var(--color-accent)]"
        >
          <option v-for="b in barbers" :key="b.id" :value="b.id">
            {{ b.user?.name || b.name || 'Barber #' + b.id }} (Chair {{ b.chair_number || 1 }})
          </option>
        </select>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            @click="reassignModalOpen = false"
            class="px-3 py-2 rounded-xl text-xs font-semibold text-white/60 hover:text-white"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submitReassign"
            :disabled="!targetBarberId || actioningId"
            class="px-4 py-2 rounded-xl bg-[var(--color-accent)] text-obsidian text-xs font-bold shadow-md cursor-pointer disabled:opacity-50"
          >
            Confirm Reassignment
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { QueueListIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

const toast = useToast();
const loading = ref(false);
const actioningId = ref(null);
const search = ref('');
const filterStatus = ref('');
const payments = ref([]);
const barbers = ref([]);

const reassignModalOpen = ref(false);
const selectedPayment = ref(null);
const targetBarberId = ref(null);

const statusFilters = [
  { label: 'All', value: '' },
  { label: 'Successful', value: 'successful' },
  { label: 'Pending / Review', value: 'awaiting_verification' },
  { label: 'Failed / Rejected', value: 'failed' }
];

let debounceTimer = null;
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchPayments(), 300);
}

async function fetchPayments() {
  loading.value = true;
  try {
    const res = await client.get('/admin/payments', {
      params: { search: search.value, status: filterStatus.value }
    });
    const data = res.data?.data || res.data || {};
    payments.value = Array.isArray(data) ? data : (data.data || []);
  } catch (err) {
    console.error('Failed to load payments:', err);
  } finally {
    loading.value = false;
  }
}

async function loadBarbers() {
  try {
    const res = await client.get('/barbers');
    const data = res.data?.data || res.data || [];
    barbers.value = Array.isArray(data) ? data : (data.data || []);
  } catch (err) {
    console.error('Failed to load barbers:', err);
  }
}

async function verifyPayment(payment) {
  actioningId.value = payment.id;
  try {
    await client.post(`/admin/payments/${payment.id}/verify`);
    toast.success(`Payment ${payment.reference || payment.id} verified & confirmed!`);
    await fetchPayments();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Verification failed');
  } finally {
    actioningId.value = null;
  }
}

async function rejectPayment(payment) {
  actioningId.value = payment.id;
  try {
    await client.post(`/admin/payments/${payment.id}/reject`, { reason: 'Admin verification declined' });
    toast.success(`Payment ${payment.reference || payment.id} marked as rejected.`);
    await fetchPayments();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Rejection failed');
  } finally {
    actioningId.value = null;
  }
}

function openReassignModal(payment) {
  selectedPayment.value = payment;
  targetBarberId.value = payment.appointment?.barber_id || (barbers.value[0]?.id || null);
  reassignModalOpen.value = true;
}

async function submitReassign() {
  if (!selectedPayment.value || !targetBarberId.value) return;
  actioningId.value = selectedPayment.value.id;
  try {
    await client.post(`/admin/payments/${selectedPayment.value.id}/reassign-barber`, {
      barber_id: targetBarberId.value
    });
    toast.success('Barber reassigned successfully!');
    reassignModalOpen.value = false;
    await fetchPayments();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to reassign barber');
  } finally {
    actioningId.value = null;
  }
}

function formatMoney(amount) {
  return Number(amount || 0).toLocaleString();
}

function formatDate(dt) {
  if (!dt) return '';
  return new Date(dt).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatChannel(ch) {
  if (!ch) return 'Transfer';
  return ch.replace('_', ' ');
}

function getStatusClass(st) {
  if (st === 'successful' || st === 'verified') return 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20';
  if (st === 'failed' || st === 'rejected') return 'bg-rose-500/10 text-rose-500 border border-rose-500/20';
  return 'bg-amber-500/10 text-amber-500 border border-amber-500/20';
}

onMounted(() => {
  fetchPayments();
  loadBarbers();
});
</script>
