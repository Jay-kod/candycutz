<template>
  <CustomerLayout>
    <div class="max-w-7xl mx-auto space-y-8 animate-fade-in pb-16 pt-4">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-[var(--color-border)] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[var(--color-bg-secondary)] dark:to-[var(--color-bg-secondary)] p-4 xs:p-6 sm:p-8 lg:p-12 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="inline-flex h-2 w-2 rounded-full bg-[var(--color-accent)] animate-pulse"></span>
              <p class="text-[10px] uppercase tracking-[0.25em] font-bold text-[var(--color-accent)]">Personal Schedule</p>
            </div>
            <h1 class="font-display text-3xl lg:text-4xl font-bold tracking-tight text-slate-900 dark:text-white drop-shadow-sm">
              My <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-[var(--color-accent)]/80">Bookings</span>
            </h1>
            <p class="mt-2 text-xs lg:text-sm text-[var(--color-text-secondary)] max-w-xl">
              Track your upcoming haircuts, grooming sessions, review receipt confirmations, and access verification codes.
            </p>
          </div>

          <RouterLink
            to="/customer/dashboard/services"
            class="flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-6 py-3 text-xs font-bold text-white transition-all hover:brightness-110 shadow-lg shadow-[var(--color-accent)]/20 active:scale-95"
          >
            <CalendarIcon class="h-4 w-4" />
            Book New Cut
          </RouterLink>
        </div>
      </div>

      <!-- Active Draft Reservation Banner -->
      <DraftBookingBanner @discarded="loadBookings" @resumed="loadBookings" />

      <!-- Filters & Active Tabs -->
      <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 dark:border-white/[0.08] pb-4">
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
            <button
              v-for="filter in filters"
              :key="filter.value"
              @click="currentFilter = filter.value"
              :class="[
                'flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all shrink-0',
                currentFilter === filter.value
                  ? 'bg-[var(--color-accent)] text-white shadow-md shadow-[var(--color-accent)]/20'
                  : 'bg-slate-100 dark:bg-white/[0.04] text-[var(--color-text-secondary)] hover:bg-slate-200 dark:hover:bg-white/[0.08] hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.06]'
              ]"
            >
              <span>{{ filter.label }}</span>
              <span 
                :class="[
                  'px-1.5 py-0.5 rounded-full text-[10px] font-mono leading-none',
                  currentFilter === filter.value ? 'bg-black/30 text-white' : 'bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-[var(--color-text-secondary)]'
                ]"
              >
                {{ filter.count }}
              </span>
            </button>
          </div>

          <p class="text-xs text-[var(--color-text-secondary)] font-mono">
            Showing {{ filteredBookings.length }} of {{ bookings.length }} appointments
          </p>
        </div>

        <!-- Bookings Grid -->
        <div v-if="loadingBookings" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="i in 6" :key="i" class="h-64 rounded-3xl bg-slate-100 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] animate-pulse"></div>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="booking in filteredBookings"
            :key="booking.id"
            class="group relative flex flex-col justify-between rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[var(--color-bg-secondary)] p-6 transition-all duration-300 hover:border-[var(--color-accent)]/30 hover:shadow-xl hover:shadow-[var(--color-accent)]/5 hover:-translate-y-1"
          >
            <div>
              <!-- Top Row: Reference & Status Badge -->
              <div class="flex items-center justify-between gap-2 mb-4 pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div>
                  <span class="text-[10px] uppercase tracking-widest text-[var(--color-text-secondary)] font-mono block">Ref No.</span>
                  <span class="font-mono text-xs font-bold text-slate-900 dark:text-white tracking-wider">{{ booking.booking_reference }}</span>
                </div>
                
                <span 
                  class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider border flex items-center gap-1.5"
                  :class="{
                    'bg-amber-500/15 text-amber-500 dark:text-amber-300 border-amber-500/40': booking.status === 'draft',
                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30': booking.status === 'confirmed',
                    'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30': booking.status === 'pending',
                    'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30': booking.status === 'completed',
                    'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/30': booking.status === 'cancelled',
                  }"
                >
                  <span v-if="booking.status === 'draft'" class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span>
                  {{ booking.status === 'draft' ? 'Draft • Unpaid' : booking.status }}
                </span>
                
                <button 
                  @click.stop="deleteBooking(booking.id)"
                  class="rounded-lg p-1.5 text-slate-400 dark:text-white/30 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors border border-transparent hover:border-red-500/20"
                  title="Remove from history"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>

              <!-- Main Details -->
              <div class="flex gap-4 items-start">
                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-[var(--color-accent)] shadow-inner">
                  <span class="font-display text-xl leading-none font-bold">{{ new Date(booking.appointment_date).getDate() || '--' }}</span>
                  <span class="text-[9px] uppercase tracking-wider font-bold text-slate-500 dark:text-white/50">{{ getMonthShort(booking.appointment_date) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-display text-lg text-slate-900 dark:text-white group-hover:text-[var(--color-accent)] transition-colors truncate">
                    {{ booking.service?.name || 'Grooming Appointment' }}
                  </p>
                  <div class="mt-2 space-y-1.5 text-xs text-[var(--color-text-secondary)]">
                    <p class="flex items-center gap-2">
                      <ClockIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0" />
                      <span>{{ formatDate(booking.appointment_date) }} at <span class="text-slate-900 dark:text-white font-bold">{{ booking.appointment_time }}</span></span>
                    </p>
                    <p class="flex items-center gap-2">
                      <UserIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0" />
                      <span>With <strong class="text-slate-900 dark:text-white font-medium">{{ booking.barber?.name || 'Assigned Barber' }}</strong></span>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Customer Notes -->
              <div v-if="booking.notes" class="mt-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] p-3 text-xs text-[var(--color-text-secondary)] border border-slate-200 dark:border-white/[0.06] border-l-2 border-l-[var(--color-accent)] italic">
                "{{ booking.notes }}"
              </div>

              <!-- Bank Transfer Payment Details Box (Directly visible on card so customer can pay) -->
              <div v-if="shouldShowBankDetails(booking)" class="mt-4 rounded-xl border border-[var(--color-accent)]/30 bg-[var(--color-accent)]/5 p-4">
                <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-200 dark:border-white/[0.08]">
                  <div class="flex items-center gap-2">
                    <BuildingLibraryIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0" />
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--color-accent)]">Transfer Payment To</p>
                  </div>
                  <span class="text-xs font-bold text-slate-900 dark:text-white bg-[var(--color-accent)]/20 border border-[var(--color-accent)]/30 px-2 py-0.5 rounded-full">
                    {{ formatNairaAmount(booking.grand_total || booking.service?.price || 0) }}
                  </span>
                </div>

                <div v-if="getBankDetails(booking)" class="space-y-2 text-xs">
                  <div class="flex justify-between items-center text-[var(--color-text-secondary)]">
                    <span>Bank Name:</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ getBankDetails(booking).bank_name }}</span>
                  </div>
                  <div class="flex justify-between items-center text-[var(--color-text-secondary)]">
                    <span>Account Name:</span>
                    <span class="text-slate-900 dark:text-white font-medium">{{ getBankDetails(booking).account_name }}</span>
                  </div>
                  <div class="flex justify-between items-center bg-slate-100 dark:bg-black/40 rounded-lg py-2 px-3 border border-slate-200 dark:border-white/[0.08] mt-1.5">
                    <span class="text-[var(--color-text-secondary)] text-[11px]">Account Number:</span>
                    <div class="flex items-center gap-2">
                      <span class="font-mono text-sm font-bold tracking-wider text-[var(--color-accent)]">{{ getBankDetails(booking).account_number }}</span>
                      <button 
                        @click="copyCode(getBankDetails(booking).account_number)"
                        class="p-1 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/15 rounded-md transition-colors"
                        title="Copy Account Number"
                      >
                        <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                      </button>
                    </div>
                  </div>
                </div>

                <div v-else class="text-xs text-amber-500 dark:text-amber-400/90 text-center py-1">
                  Payment details configured. Click below to view and upload receipt.
                </div>
              </div>

              <!-- Verification Code Box -->
              <div v-if="booking.status === 'confirmed' && booking.verification_code" class="mt-4 rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4">
                <div class="flex items-center justify-between mb-1.5">
                  <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Service Verification Code</p>
                  <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80">Present at checkout</span>
                </div>
                <div class="flex items-center justify-between bg-emerald-50 dark:bg-black/40 rounded-lg py-2.5 px-3.5 border border-emerald-500/20">
                  <span class="font-mono text-lg font-bold tracking-widest text-emerald-700 dark:text-emerald-300 uppercase">{{ booking.verification_code }}</span>
                  <button 
                    @click="copyCode(booking.verification_code)"
                    class="p-1.5 text-emerald-600 dark:text-emerald-400/80 hover:text-emerald-800 dark:hover:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 rounded-lg transition-colors shrink-0"
                    title="Copy Code"
                  >
                    <DocumentDuplicateIcon class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Actions Footer -->
            <div v-if="['draft', 'pending', 'confirmed'].includes(booking.status)" class="mt-6 pt-4 border-t border-slate-100 dark:border-white/[0.06] flex flex-col gap-2.5">
              <!-- Draft Unpaid Action Group -->
              <template v-if="booking.status === 'draft'">
                <div class="space-y-2">
                  <RouterLink 
                    :to="`/customer/dashboard/payment/${booking.id}`"
                    class="w-full rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:brightness-110 py-2.5 text-xs font-bold text-slate-950 transition-all flex items-center justify-center gap-2 shadow-md shadow-amber-500/20 active:scale-95" 
                  >
                    <CreditCardIcon class="h-4 w-4" /> Complete Payment ({{ formatNairaAmount(booking.grand_total || booking.service?.price || 0) }})
                  </RouterLink>
                  <button 
                    type="button"
                    class="w-full rounded-xl border border-red-500/30 bg-red-500/10 hover:bg-red-500/20 py-2 text-xs font-semibold text-red-400 transition-all flex items-center justify-center gap-1.5 cursor-pointer" 
                    @click="discardDraft(booking.id)"
                  >
                    <TrashIcon class="h-4 w-4" /> Discard Draft (Release Slot)
                  </button>
                </div>
              </template>

              <!-- Pending Verification Action Group -->
              <template v-else-if="booking.status === 'pending'">
                <div v-if="!booking.payment_status || booking.payment_status === 'pending' || booking.payment_status === 'unpaid'" class="space-y-2">
                  <RouterLink 
                    :to="`/customer/dashboard/payment/${booking.id}`"
                    class="w-full rounded-xl bg-[#FDB913] hover:bg-[#F2AC07] py-2.5 text-xs font-bold text-slate-950 transition-all flex items-center justify-center gap-2 shadow-md shadow-[#FDB913]/20" 
                  >
                    <CreditCardIcon class="h-4 w-4" /> Pay Now • Transfer / USSD ({{ formatNairaAmount(booking.grand_total || booking.service?.price || 0) }})
                  </RouterLink>
                  <button 
                    v-if="!showUploader[booking.id]" 
                    class="w-full rounded-xl border border-white/10 bg-white/[0.04] hover:bg-white/[0.08] py-2 text-xs font-semibold text-white/80 transition-all flex items-center justify-center gap-1.5" 
                    @click="startPayment(booking)"
                  >
                    <span>Upload Manual Receipt Slip</span>
                  </button>
                  <ReceiptUploader v-else :appointmentId="booking.id" :initialBankDetails="getBankDetails(booking)" @uploaded="onReceiptUploaded" />
                </div>
                <div v-else-if="booking.payment_status === 'awaiting_verification'" class="w-full rounded-xl bg-blue-500/10 py-2.5 text-xs font-bold text-blue-500 dark:text-blue-400 border border-blue-500/25 text-center flex items-center justify-center gap-2">
                  <ArrowPathIcon class="h-4 w-4 animate-spin" /> Receipt Under Review
                </div>
              </template>

              <button 
                class="w-full rounded-xl bg-red-500/10 py-2.5 text-xs font-bold text-red-500 dark:text-red-400 hover:bg-red-500 hover:text-white transition-all border border-red-500/20" 
                @click="cancel(booking.id)"
              >
                Cancel Booking
              </button>
            </div>
          </article>
          
          <!-- Empty State -->
          <div v-if="filteredBookings.length === 0" class="col-span-full rounded-3xl border border-dashed border-slate-200 dark:border-white/10 py-20 text-center bg-slate-50/50 dark:bg-white/[0.02]">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 mb-4">
              <CalendarIcon class="h-8 w-8 text-[var(--color-accent)]/60" />
            </div>
            <p class="text-slate-900 dark:text-white font-display text-xl mb-1.5">No Bookings Found</p>
            <p class="text-xs text-[var(--color-text-secondary)] max-w-sm mx-auto mb-6">No appointments matching this filter are currently registered.</p>
            <RouterLink 
              to="/customer/dashboard/services" 
              class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-6 py-3 text-xs font-bold text-white transition-all hover:brightness-110 shadow-lg shadow-[var(--color-accent)]/20"
            >
              Browse Services
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import ReceiptUploader from '../../../core/components/ReceiptUploader.vue';
import DraftBookingBanner from '../components/booking/DraftBookingBanner.vue';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { formatNairaAmount } from '../../../core/utils';
import { 
  ArrowPathIcon, 
  UserIcon, 
  CreditCardIcon,
  CalendarIcon,
  ClockIcon,
  TrashIcon,
  DocumentDuplicateIcon,
  BuildingLibraryIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();

const bookings = ref([]);
const loadingBookings = ref(true);
const showUploader = reactive({});
const currentFilter = ref('all');

const filters = computed(() => {
  const all = bookings.value.data || bookings.value || [];
  return [
    { label: 'All', value: 'all', count: all.length },
    { label: 'Drafts', value: 'draft', count: all.filter(b => b.status === 'draft').length },
    { label: 'Pending', value: 'pending', count: all.filter(b => b.status === 'pending').length },
    { label: 'Confirmed', value: 'confirmed', count: all.filter(b => b.status === 'confirmed').length },
    { label: 'Cancelled', value: 'cancelled', count: all.filter(b => b.status === 'cancelled').length },
  ];
});

const filteredBookings = computed(() => {
  const allBookings = bookings.value.data || bookings.value || [];
  if (currentFilter.value === 'all') return allBookings;
  return allBookings.filter(b => b.status === currentFilter.value);
});

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getMonthShort(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short' });
}

async function loadBookings() {
  loadingBookings.value = true;
  try {
    const response = await client.get('/customer/bookings');
    bookings.value = response.data?.data || response.data || [];
  } catch (err) {
    toast.error('Failed to load bookings');
  } finally {
    loadingBookings.value = false;
  }
}

async function cancel(id) {
  const ok = await confirm({ title: 'Cancel Booking', message: 'Are you sure you want to cancel this booking? This action cannot be undone.', confirmText: 'Cancel Booking' });
  if (!ok) return;
  
  try {
    await client.patch(`/customer/bookings/${id}/cancel`);
    toast.success('Booking cancelled');
    await loadBookings();
  } catch (err) {
    toast.error('Failed to cancel booking');
  }
}

async function deleteBooking(id) {
  const ok = await confirm({ title: 'Delete Booking', message: 'Are you sure you want to permanently remove this booking from your history? This action cannot be undone.', confirmText: 'Delete' });
  if (!ok) return;
  
  try {
    await client.delete(`/customer/bookings/${id}`);
    toast.success('Booking removed successfully');
    await loadBookings();
  } catch (err) {
    toast.error('Failed to delete booking');
  }
}

async function discardDraft(id) {
  const ok = await confirm({
    title: 'Discard Draft Booking',
    message: 'Are you sure you want to discard this draft? Your reserved chair slot will be released immediately.',
    confirmText: 'Discard Draft'
  });
  if (!ok) return;

  try {
    await client.delete(`/customer/drafts/${id}`).catch(() => {
      return client.delete(`/appointments/drafts/${id}`);
    });
    toast.success('Draft booking discarded. Slot released.');
    await loadBookings();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to discard draft');
  }
}

async function startPayment(booking) {
  const amount = booking.grand_total || booking.service?.price || 5000;
  try {
    await client.post('/customer/checkout', {
      appointment_id: booking.id,
      amount,
      payment_method: 'manual_transfer',
    });
  } catch (err) {
    console.warn('Checkout initialization note:', err);
  }
  showUploader[booking.id] = true;
}

async function onReceiptUploaded() {
  toast.success('Receipt uploaded successfully. Awaiting verification.');
  await loadBookings();
}

async function copyCode(code) {
  if (!code) return;
  try {
    await navigator.clipboard.writeText(String(code));
    toast.success('Copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy');
  }
}

const salonFallbackBank = ref({
  bank_name: 'Zenith Bank',
  account_name: 'Candy Cutz Luxury Grooming',
  account_number: '1012345678',
});

function getBankDetails(booking) {
  if (booking?.bank_details?.bank_name && booking?.bank_details?.account_number) {
    return booking.bank_details;
  }
  return salonFallbackBank.value;
}

function shouldShowBankDetails(booking) {
  if (!booking) return false;
  if (['cancelled', 'completed'].includes(booking.status)) return false;
  if (booking.deposit_paid || booking.payment_status === 'paid' || booking.payment_status === 'awaiting_verification') return false;
  return true;
}

onMounted(async () => {
  await loadBookings();
  try {
    const res = await client.get('/settings');
    const data = res.data?.data || res.data || {};
    if (data.bank_name && data.account_number) {
      salonFallbackBank.value = {
        bank_name: data.bank_name,
        account_name: data.account_name || 'Candy Cutz Luxury Grooming',
        account_number: data.account_number,
      };
    }
  } catch (err) {
    // Non-blocking fallback
  }
});
</script>
