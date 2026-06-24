<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in max-w-4xl mx-auto pb-10">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-emerald-500/10 blur-[100px]"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
              <UserPlusIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-emerald-400/80 font-bold">Registration</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            New <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-emerald-200">Walk-In</span>
          </h1>
        </div>
      </div>

      <!-- Form Card -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#111111]/80 shadow-2xl backdrop-blur-xl p-8 lg:p-10 relative overflow-hidden">
        <div class="absolute -left-32 -bottom-32 h-64 w-64 rounded-full bg-purple-500/10 blur-[80px]"></div>

        <div class="space-y-8 relative z-10">
          <!-- Customer Details -->
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-emerald-400/60 font-bold mb-5 flex items-center gap-2">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              Customer Details
            </p>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-white/60 mb-2">Customer Name *</label>
                <input v-model="walkInForm.customer_name" type="text" placeholder="e.g. John Doe" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 placeholder:text-white/20 transition-all" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-white/60 mb-2">Phone Number *</label>
                  <input v-model="walkInForm.customer_phone" type="tel" placeholder="080xxxxxxxx" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 placeholder:text-white/20 transition-all" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-white/60 mb-2">Email <span class="text-white/30 font-normal">(optional)</span></label>
                  <input v-model="walkInForm.customer_email" type="email" placeholder="john@email.com" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 placeholder:text-white/20 transition-all" />
                </div>
              </div>
            </div>
          </div>

          <!-- Appointment Details -->
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-emerald-400/60 font-bold mb-5 flex items-center gap-2 pt-6 border-t border-white/5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              Appointment Details
            </p>
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-white/60 mb-2">Barber *</label>
                  <select v-model="walkInForm.barber_id" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer">
                    <option value="" disabled class="bg-[#111]">Select a barber...</option>
                    <option v-for="b in barbersList" :key="b.id" :value="b.id" class="bg-[#111]">{{ b.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-white/60 mb-2">Service *</label>
                  <select v-model="walkInForm.service_id" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer">
                    <option value="" disabled class="bg-[#111]">Select a service...</option>
                    <option v-for="svc in servicesList" :key="svc.id" :value="svc.id" class="bg-[#111]">{{ svc.name }} — ₦{{ Number(svc.price).toLocaleString() }}</option>
                  </select>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-white/60 mb-2">Date</label>
                  <input v-model="walkInForm.appointment_date" type="date" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 transition-all" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-white/60 mb-2">Time</label>
                  <input v-model="walkInForm.appointment_time" type="time" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 transition-all" />
                </div>
              </div>
            </div>
          </div>

          <!-- Payment -->
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-emerald-400/60 font-bold mb-5 flex items-center gap-2 pt-6 border-t border-white/5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              Payment
            </p>
            <div>
              <label class="block text-xs font-bold text-white/60 mb-2">Payment Method *</label>
              <select v-model="walkInForm.payment_method" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] px-5 py-4 text-sm text-white outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none cursor-pointer">
                <option value="cash" class="bg-[#111]">💵 Cash</option>
                <option value="pos" class="bg-[#111]">💳 POS / Card</option>
                <option value="transfer" class="bg-[#111]">🏦 Bank Transfer</option>
                <option value="pending" class="bg-[#111]">⏳ Not Yet Paid</option>
              </select>
            </div>
          </div>
        </div>

        <div class="mt-10 flex items-center justify-end gap-4 pt-8 border-t border-white/5 relative z-10">
          <router-link to="/admin/appointments" class="px-6 py-4 rounded-2xl text-sm font-bold text-white/50 hover:text-white border border-white/10 hover:bg-white/5 transition-all">
            Cancel
          </router-link>
          <button
            @click="submitWalkIn"
            :disabled="walkInSubmitting || !walkInForm.customer_name || !walkInForm.customer_phone || !walkInForm.service_id || !walkInForm.barber_id"
            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-sm font-bold text-obsidian shadow-lg shadow-emerald-500/20 transition-all hover:shadow-[0_8px_30px_rgba(16,185,129,0.4)] disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-1"
          >
            <span v-if="walkInSubmitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
            <CheckCircleIcon v-else class="h-5 w-5" />
            {{ walkInSubmitting ? 'Creating...' : 'Create Appointment' }}
          </button>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { UserPlusIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const toast = useToast();

const walkInSubmitting = ref(false);
const servicesList = ref([]);
const barbersList = ref([]);
const walkInForm = ref({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  barber_id: '',
  service_id: '',
  appointment_date: new Date().toISOString().split('T')[0],
  appointment_time: new Date().toTimeString().slice(0, 5),
  payment_method: 'cash'
});

async function loadServicesAndBarbers() {
  try {
    const [sRes, bRes] = await Promise.all([
      adminApi.services(),
      adminApi.barbers()
    ]);
    servicesList.value = sRes.data.data || [];
    barbersList.value = (bRes.data.data || []).map(b => ({ id: b.id, name: b.name || b.user?.name || 'Barber' }));
  } catch (e) {
    // silently fail
  }
}

async function submitWalkIn() {
  if (!walkInForm.value.customer_name || !walkInForm.value.customer_phone || !walkInForm.value.service_id || !walkInForm.value.barber_id) {
    toast.error('Please fill in all required fields');
    return;
  }
  walkInSubmitting.value = true;
  try {
    await adminApi.createWalkIn(walkInForm.value);
    toast.success('Walk-in appointment created successfully!');
    router.push('/admin/appointments');
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create walk-in appointment');
  } finally {
    walkInSubmitting.value = false;
  }
}

onMounted(() => {
  loadServicesAndBarbers();
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
