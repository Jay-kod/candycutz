<template>
  <CustomerLayout>
    <div class="animate-fade-in pb-20">
      
      <!-- Hero Banner -->
      <section class="relative h-[40vh] min-h-[300px] w-full overflow-hidden rounded-3xl border border-gold/20 mb-12 shadow-2xl">
        <img src="https://images.unsplash.com/photo-1521590832167-7bfc17484d20?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" alt="Barbershop" class="absolute inset-0 h-full w-full object-cover scale-105" />
        <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/80 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-obsidian/90 via-obsidian/60 to-transparent"></div>
        
        <div class="absolute inset-0 flex items-center p-8 md:p-12">
          <div class="max-w-2xl">
            <p class="text-xs uppercase tracking-[0.4em] text-gold-light mb-4 font-bold">CandyCutz Experience</p>
            <h1 class="font-display text-5xl md:text-6xl font-bold text-white drop-shadow-lg">
              Reserve your <span class="text-gold italic">Seat</span>
            </h1>
            <p class="mt-4 text-lg text-ivory/70 max-w-xl font-light hidden sm:block">
              Choose your master barber, pick a service, and secure your time slot. Experience grooming at its finest.
            </p>
          </div>
        </div>
      </section>

      <!-- Main Layout -->
      <div class="grid gap-8 lg:grid-cols-12 items-start">
        
        <!-- Left: Booking Flow -->
        <div class="lg:col-span-7 space-y-8">
          
          <form @submit.prevent="submit" class="bg-theme-surface/60 backdrop-blur-xl border border-white/5 rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-gold/5 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

            <div class="flex items-center justify-between mb-8">
              <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">1</span>
                The <span class="text-gold">Barber</span>
              </h2>
            </div>
            
            <!-- Barbers Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-12">
              <div 
                v-for="barber in barbers" 
                :key="barber.id"
                @click="barber.status === 'active' ? selectBarber(barber.id) : null"
                class="relative group rounded-2xl border p-4 transition-all duration-300 overflow-hidden text-center"
                :class="[
                  barber.status === 'active' ? 'cursor-pointer hover:-translate-y-1 hover:shadow-xl hover:shadow-gold/10' : 'cursor-not-allowed opacity-50 grayscale',
                  form.barber_id === barber.id ? 'border-gold bg-gradient-to-b from-gold/10 to-transparent shadow-[0_0_20px_rgba(212,175,55,0.15)]' : 'border-theme-border bg-theme-bg/50 hover:border-gold/30'
                ]"
              >
                <div v-if="form.barber_id === barber.id" class="absolute top-3 right-3 text-gold">
                  <CheckCircleIcon class="w-6 h-6 drop-shadow-md" />
                </div>

                <div class="mx-auto h-16 w-16 mb-3 overflow-hidden rounded-full border-2 transition-colors"
                     :class="form.barber_id === barber.id ? 'border-gold' : 'border-theme-border'">
                  <img v-if="barber.avatar" :src="barber.avatar" :alt="barber.name" class="h-full w-full object-cover transition-transform group-hover:scale-110" />
                  <UserIcon v-else class="h-full w-full p-3 text-ivory/30 bg-theme-surface" />
                </div>
                
                <p class="font-display text-lg leading-tight text-theme-text transition-colors" :class="{ 'text-gold': form.barber_id === barber.id }">{{ barber.name }}</p>
                <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-black/20 px-2 py-0.5 border border-white/5">
                  <div class="h-1.5 w-1.5 rounded-full" :class="barber.status === 'active' ? 'bg-emerald-400' : 'bg-amber-400'"></div>
                  <span class="text-[10px] uppercase tracking-wider font-bold" :class="barber.status === 'active' ? 'text-emerald-400' : 'text-amber-400'">
                    {{ barber.status === 'active' ? 'Available' : 'Not Active' }}
                  </span>
                </div>
              </div>
            </div>

            </div>

            <!-- Step 2: The Service (Only shown if Barber is selected) -->
            <transition name="fade-slide">
              <div v-if="form.barber_id" class="mt-12">
                <div class="w-full h-px bg-gradient-to-r from-transparent via-theme-border to-transparent mb-12"></div>

                <div class="flex items-center justify-between mb-8">
                  <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">2</span>
                    The <span class="text-gold">Service</span>
                  </h2>
                </div>

                <!-- Services Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-12">
                  <div 
                    v-for="service in filteredServices" 
                    :key="service.id"
                    @click="selectService(service.id)"
                    class="group flex flex-col justify-between rounded-2xl border p-5 cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-gold/10"
                    :class="form.service_id === service.id ? 'border-gold bg-gradient-to-br from-gold/10 to-transparent shadow-[0_0_20px_rgba(212,175,55,0.15)]' : 'border-theme-border bg-theme-bg/50 hover:border-gold/30'"
                  >
                    <div class="flex justify-between items-start mb-4">
                      <div class="p-2.5 rounded-xl transition-colors" :class="form.service_id === service.id ? 'bg-gold text-obsidian shadow-lg shadow-gold/20' : 'bg-theme-surface text-gold group-hover:bg-gold/10'">
                        <ScissorsIcon class="w-6 h-6" />
                      </div>
                      <div v-if="form.service_id === service.id" class="text-gold">
                        <CheckCircleIcon class="w-6 h-6 drop-shadow-md" />
                      </div>
                    </div>
                    
                    <div>
                      <h3 class="font-display text-xl text-theme-text transition-colors" :class="{ 'text-gold': form.service_id === service.id }">{{ service.name }}</h3>
                      <p class="text-sm text-theme-muted mt-1 line-clamp-2">{{ service.description }}</p>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-white/5 flex items-center justify-between">
                      <span class="text-xs uppercase tracking-widest text-ivory/50">Price</span>
                      <span class="font-display text-2xl text-theme-text transition-colors" :class="{ 'text-gold': form.service_id === service.id }">₦{{ service.price.toLocaleString() }}</span>
                    </div>
                  </div>
                  <div v-if="filteredServices.length === 0" class="col-span-full rounded-2xl border border-dashed border-theme-border py-8 text-center bg-theme-bg/30 text-ivory/40">
                    No services match this barber's specialties.
                  </div>
                </div>
              </div>
            </transition>

            <!-- Step 3: Date & Time (Only shown if Service is selected) -->
            <transition name="fade-slide">
              <div v-if="form.service_id" class="mt-12">
                <div class="w-full h-px bg-gradient-to-r from-transparent via-theme-border to-transparent mb-12"></div>

                <div class="flex items-center justify-between mb-8">
                  <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">3</span>
                    Date & <span class="text-gold">Time</span>
                  </h2>
                </div>

                <!-- Date and Time -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-12">
                  <div>
                    <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block">Select Date</label>
                    <div class="relative group">
                      <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gold/50 group-hover:text-gold transition-colors">
                        <CalendarDaysIcon class="h-6 w-6" />
                      </div>
                      <input 
                        v-model="form.appointment_date" 
                        type="date" 
                        class="w-full rounded-2xl border border-theme-border bg-theme-bg/80 pl-14 pr-6 py-4 text-base text-theme-text outline-none transition-all focus:border-gold focus:bg-theme-bg focus:ring-4 focus:ring-gold/10 hover:border-gold/30 [color-scheme:dark]" 
                        @change="loadSlots" 
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block flex justify-between">
                      <span>Select Time</span>
                      <span v-if="loadingSlots" class="text-gold italic normal-case flex items-center gap-1">
                        <ArrowPathIcon class="w-3 h-3 animate-spin" /> checking...
                      </span>
                    </label>
                    
                    <div v-if="slots.length > 0" class="grid grid-cols-3 gap-2">
                      <button 
                        v-for="slot in slots" :key="slot"
                        type="button"
                        @click="form.appointment_time = slot"
                        class="rounded-xl py-3 text-sm font-bold transition-all duration-200 border"
                        :class="form.appointment_time === slot ? 'bg-gold text-obsidian border-gold shadow-[0_0_15px_rgba(212,175,55,0.3)] scale-105 z-10' : 'bg-theme-bg/50 border-theme-border text-theme-text hover:border-gold/40 hover:bg-theme-surface'"
                      >
                        {{ slot }}
                      </button>
                    </div>
                    
                    <div v-else class="h-[58px] rounded-2xl border border-dashed border-theme-border flex items-center justify-center text-sm text-ivory/40 italic bg-theme-bg/30">
                      <span v-if="form.barber_id && form.service_id && form.appointment_date">No slots available</span>
                      <span v-else>Select options first</span>
                    </div>
                  </div>
                </div>

                <!-- Notes & Submit -->
                <transition name="fade-slide">
                  <div v-if="form.appointment_time" class="space-y-6">
                    <div>
                      <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block">Special Requests (Optional)</label>
                      <textarea 
                        v-model="form.notes" 
                        rows="2" 
                        class="w-full rounded-2xl border border-theme-border bg-theme-bg/80 px-6 py-4 text-sm text-theme-text placeholder-theme-muted/50 outline-none transition-all focus:border-gold focus:bg-theme-bg focus:ring-4 focus:ring-gold/10 hover:border-gold/30 resize-none" 
                        placeholder="Any particular styling instructions?"
                      ></textarea>
                    </div>

                    <button 
                      type="submit"
                      class="group w-full relative flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-gold via-gold-light to-gold p-1 shadow-[0_0_30px_rgba(212,175,55,0.2)] transition-all duration-300 hover:shadow-[0_0_40px_rgba(212,175,55,0.4)] hover:scale-[1.01]"
                    >
                      <div class="w-full bg-obsidian rounded-xl px-8 py-4 transition-all duration-300 group-hover:bg-transparent">
                        <div class="flex items-center justify-center gap-3 text-gold group-hover:text-obsidian transition-colors font-display text-xl">
                          <span>Confirm Booking</span>
                          <svg class="h-5 w-5 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </div>
                      </div>
                    </button>
                  </div>
                </transition>
              </div>
            </transition>
            
            
          </form>
        </div>

        <!-- Right: History & Receipts -->
        <div class="lg:col-span-5 space-y-8">
          
          <div class="rounded-3xl border border-theme-border bg-theme-surface/60 backdrop-blur-xl p-6 md:p-8 lg:sticky lg:top-8 shadow-2xl">
            <div class="flex items-center justify-between mb-8">
              <h2 class="font-display text-2xl md:text-3xl text-theme-text">Booking <span class="text-gold">History</span></h2>
              <button class="rounded-full bg-theme-bg p-2.5 text-ivory/40 hover:bg-gold/10 hover:text-gold hover:rotate-180 transition-all duration-500 border border-theme-border" @click="loadBookings" title="Refresh">
                <ArrowPathIcon class="h-5 w-5" :class="{ 'animate-spin': loadingBookings }" />
              </button>
            </div>
            
            <div v-if="loadingBookings" class="py-20 flex justify-center">
              <div class="h-10 w-10 animate-spin rounded-full border-4 border-gold border-t-transparent shadow-[0_0_15px_rgba(212,175,55,0.5)]"></div>
            </div>

            <div v-else class="space-y-4 max-h-[800px] overflow-y-auto pr-2 custom-scrollbar">
              <article 
                v-for="booking in bookings.data || bookings" 
                :key="booking.id" 
                class="group relative rounded-2xl border p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl bg-theme-bg/40 border-theme-border hover:border-gold/30 hover:bg-theme-surface/80"
              >
                <!-- Status Badge -->
                <div class="absolute top-5 right-5">
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
                </div>

                <div class="flex gap-4">
                  <div class="mt-1 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-theme-surface border border-theme-border text-gold shadow-inner">
                    <span class="font-display text-xl">{{ new Date(booking.appointment_date).getDate() }}</span>
                  </div>
                  <div class="pr-16">
                    <p class="font-display text-xl text-theme-text">{{ booking.service?.name }}</p>
                    <div class="mt-2 space-y-1.5">
                      <p class="flex items-center gap-2 text-sm text-ivory/60">
                        <CalendarIcon class="h-4 w-4 text-gold/50" />
                        <span>{{ formatDate(booking.appointment_date) }} at <span class="text-gold font-bold">{{ booking.appointment_time }}</span></span>
                      </p>
                      <p class="flex items-center gap-2 text-sm text-ivory/60">
                        <UserIcon class="h-4 w-4 text-gold/50" />
                        <span>With {{ booking.barber?.name }}</span>
                      </p>
                    </div>
                  </div>
                </div>

                <div v-if="booking.notes" class="mt-4 rounded-xl bg-black/20 p-3 text-xs text-ivory/50 border border-white/5 border-l-2 border-l-gold">
                  "{{ booking.notes }}"
                </div>

                <!-- Actions -->
                <div v-if="booking.status === 'pending' || booking.status === 'confirmed'" class="mt-5 pt-4 border-t border-white/5 flex gap-3">
                  <button v-if="booking.status === 'pending'" class="flex-1 rounded-xl bg-gold py-2.5 text-xs font-bold text-obsidian hover:bg-gold-light hover:shadow-[0_0_15px_rgba(212,175,55,0.4)] transition-all flex items-center justify-center gap-2" @click="pay(booking)">
                    <CreditCardIcon class="h-4 w-4" /> Pay ₦{{ (booking.service?.price || 0).toLocaleString() }}
                  </button>
                  <button class="flex-1 rounded-xl bg-red-500/10 py-2.5 text-xs font-bold text-red-400 hover:bg-red-500 hover:text-white transition-all border border-red-500/20" @click="cancel(booking.id)">
                    Cancel Booking
                  </button>
                </div>
              </article>
              
              <div v-if="(bookings.data || bookings).length === 0" class="rounded-2xl border border-dashed border-theme-border py-16 text-center bg-theme-bg/30">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-theme-surface mb-4">
                  <CalendarIcon class="h-8 w-8 text-gold/30" />
                </div>
                <p class="text-theme-text font-display text-xl mb-2">No Bookings Yet</p>
                <p class="text-sm text-ivory/40 max-w-xs mx-auto">Your upcoming and past appointments will appear here.</p>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { customerApi } from '../api/customer.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { 
  CalendarDaysIcon, 
  ArrowPathIcon, 
  ScissorsIcon, 
  UserIcon, 
  CreditCardIcon,
  CalendarIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

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
const loadingSlots = ref(false);

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

async function loadOptions() {
  try {
    const [servicesResponse, barbersResponse] = await Promise.all([publicApi.services(), publicApi.barbers()]);
    services.value = servicesResponse.data.data;
    barbers.value = barbersResponse.data.data;
    
    if (route.query.service) {
      form.service_id = route.query.service;
      router.replace({ query: {} });
    }
  } catch (err) {
    toast.error('Failed to load form options');
  }
}

const filteredServices = computed(() => {
  if (!form.barber_id) return [];
  const selectedBarber = barbers.value.find(b => b.id === form.barber_id);
  if (!selectedBarber || !selectedBarber.specialties || selectedBarber.specialties.length === 0) {
    return services.value; // Show all if barber has no specific specialties mapped
  }
  
  // Filter services if their name or category loosely matches a barber's specialty
  const specialtiesStr = selectedBarber.specialties.join(' ').toLowerCase();
  
  const filtered = services.value.filter(s => {
    const sName = s.name.toLowerCase();
    return selectedBarber.specialties.some(spec => {
      const specLower = spec.toLowerCase();
      return sName.includes(specLower) || specLower.includes(sName);
    });
  });
  
  // Fallback: If strict filtering returns nothing, just show all services
  return filtered.length > 0 ? filtered : services.value;
});

function selectBarber(id) {
  if (form.barber_id !== id) {
    form.barber_id = id;
    form.service_id = ''; // Reset service when barber changes
    form.appointment_time = '';
    slots.value = [];
  }
  loadSlots();
}

function selectService(id) {
  form.service_id = id;
  loadSlots();
}

async function loadSlots() {
  if (!form.barber_id || !form.service_id || !form.appointment_date) {
    slots.value = [];
    return;
  }

  loadingSlots.value = true;
  try {
    const response = await publicApi.availableSlots({
      barber_id: form.barber_id,
      service_id: form.service_id,
      date: form.appointment_date,
    });
    slots.value = response.data.data;
    
    // Clear time if previously selected slot is no longer available
    if (form.appointment_time && !slots.value.includes(form.appointment_time)) {
      form.appointment_time = '';
    }
  } catch (err) {
    toast.error('Failed to load available slots');
  } finally {
    loadingSlots.value = false;
  }
}

async function submit() {
  if (!form.barber_id) { toast.warning('Please select a barber'); return; }
  if (!form.service_id) { toast.warning('Please select a service'); return; }
  if (!form.appointment_date) { toast.warning('Please select a date'); return; }
  if (!form.appointment_time) { toast.warning('Please select a time slot'); return; }
  
  try {
    await customerApi.createBooking(form);
    toast.success('Booking created successfully! Please pay to confirm.');
    
    // Reset form
    form.appointment_date = '';
    form.appointment_time = '';
    form.notes = '';
    slots.value = [];
    await loadBookings();
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create booking');
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
    const amount = booking.service?.price || 5000;
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

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.5s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.02);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(212,175,55,0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(212,175,55,0.4);
}
</style>