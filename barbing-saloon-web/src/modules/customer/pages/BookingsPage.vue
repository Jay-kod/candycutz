<template>
  <CustomerLayout>
    <div class="animate-fade-in pb-20">
      
      <!-- Hero Banner -->
      <section class="relative h-[30vh] min-h-[250px] w-full overflow-hidden rounded-3xl border border-gold/20 mb-12 shadow-2xl">
        <img src="https://images.unsplash.com/photo-1593702275687-f8b402bf1fb5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" alt="Barbershop" class="absolute inset-0 h-full w-full object-cover scale-105" />
        <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/80 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-obsidian/90 via-obsidian/60 to-transparent"></div>
        
        <div class="absolute inset-0 flex items-center p-8 md:p-12">
          <div class="max-w-2xl">
            <p class="text-xs uppercase tracking-[0.4em] text-gold-light mb-4 font-bold">Your Appointments</p>
            <h1 class="font-display text-5xl md:text-6xl font-bold text-white drop-shadow-lg">
              Manage your <span class="text-gold italic">Bookings</span>
            </h1>
            <p class="mt-4 text-lg text-white/70 max-w-xl font-light hidden sm:block">
              View your upcoming appointments, upload payment receipts, and check your grooming history.
            </p>
          </div>
        </div>
      </section>

      <!-- Main Layout -->
      <div class="space-y-8 max-w-5xl mx-auto">
        <div class="rounded-3xl border border-theme-border bg-theme-surface/60 backdrop-blur-xl p-6 md:p-8 shadow-2xl">
          <div class="flex items-center justify-between mb-8">
            <h2 class="font-display text-2xl md:text-3xl text-theme-text">Booking <span class="text-gold">History</span></h2>
            <button class="flex items-center gap-2 rounded-xl bg-theme-bg px-4 py-2 text-xs font-bold text-theme-muted hover:bg-gold/10 hover:text-gold hover:border-gold/30 transition-all border border-theme-border shadow-sm group" @click="loadBookings" title="Refresh Data">
              <ArrowPathIcon class="h-4 w-4 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': loadingBookings }" />
              <span class="hidden sm:inline">Refresh</span>
            </button>
          </div>

          <!-- Filters -->
          <div class="mb-6 flex flex-wrap gap-2 md:gap-3">
            <button @click="currentFilter = 'all'" :class="currentFilter === 'all' ? 'bg-gold text-obsidian font-bold border-gold' : 'bg-theme-bg text-theme-muted hover:bg-theme-surface'" class="rounded-xl px-4 md:px-6 py-2 md:py-2.5 text-xs md:text-sm transition-all border border-theme-border shadow-sm">Recent / All</button>
            <button @click="currentFilter = 'pending'" :class="currentFilter === 'pending' ? 'bg-amber-500 text-obsidian font-bold border-amber-500' : 'bg-theme-bg text-theme-muted hover:bg-theme-surface'" class="rounded-xl px-4 md:px-6 py-2 md:py-2.5 text-xs md:text-sm transition-all border border-theme-border shadow-sm">Pending</button>
            <button @click="currentFilter = 'confirmed'" :class="currentFilter === 'confirmed' ? 'bg-emerald-400 text-obsidian font-bold border-emerald-400' : 'bg-theme-bg text-theme-muted hover:bg-theme-surface'" class="rounded-xl px-4 md:px-6 py-2 md:py-2.5 text-xs md:text-sm transition-all border border-theme-border shadow-sm">Approved</button>
            <button @click="currentFilter = 'cancelled'" :class="currentFilter === 'cancelled' ? 'bg-red-500 text-white font-bold border-red-500' : 'bg-theme-bg text-theme-muted hover:bg-theme-surface'" class="rounded-xl px-4 md:px-6 py-2 md:py-2.5 text-xs md:text-sm transition-all border border-theme-border shadow-sm">Cancelled</button>
          </div>
          
          <div v-if="loadingBookings" class="py-20 flex justify-center">
            <div class="h-10 w-10 animate-spin rounded-full border-4 border-gold border-t-transparent shadow-[0_0_15px_rgba(212,175,55,0.5)]"></div>
          </div>

          <div v-else class="grid gap-6 md:grid-cols-2">
            <article 
              v-for="booking in filteredBookings" 
              :key="booking.id" 
              class="group relative rounded-2xl border p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-theme-bg/40 border-theme-border hover:border-gold/30 hover:bg-theme-surface/80"
            >
              <!-- Status Badge & Delete Action -->
              <div class="absolute top-6 right-6 flex items-center gap-2">
                <span 
                  class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest border"
                  :class="{
                    'bg-amber-500/10 text-amber-400 border-amber-500/20': booking.status === 'pending',
                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-[0_0_15px_rgba(16,185,129,0.2)]': booking.status === 'confirmed',
                    'bg-red-500/10 text-red-400 border-red-500/20': booking.status === 'cancelled',
                    'bg-blue-500/10 text-blue-400 border-blue-500/20': booking.status === 'completed'
                  }"
                >
                  {{ booking.status }}
                </span>
                
                <button 
                  @click.stop="deleteBooking(booking.id)"
                  class="rounded-lg p-1.5 text-theme-muted/50 hover:bg-red-500/10 hover:text-red-400 transition-colors border border-transparent hover:border-red-500/20"
                  title="Delete Booking"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>

              <div class="flex gap-4">
                <div class="mt-1 flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-theme-surface border border-theme-border text-gold shadow-inner">
                  <span class="font-display text-2xl">{{ new Date(booking.appointment_date).getDate() }}</span>
                </div>
                <div class="pr-20">
                  <p class="font-display text-xl text-theme-text">{{ booking.service?.name }}</p>
                  <div class="mt-2 space-y-1.5">
                    <p class="flex items-center gap-2 text-sm text-theme-muted">
                      <CalendarIcon class="h-4 w-4 text-gold/50" />
                      <span>{{ formatDate(booking.appointment_date) }} at <span class="text-gold font-bold">{{ booking.appointment_time }}</span></span>
                    </p>
                    <p class="flex items-center gap-2 text-sm text-theme-muted">
                      <UserIcon class="h-4 w-4 text-gold/50" />
                      <span>With {{ booking.barber?.name }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div v-if="booking.notes" class="mt-4 rounded-xl bg-theme-surface p-3 text-xs text-theme-muted border border-theme-border border-l-2 border-l-gold">
                "{{ booking.notes }}"
              </div>

              <div v-if="booking.status === 'confirmed' && booking.verification_code" class="mt-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-emerald-400 mb-2">Service Verification Code</p>
                <p class="text-sm text-theme-muted mb-3">Provide this verification code to your barber after your service is completed to verify delivery.</p>
                <div class="flex items-center justify-between bg-theme-bg rounded-lg py-3 px-4 border border-theme-border shadow-inner">
                  <span class="font-mono text-xl font-bold tracking-widest text-emerald-400 uppercase text-center w-full">{{ booking.verification_code }}</span>
                  <button 
                    @click="copyCode(booking.verification_code)"
                    class="p-2 text-emerald-400/50 hover:text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition-colors flex-shrink-0"
                    title="Copy Code"
                  >
                    <DocumentDuplicateIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- Actions -->
              <div v-if="['pending', 'confirmed'].includes(booking.status)" class="mt-6 pt-5 border-t border-white/5 flex flex-col gap-3">
                
                <template v-if="booking.status === 'pending'">
                  <div v-if="!booking.payment_status || booking.payment_status === 'pending'">
                    <button v-if="!showUploader[booking.id]" class="w-full rounded-xl bg-gold py-3 text-sm font-bold text-obsidian hover:bg-gold-light hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all flex items-center justify-center gap-2" @click="startPayment(booking)">
                      <CreditCardIcon class="h-5 w-5" /> Pay & Upload Receipt (₦{{ (booking.service?.price || 0).toLocaleString() }})
                    </button>
                    <ReceiptUploader v-else :appointmentId="booking.id" @uploaded="onReceiptUploaded" />
                  </div>
                  
                  <div v-else-if="booking.payment_status === 'awaiting_verification'" class="w-full rounded-xl bg-blue-500/10 py-3 text-sm font-bold text-blue-400 border border-blue-500/20 text-center flex items-center justify-center gap-2">
                    <ArrowPathIcon class="h-5 w-5 animate-spin" /> Pending Confirmation
                  </div>
                </template>

                <button class="w-full rounded-xl bg-red-500/10 py-3 text-sm font-bold text-red-400 hover:bg-red-500 hover:text-white transition-all border border-red-500/20 mt-2" @click="cancel(booking.id)">
                  Cancel Booking
                </button>
              </div>
            </article>
            
            <div v-if="filteredBookings.length === 0" class="col-span-full rounded-2xl border border-dashed border-theme-border py-24 text-center bg-theme-bg/30">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-theme-surface mb-4">
                <CalendarIcon class="h-8 w-8 text-gold/30" />
              </div>
              <p class="text-theme-text font-display text-xl mb-2">No Bookings Yet</p>
              <p class="text-sm text-theme-muted max-w-md mx-auto mb-6">Your upcoming and past appointments will appear here.</p>
              <RouterLink to="/customer/dashboard/services" class="inline-flex items-center gap-2 rounded-xl bg-gold px-6 py-3 text-sm font-bold text-obsidian transition-all hover:bg-gold-light hover:shadow-[0_0_20px_rgba(212,175,55,0.3)]">
                Browse Services
              </RouterLink>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import ReceiptUploader from '../../../core/components/ReceiptUploader.vue';
import { customerApi } from '../api/customer.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { 
  ArrowPathIcon, 
  UserIcon, 
  CreditCardIcon,
  CalendarIcon,
  TrashIcon,
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();

const bookings = ref([]);
const loadingBookings = ref(true);
const showUploader = reactive({});

const currentFilter = ref('all');

const filteredBookings = computed(() => {
  const allBookings = bookings.value.data || bookings.value || [];
  if (currentFilter.value === 'all') return allBookings;
  return allBookings.filter(b => b.status === currentFilter.value);
});

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

async function loadBookings() {
  loadingBookings.value = true;
  try {
    const response = await customerApi.bookings();
    bookings.value = response.data.data || [];
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
    await customerApi.cancelBooking(id);
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
    await customerApi.deleteBooking(id);
    toast.success('Booking removed successfully');
    await loadBookings();
  } catch (err) {
    toast.error('Failed to delete booking');
  }
}

async function startPayment(booking) {
  try {
    const amount = booking.service?.price || 5000;
    if (!booking.payment_status) {
      await customerApi.checkout({ appointment_id: booking.id, amount: amount });
    }
    showUploader[booking.id] = true;
  } catch (err) {
    toast.error('Failed to initiate payment.');
  }
}

async function onReceiptUploaded(data) {
  toast.success('Receipt uploaded successfully. Awaiting verification.');
  await loadBookings();
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

onMounted(async () => {
  await loadBookings();
});
</script>