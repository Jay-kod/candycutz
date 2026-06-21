<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-ivory">
              Your <span class="text-gold">Bookings</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Create new appointments, pay for pending ones, or view your history.
            </p>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <form class="rounded-2xl border border-white/[0.06] bg-charcoal/80 p-8 backdrop-blur-sm" @submit.prevent="submit">
          <h2 class="font-display text-2xl text-ivory">Create <span class="text-gold">Booking</span></h2>
          <div class="mt-6 space-y-5">
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Barber</label>
              <select v-model="form.barber_id" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory outline-none transition-colors focus:border-gold/50" @change="loadSlots">
                <option value="">Select barber</option>
                <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Service</label>
              <select v-model="form.service_id" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory outline-none transition-colors focus:border-gold/50" @change="loadSlots">
                <option value="">Select service</option>
                <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }} - ₦{{ service.price }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Date</label>
              <input v-model="form.appointment_date" type="date" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory outline-none transition-colors focus:border-gold/50 [color-scheme:dark]" @change="loadSlots" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Time</label>
              <select v-model="form.appointment_time" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory outline-none transition-colors focus:border-gold/50" :disabled="!slots.length">
                <option value="">Select time</option>
                <option v-for="slot in slots" :key="slot" :value="slot">{{ slot }}</option>
              </select>
              <p v-if="slots.length === 0 && form.barber_id && form.service_id && form.appointment_date" class="text-xs text-amber-400 mt-1">No slots available for this date.</p>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Notes</label>
              <textarea v-model="form.notes" rows="3" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory placeholder-ivory/30 outline-none transition-colors focus:border-gold/50 resize-none" placeholder="Any special requests?"></textarea>
            </div>
          </div>
          <button class="mt-8 w-full rounded-xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]">
            Book now
          </button>
        </form>

        <div class="rounded-2xl border border-white/[0.06] bg-charcoal/80 p-8 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl text-ivory">Your <span class="text-gold">History</span></h2>
            <button class="rounded-lg p-2 text-ivory/40 hover:bg-gold/10 hover:text-gold transition-colors" @click="loadBookings" title="Refresh">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            </button>
          </div>
          
          <div v-if="loadingBookings" class="mt-12 flex justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-gold border-t-transparent"></div>
          </div>

          <div v-else class="mt-6 space-y-4">
            <article v-for="booking in bookings.data || bookings" :key="booking.id" class="rounded-xl border border-white/[0.04] bg-obsidian/50 p-5 transition-all hover:border-gold/30 hover:bg-white/[0.02]">
              <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                  <p class="font-display text-lg text-ivory">{{ booking.service?.name }}</p>
                  <p class="mt-1 text-sm text-ivory/50">{{ booking.appointment_date }} at {{ booking.appointment_time }}</p>
                  <p class="text-sm text-ivory/50 mt-1">Barber: <span class="text-ivory/80 font-medium">{{ booking.barber?.name }}</span></p>
                </div>
                <div class="flex flex-col items-end gap-2">
                  <span 
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="{
                      'bg-amber-500/10 text-amber-400': booking.status === 'pending',
                      'bg-emerald-500/10 text-emerald-400': booking.status === 'confirmed',
                      'bg-red-500/10 text-red-400': booking.status === 'cancelled',
                      'bg-blue-500/10 text-blue-400': booking.status === 'completed'
                    }"
                  >
                    {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                  </span>
                  
                  <div class="flex flex-wrap justify-end gap-2 mt-2">
                    <button v-if="booking.status === 'pending'" class="rounded-lg bg-gold/10 px-3 py-1.5 text-xs font-semibold text-gold hover:bg-gold hover:text-obsidian transition-colors" @click="pay(booking)">
                      Pay Now
                    </button>
                    <button v-if="booking.status === 'pending' || booking.status === 'confirmed'" class="rounded-lg bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-500 hover:text-white transition-colors" @click="cancel(booking.id)">
                      Cancel
                    </button>
                  </div>
                </div>
              </div>
            </article>
            
            <div v-if="(bookings.data || bookings).length === 0" class="rounded-xl border border-dashed border-white/10 p-8 text-center">
              <p class="text-sm text-ivory/40">No bookings found.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { customerApi } from '../api/customer.api';
import { useToast } from 'vue-toastification';
import { useConfirm } from '../../../core/composables/useConfirm';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { confirm } = useConfirm();

const form = reactive({ barber_id: '', service_id: '', appointment_date: '', appointment_time: '', notes: '' });
const bookings = ref([]);
const services = ref([]);
const barbers = ref([]);
const slots = ref([]);
const loadingBookings = ref(true);

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

async function loadOptions() {
  try {
    const [servicesResponse, barbersResponse] = await Promise.all([publicApi.services(), publicApi.barbers()]);
    services.value = servicesResponse.data.data;
    barbers.value = barbersResponse.data.data;
    
    // Check if service ID was passed in query string (from public pages)
    if (route.query.service) {
      form.service_id = route.query.service;
      // remove it from url so it doesn't persist forever
      router.replace({ query: {} });
    }
  } catch (err) {
    toast.error('Failed to load form options');
  }
}

async function loadSlots() {
  if (!form.barber_id || !form.service_id || !form.appointment_date) {
    slots.value = [];
    return;
  }

  try {
    const response = await publicApi.availableSlots({
      barber_id: form.barber_id,
      service_id: form.service_id,
      date: form.appointment_date,
    });
    slots.value = response.data.data;
  } catch (err) {
    toast.error('Failed to load available slots');
  }
}

async function submit() {
  if (!form.appointment_time) {
    toast.warning('Please select an appointment time');
    return;
  }
  
  try {
    await customerApi.createBooking(form);
    toast.success('Booking created successfully!');
    // Reset form
    form.appointment_date = '';
    form.appointment_time = '';
    form.notes = '';
    slots.value = [];
    await loadBookings();
  } catch (err) {
    toast.error('Failed to create booking');
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

async function pay(booking) {
  try {
    const amount = booking.service?.price || 5000; // Mock amount
    await customerApi.checkout({ appointment_id: booking.id, amount: amount });
    toast.success('Payment successful! Booking confirmed.');
    await loadBookings();
  } catch (err) {
    toast.error('Payment failed.');
  }
}

onMounted(async () => {
  await loadOptions();
  await loadBookings();
});
</script>