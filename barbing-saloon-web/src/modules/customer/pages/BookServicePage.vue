<template>
  <CustomerLayout>
    <div class="animate-fade-in pb-20 max-w-4xl mx-auto">
      
      <!-- Header -->
      <section class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8 mb-12 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
          <button @click="goBack" class="mb-6 flex items-center gap-2 text-sm text-ivory/50 hover:text-gold transition-colors">
            <ArrowLeftIcon class="h-4 w-4" /> Back to Services
          </button>
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Book Appointment</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
            Secure your <span class="text-gold">Seat</span>
          </h1>
        </div>
      </section>

      <!-- Loading State -->
      <div v-if="loadingService" class="flex justify-center py-20">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-gold border-t-transparent shadow-[0_0_15px_rgba(212,175,55,0.5)]"></div>
      </div>

      <div v-else-if="!service" class="text-center py-20 bg-theme-surface/60 rounded-3xl border border-theme-border">
        <p class="text-ivory/50">Service not found.</p>
        <button @click="router.push('/customer/dashboard/services')" class="mt-4 text-gold hover:underline">Go back to services</button>
      </div>

      <div v-else class="space-y-8">
        
        <!-- Selected Service & Barber Summary -->
        <div class="bg-theme-surface/80 backdrop-blur-xl border border-gold/20 rounded-2xl p-6 shadow-xl">
          <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="h-24 w-24 rounded-xl overflow-hidden shrink-0 border border-gold/30">
              <img v-if="service.image" :src="service.image" :alt="service.name" class="h-full w-full object-cover" />
              <div v-else class="h-full w-full bg-theme-bg flex items-center justify-center">
                <ScissorsIcon class="w-8 h-8 text-gold/50" />
              </div>
            </div>
            <div class="flex-1 text-center sm:text-left">
              <span class="text-[10px] uppercase tracking-widest text-gold/70">{{ service.category?.name || 'Service' }}</span>
              <h2 class="font-display text-2xl font-bold text-theme-text mt-1">{{ service.name }}</h2>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 mt-3 text-sm text-ivory/60">
                <span class="flex items-center gap-1.5"><ClockIcon class="w-4 h-4 text-gold/50" /> {{ service.duration_minutes }} mins</span>
                <span class="hidden sm:inline text-theme-border">•</span>
                <span class="flex items-center gap-1.5 font-bold text-theme-text"><CurrencyDollarIcon class="w-4 h-4 text-gold/50" /> ₦{{ Number(service.price).toLocaleString() }}</span>
              </div>
            </div>
          </div>

          <!-- Barber tied to this service -->
          <div v-if="service.barber?.name" class="mt-6 pt-5 border-t border-white/5">
            <div class="flex items-center gap-4 rounded-xl bg-white/[0.04] border border-white/5 p-4">
              <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full border-2 border-gold/40">
                <img v-if="service.barber.avatar" :src="getFullImageUrl(service.barber.avatar)" :alt="service.barber.name" class="h-full w-full object-cover" />
                <div v-else class="h-full w-full bg-gold/10 flex items-center justify-center text-gold font-bold text-lg">
                  {{ service.barber.name.charAt(0) }}
                </div>
              </div>
              <div>
                <p class="text-[10px] uppercase tracking-widest text-gold/60 mb-0.5">Your Barber</p>
                <p class="text-lg font-display font-bold text-theme-text">{{ service.barber.name }}</p>
              </div>
              <div class="ml-auto">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1 border border-emerald-500/20">
                  <div class="h-1.5 w-1.5 rounded-full bg-emerald-400"></div>
                  <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-400">Auto-Selected</span>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Booking Form: Date & Time only -->
        <form @submit.prevent="submit" class="bg-theme-surface/60 backdrop-blur-xl border border-white/5 rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-gold/5 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

          <!-- Step 1: Date & Time -->
          <div class="flex items-center justify-between mb-8">
            <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">1</span>
              Date &amp; <span class="text-gold">Time</span>
            </h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div>
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block">Select Date</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gold/50 group-hover:text-gold transition-colors">
                  <CalendarDaysIcon class="h-6 w-6" />
                </div>
                <input 
                  v-model="form.appointment_date" 
                  type="date" 
                  :min="todayStr"
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
                <span v-if="form.appointment_date">No slots available</span>
                <span v-else>Pick a date first</span>
              </div>
            </div>
          </div>

          <!-- Step 2: Notes & Submit -->
          <transition name="fade-slide">
            <div v-if="form.appointment_time" key="step2">
              <div class="w-full h-px bg-gradient-to-r from-transparent via-theme-border to-transparent my-8"></div>
              
              <div class="flex items-center justify-between mb-8">
                <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
                  <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">2</span>
                  Special <span class="text-gold">Requests</span>
                </h2>
              </div>
              
              <div class="space-y-6">
                <div>
                  <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block">Notes (Optional)</label>
                  <textarea 
                    v-model="form.notes" 
                    rows="2" 
                    class="w-full rounded-2xl border border-theme-border bg-theme-bg/80 px-6 py-4 text-sm text-theme-text placeholder-theme-muted/50 outline-none transition-all focus:border-gold focus:bg-theme-bg focus:ring-4 focus:ring-gold/10 hover:border-gold/30 resize-none" 
                    placeholder="Any particular styling instructions?"
                  ></textarea>
                </div>

                <!-- Booking Summary -->
                <div class="rounded-2xl border border-gold/20 bg-gold/5 p-5">
                  <p class="text-xs uppercase tracking-widest text-gold/70 mb-3 font-bold">Booking Summary</p>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-ivory/50">Service</span><span class="text-theme-text font-semibold">{{ service.name }}</span></div>
                    <div class="flex justify-between"><span class="text-ivory/50">Barber</span><span class="text-theme-text font-semibold">{{ service.barber?.name || 'Any Available' }}</span></div>
                    <div class="flex justify-between"><span class="text-ivory/50">Date</span><span class="text-theme-text font-semibold">{{ formatDate(form.appointment_date) }}</span></div>
                    <div class="flex justify-between"><span class="text-ivory/50">Time</span><span class="text-gold font-bold">{{ form.appointment_time }}</span></div>
                    <div class="flex justify-between border-t border-gold/20 pt-2 mt-2"><span class="text-ivory/50">Total</span><span class="text-gold font-display text-lg font-bold">₦{{ Number(service.price).toLocaleString() }}</span></div>
                  </div>
                </div>

                <button 
                  type="submit"
                  :disabled="isSubmitting"
                  class="group w-full relative flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-gold via-gold-light to-gold p-1 shadow-[0_0_30px_rgba(212,175,55,0.2)] transition-all duration-300 hover:shadow-[0_0_40px_rgba(212,175,55,0.4)] hover:scale-[1.01] disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  <div class="w-full bg-obsidian rounded-xl px-8 py-4 transition-all duration-300 group-hover:bg-transparent flex items-center justify-center">
                    <div class="flex items-center justify-center gap-3 text-gold group-hover:text-obsidian transition-colors font-display text-xl">
                      <ArrowPathIcon v-if="isSubmitting" class="w-5 h-5 animate-spin" />
                      <span>{{ isSubmitting ? 'Confirming...' : 'Confirm Booking' }}</span>
                      <svg v-if="!isSubmitting" class="h-5 w-5 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </div>
                  </div>
                </button>
              </div>
            </div>
          </transition>
        </form>
      </div>

    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { customerApi } from '../api/customer.api';
import { useToast } from '../../../core/composables/useToast';
import { 
  ArrowLeftIcon,
  CalendarDaysIcon, 
  ArrowPathIcon, 
  ScissorsIcon, 
  UserIcon, 
  CheckCircleIcon,
  ClockIcon,
  CurrencyDollarIcon
} from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const serviceId = route.params.serviceId;

const form = reactive({ barber_id: '', service_id: serviceId, appointment_date: '', appointment_time: '', notes: '' });
const service = ref(null);
const slots = ref([]);

const loadingService = ref(true);
const loadingSlots = ref(false);
const isSubmitting = ref(false);

const API_ROOT = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000';

const todayStr = new Date().toISOString().split('T')[0];

function getFullImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `${API_ROOT}${path.startsWith('/') ? '' : '/'}${path}`;
}

function goBack() {
  router.back();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

async function loadData() {
  loadingService.value = true;
  try {
    const servicesResponse = await publicApi.services();
    
    // Find the specific service
    const foundService = servicesResponse.data.data.find(s => s.id == serviceId);
    if (!foundService) {
      toast.error('Service not found');
      loadingService.value = false;
      return;
    }
    
    service.value = foundService;
    
    // Auto-select the barber tied to this service
    if (foundService.barber_id) {
      form.barber_id = foundService.barber_id;
    }
    
  } catch (err) {
    toast.error('Failed to load booking details');
  } finally {
    loadingService.value = false;
  }
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
  if (!form.barber_id) { toast.warning('No barber assigned to this service'); return; }
  if (!form.service_id) { toast.warning('Please select a service'); return; }
  if (!form.appointment_date) { toast.warning('Please select a date'); return; }
  if (!form.appointment_time) { toast.warning('Please select a time slot'); return; }
  
  isSubmitting.value = true;
  try {
    await customerApi.createBooking(form);
    toast.success('Booking created successfully! Please pay to confirm.');
    router.push('/customer/dashboard/bookings');
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create booking');
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  loadData();
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
</style>
