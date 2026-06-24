<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in max-w-4xl mx-auto">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex items-center justify-between">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Registration</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              New <span class="text-gold">Walk-In</span>
            </h1>
          </div>
          <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gold/10 border border-gold/30">
            <UserPlusIcon class="h-8 w-8 text-gold" />
          </div>
        </div>
      </div>

      <!-- Form Card -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#111111]/80 shadow-2xl backdrop-blur-xl p-8 lg:p-10">
        <div class="space-y-8">
          <!-- Customer Details -->
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/60 font-bold mb-5 flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-gold"></span>
              Customer Details
            </p>
            <div class="space-y-5">
              <div>
                <label class="block text-sm font-bold text-ivory/60 mb-2">Customer Name *</label>
                <input v-model="walkInForm.customer_name" type="text" placeholder="e.g. John Doe" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text placeholder-ivory/20 outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all text-lg" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="block text-sm font-bold text-ivory/60 mb-2">Phone Number *</label>
                  <input v-model="walkInForm.customer_phone" type="tel" placeholder="080xxxxxxxx" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text placeholder-ivory/20 outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all text-lg" />
                </div>
                <div>
                  <label class="block text-sm font-bold text-ivory/60 mb-2">Email <span class="text-ivory/30 font-normal">(optional)</span></label>
                  <input v-model="walkInForm.customer_email" type="email" placeholder="john@email.com" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text placeholder-ivory/20 outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all text-lg" />
                </div>
              </div>
            </div>
          </div>

          <!-- Appointment Details -->
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/60 font-bold mb-5 flex items-center gap-2 pt-4 border-t border-white/5">
              <span class="w-2 h-2 rounded-full bg-gold"></span>
              Appointment Details
            </p>
            <div class="space-y-5">
              <div>
                <label class="block text-sm font-bold text-ivory/60 mb-2">Service *</label>
                <select v-model="walkInForm.service_id" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all appearance-none cursor-pointer text-lg">
                  <option value="" disabled class="bg-obsidian">Select a service...</option>
                  <option v-for="svc in servicesList" :key="svc.id" :value="svc.id" class="bg-obsidian">{{ svc.name }} — ₦{{ Number(svc.price).toLocaleString() }}</option>
                </select>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="block text-sm font-bold text-ivory/60 mb-2">Date</label>
                  <input v-model="walkInForm.appointment_date" type="date" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all text-lg" />
                </div>
                <div>
                  <label class="block text-sm font-bold text-ivory/60 mb-2">Time</label>
                  <input v-model="walkInForm.appointment_time" type="time" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all text-lg" />
                </div>
              </div>
            </div>
          </div>

          <!-- Payment -->
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/60 font-bold mb-5 flex items-center gap-2 pt-4 border-t border-white/5">
              <span class="w-2 h-2 rounded-full bg-gold"></span>
              Payment
            </p>
            <div>
              <label class="block text-sm font-bold text-ivory/60 mb-2">Payment Method *</label>
              <select v-model="walkInForm.payment_method" class="w-full rounded-2xl border border-white/10 bg-black/40 px-5 py-4 text-theme-text outline-none focus:border-gold/50 focus:ring-2 focus:ring-gold/30 transition-all appearance-none cursor-pointer text-lg">
                <option value="cash" class="bg-obsidian">💵 Cash</option>
                <option value="pos" class="bg-obsidian">💳 POS / Card</option>
                <option value="transfer" class="bg-obsidian">🏦 Bank Transfer</option>
                <option value="pending" class="bg-obsidian">⏳ Not Yet Paid</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-10 flex items-center justify-end gap-4 pt-6 border-t border-white/5">
          <router-link to="/barber/appointments" class="px-6 py-4 rounded-2xl font-bold text-ivory/50 hover:text-ivory border border-white/10 hover:border-white/20 transition-all">
            Cancel
          </router-link>
          <button
            @click="submitWalkIn"
            :disabled="walkInSubmitting || !walkInForm.customer_name || !walkInForm.customer_phone || !walkInForm.service_id"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-gold text-lg font-bold text-obsidian shadow-lg shadow-gold/20 transition-all hover:bg-gold-light hover:shadow-[0_0_20px_rgba(212,175,55,0.4)] disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-1"
          >
            <ArrowPathIcon v-if="walkInSubmitting" class="h-6 w-6 animate-spin" />
            <CheckIcon v-else class="h-6 w-6" />
            {{ walkInSubmitting ? 'Registering...' : 'Complete Walk-In' }}
          </button>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from '../../../core/composables/useToast';
import { UserPlusIcon, ArrowPathIcon, CheckIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const toast = useToast();

const walkInSubmitting = ref(false);
const servicesList = ref([]);
const walkInForm = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  service_id: '',
  appointment_date: new Date().toISOString().split('T')[0],
  appointment_time: new Date().toTimeString().slice(0, 5),
  payment_method: 'cash'
});

async function loadServices() {
  try {
    const res = await barberApi.getServices();
    servicesList.value = res.data.data || [];
  } catch (e) {
    // Silently fail, dropdown will be empty
  }
}

async function submitWalkIn() {
  if (!walkInForm.value.customer_name || !walkInForm.value.customer_phone || !walkInForm.value.service_id) {
    toast.error('Please fill in all required fields');
    return;
  }
  walkInSubmitting.value = true;
  try {
    await barberApi.createWalkIn(walkInForm.value);
    toast.success('Walk-in appointment created successfully!');
    router.push('/barber/appointments');
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create walk-in appointment');
  } finally {
    walkInSubmitting.value = false;
  }
}

onMounted(() => {
  loadServices();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
