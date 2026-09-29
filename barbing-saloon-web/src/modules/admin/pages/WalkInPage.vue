<template>
  <AdminLayout>
    <section class="max-w-7xl mx-auto pb-12 pt-4 px-4 sm:px-6 lg:px-8 animate-fade-in">
      
      <!-- Page Header with Glow -->
      <div class="relative rounded-[2.5rem] p-8 md:p-12 mb-8 bg-[#0a0a0a] border border-white/5 overflow-hidden shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-[120px] pointer-events-none group-hover:bg-emerald-500/20 transition-colors duration-700"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-teal-500/10 rounded-full blur-[120px] pointer-events-none group-hover:bg-teal-500/20 transition-colors duration-700"></div>
        
        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 mb-6">
            <UserPlusIcon class="h-4 w-4 text-emerald-400" />
            <span class="text-xs font-bold text-emerald-400 tracking-widest uppercase">Quick Register</span>
          </div>
          <h1 class="text-5xl md:text-6xl font-display font-bold text-white tracking-tight mb-2">
            New <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-200">Walk-In</span>
          </h1>
          <p class="text-white/40 text-sm max-w-md">Instantly register and check-in a customer who just walked through the doors.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8">
        
        <!-- Left Column: Form Sections -->
        <div class="space-y-6">
          
          <!-- Section 1: Customer -->
          <div class="bg-[#111] rounded-[2rem] border border-white/5 p-8 relative overflow-hidden group hover:border-emerald-500/30 transition-colors duration-500">
             <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
             
             <div class="flex items-center gap-4 mb-8 relative z-10">
               <div class="h-12 w-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white/50 group-hover:text-emerald-400 group-hover:bg-emerald-500/10 group-hover:border-emerald-500/20 transition-all duration-300">
                 <UserIcon class="h-6 w-6" />
               </div>
               <div>
                 <h2 class="text-xl font-display text-white font-bold">Customer Info</h2>
                 <p class="text-xs text-white/40">Basic details for the booking.</p>
               </div>
             </div>

             <div class="space-y-5 relative z-10">
                <div class="relative group/input">
                  <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Customer Name *</label>
                  <div class="relative">
                    <input v-model="walkInForm.customer_name" type="text" placeholder="John Doe" class="w-full bg-white/[0.02] border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer" />
                    <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/20 peer-focus:text-emerald-400 transition-colors" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div class="relative group/input">
                    <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Phone Number *</label>
                    <div class="relative">
                      <input v-model="walkInForm.customer_phone" type="tel" placeholder="080..." class="w-full bg-white/[0.02] border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer" />
                      <PhoneIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/20 peer-focus:text-emerald-400 transition-colors" />
                    </div>
                  </div>
                  <div class="relative group/input">
                    <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Email <span class="opacity-50 font-normal">(Optional)</span></label>
                    <div class="relative">
                      <input v-model="walkInForm.customer_email" type="email" placeholder="john@example.com" class="w-full bg-white/[0.02] border border-white/10 rounded-2xl py-4 pl-12 pr-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer" />
                      <EnvelopeIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/20 peer-focus:text-emerald-400 transition-colors" />
                    </div>
                  </div>
                </div>
             </div>
          </div>

          <!-- Section 2: Service & Barber -->
          <div class="bg-[#111] rounded-[2rem] border border-white/5 p-8 relative overflow-hidden group hover:border-emerald-500/30 transition-colors duration-500">
             <div class="flex items-center gap-4 mb-8 relative z-10">
               <div class="h-12 w-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white/50 group-hover:text-emerald-400 group-hover:bg-emerald-500/10 group-hover:border-emerald-500/20 transition-all duration-300">
                 <ScissorsIcon class="h-6 w-6" />
               </div>
               <div>
                 <h2 class="text-xl font-display text-white font-bold">Service Details</h2>
                 <p class="text-xs text-white/40">Select what they want and who will do it.</p>
               </div>
             </div>

             <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 relative z-10">
                <div class="relative group/input">
                  <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Select Barber *</label>
                  <div class="relative">
                    <select v-model="walkInForm.barber_id" class="w-full appearance-none bg-white/[0.02] border border-white/10 rounded-2xl py-4 pl-12 pr-10 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer cursor-pointer">
                      <option value="" disabled class="bg-[#1a1a1a]">Choose a barber...</option>
                      <option v-for="b in barbersList" :key="b.id" :value="b.id" class="bg-[#1a1a1a]">{{ b.name }}</option>
                    </select>
                    <UserCircleIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/20 peer-focus:text-emerald-400 transition-colors pointer-events-none" />
                    <ChevronDownIcon class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/40 peer-focus:text-emerald-400 transition-colors pointer-events-none" />
                  </div>
                </div>

                <div class="relative group/input">
                  <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Select Service *</label>
                  <div class="relative">
                    <select v-model="walkInForm.service_id" class="w-full appearance-none bg-white/[0.02] border border-white/10 rounded-2xl py-4 pl-12 pr-10 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer cursor-pointer">
                      <option value="" disabled class="bg-[#1a1a1a]">Choose a service...</option>
                      <option v-for="svc in servicesList" :key="svc.id" :value="svc.id" class="bg-[#1a1a1a]">{{ svc.name }}</option>
                    </select>
                    <SparklesIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/20 peer-focus:text-emerald-400 transition-colors pointer-events-none" />
                    <ChevronDownIcon class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/40 peer-focus:text-emerald-400 transition-colors pointer-events-none" />
                  </div>
                </div>
             </div>
          </div>

          <!-- Section 3: Schedule & Payment -->
          <div class="bg-[#111] rounded-[2rem] border border-white/5 p-8 relative overflow-hidden group hover:border-emerald-500/30 transition-colors duration-500">
             <div class="flex items-center gap-4 mb-8 relative z-10">
               <div class="h-12 w-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white/50 group-hover:text-emerald-400 group-hover:bg-emerald-500/10 group-hover:border-emerald-500/20 transition-all duration-300">
                 <CalendarIcon class="h-6 w-6" />
               </div>
               <div>
                 <h2 class="text-xl font-display text-white font-bold">Schedule & Payment</h2>
                 <p class="text-xs text-white/40">When and how are they paying?</p>
               </div>
             </div>

             <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 relative z-10">
                <div class="relative group/input">
                  <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Date</label>
                  <div class="relative">
                    <input v-model="walkInForm.appointment_date" type="date" class="w-full bg-white/[0.02] border border-white/10 rounded-2xl py-4 px-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer [color-scheme:dark]" />
                  </div>
                </div>

                <div class="relative group/input">
                  <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Time</label>
                  <div class="relative">
                    <input v-model="walkInForm.appointment_time" type="time" class="w-full bg-white/[0.02] border border-white/10 rounded-2xl py-4 px-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer [color-scheme:dark]" />
                  </div>
                </div>

                <div class="relative group/input">
                  <label class="block text-[10px] uppercase tracking-widest text-white/40 font-bold mb-2 ml-1">Payment Method *</label>
                  <div class="relative">
                    <select v-model="walkInForm.payment_method" class="w-full appearance-none bg-white/[0.02] border border-white/10 rounded-2xl py-4 pl-12 pr-10 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:bg-emerald-500/5 transition-all peer cursor-pointer">
                      <option value="cash" class="bg-[#1a1a1a]">Cash</option>
                      <option value="pos" class="bg-[#1a1a1a]">POS</option>
                      <option value="transfer" class="bg-[#1a1a1a]">Transfer</option>
                      <option value="pending" class="bg-[#1a1a1a]">Unpaid</option>
                    </select>
                    <BanknotesIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-white/20 peer-focus:text-emerald-400 transition-colors pointer-events-none" />
                    <ChevronDownIcon class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/40 peer-focus:text-emerald-400 transition-colors pointer-events-none" />
                  </div>
                </div>
             </div>
          </div>
        </div>

        <!-- Right Column: Live Summary "Receipt" & History -->
        <div class="lg:sticky lg:top-8 h-fit space-y-6">
          <div class="bg-[#0a0a0a] rounded-[2rem] border border-white/10 overflow-hidden shadow-2xl relative">
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="absolute top-0 right-0 p-32 bg-emerald-500/10 blur-[80px] pointer-events-none rounded-full"></div>
            
            <div class="p-8 pb-10 relative z-10">
              <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto bg-white/5 rounded-2xl border border-white/10 flex items-center justify-center mb-4">
                  <TicketIcon class="h-8 w-8 text-emerald-400 opacity-80" />
                </div>
                <h3 class="text-xl font-display font-bold text-white tracking-widest uppercase">Walk-In Ticket</h3>
                <p class="text-[10px] text-white/40 font-mono mt-1">{{ currentDateFormatted }}</p>
              </div>

              <div class="space-y-5">
                <div class="flex justify-between items-end border-b border-white/5 pb-4">
                  <div>
                    <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold mb-1">Customer</p>
                    <p class="text-sm text-white font-medium truncate max-w-[150px]">{{ walkInForm.customer_name || '---' }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold mb-1">Phone</p>
                    <p class="text-sm text-white font-medium">{{ walkInForm.customer_phone || '---' }}</p>
                  </div>
                </div>

                <div class="flex justify-between items-end border-b border-white/5 pb-4">
                  <div>
                    <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold mb-1">Service</p>
                    <p class="text-sm text-white font-medium">{{ selectedServiceName || '---' }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold mb-1">Barber</p>
                    <p class="text-sm text-white font-medium">{{ selectedBarberName || '---' }}</p>
                  </div>
                </div>

                <div class="flex justify-between items-end border-b border-white/5 pb-4">
                  <div>
                    <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold mb-1">Schedule</p>
                    <p class="text-sm text-white font-medium">{{ walkInForm.appointment_date }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold mb-1">Time</p>
                    <p class="text-sm text-white font-medium">{{ walkInForm.appointment_time }}</p>
                  </div>
                </div>

                <div class="flex justify-between items-center bg-white/[0.03] rounded-2xl p-5 border border-white/5 mt-6 relative overflow-hidden">
                  <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-transparent pointer-events-none"></div>
                  <span class="text-xs font-bold text-white/60 uppercase tracking-widest relative z-10">Total Price</span>
                  <span class="text-3xl font-bold text-emerald-400 font-mono relative z-10">₦{{ selectedServicePrice.toLocaleString() }}</span>
                </div>
              </div>
            </div>
            
            <!-- Ticket tear marks -->
            <div class="w-full flex justify-between px-2 pb-2">
               <div v-for="i in 20" :key="i" class="w-2 h-2 rounded-full bg-[#111]"></div>
            </div>
          </div>

          <div class="flex gap-4">
            <button
              @click="resetForm"
              class="flex-1 px-6 py-4 rounded-2xl text-xs font-bold text-white/50 hover:text-white border border-white/10 hover:bg-white/5 transition-all text-center uppercase tracking-widest flex items-center justify-center"
            >
              Reset
            </button>
            <button
              @click="submitWalkIn"
              :disabled="walkInSubmitting || !isFormValid"
              class="flex-[2] flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 text-xs font-bold text-obsidian uppercase tracking-widest shadow-lg shadow-emerald-500/20 transition-all hover:shadow-[0_8px_30px_rgba(16,185,129,0.4)] disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-1"
            >
              <span v-if="walkInSubmitting" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
              <CheckBadgeIcon v-else class="h-5 w-5" />
              {{ walkInSubmitting ? 'Creating...' : 'Create Walk-In' }}
            </button>
          </div>

          <!-- Recent Walk-Ins History Card -->
          <div class="bg-[#0a0a0a] rounded-[2rem] border border-white/10 overflow-hidden shadow-2xl p-6 relative">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-xs font-display font-bold text-white uppercase tracking-widest flex items-center gap-2">
                <ClockIcon class="h-4 w-4 text-emerald-400" />
                Recent Walk-Ins
              </h3>
              <router-link to="/admin/appointments" class="text-[10px] text-emerald-400 hover:text-emerald-300 uppercase tracking-widest font-bold">
                View All
              </router-link>
            </div>

            <div v-if="recentWalkIns.length === 0" class="text-xs text-white/40 text-center py-6 bg-white/[0.02] rounded-2xl border border-white/5">
              No recent walk-ins today.
            </div>

            <div v-else class="space-y-3">
              <div v-for="walkIn in recentWalkIns" :key="walkIn.id" class="flex items-center justify-between p-3 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/5 transition-colors">
                <div class="flex items-center gap-3 overflow-hidden">
                  <div class="h-9 w-9 rounded-full bg-emerald-500/10 flex items-center justify-center shrink-0 border border-emerald-500/20">
                    <UserIcon class="h-4 w-4 text-emerald-400" />
                  </div>
                  <div class="truncate">
                    <p class="text-xs font-bold text-white truncate">{{ walkIn.customer?.name || walkIn.client_name }}</p>
                    <p class="text-[10px] text-white/40 truncate">{{ walkIn.service?.name }} • {{ walkIn.appointment_time?.substring(0, 5) }}</p>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <p class="text-xs font-bold text-emerald-400 font-mono">₦{{ Number(walkIn.service?.price || 0).toLocaleString() }}</p>
                  <p class="text-[9px] uppercase tracking-wider font-bold mt-0.5" :class="walkIn.status === 'completed' ? 'text-emerald-400/80' : walkIn.status === 'cancelled' ? 'text-red-400/80' : 'text-amber-400/80'">{{ walkIn.status }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '../../../core/composables/useToast';
import { 
  UserPlusIcon, CheckCircleIcon, UserIcon, PhoneIcon, EnvelopeIcon,
  ScissorsIcon, UserCircleIcon, SparklesIcon, CalendarIcon, BanknotesIcon,
  TicketIcon, ChevronDownIcon, CheckBadgeIcon, ClockIcon
} from '@heroicons/vue/24/outline';

const router = useRouter();
const toast = useToast();

const walkInSubmitting = ref(false);
const servicesList = ref([]);
const barbersList = ref([]);
const recentWalkIns = ref([]);

const getInitialForm = () => ({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  barber_id: '',
  service_id: '',
  appointment_date: new Date().toISOString().split('T')[0],
  appointment_time: new Date().toTimeString().slice(0, 5),
  payment_method: 'cash'
});

const walkInForm = ref(getInitialForm());

const currentDateFormatted = computed(() => {
  return new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const selectedServiceName = computed(() => {
  if (!walkInForm.value.service_id) return '';
  const s = servicesList.value.find(x => x.id === walkInForm.value.service_id);
  return s ? s.name : '';
});

const selectedServicePrice = computed(() => {
  if (!walkInForm.value.service_id) return 0;
  const s = servicesList.value.find(x => x.id === walkInForm.value.service_id);
  return s ? Number(s.price) : 0;
});

const selectedBarberName = computed(() => {
  if (!walkInForm.value.barber_id) return '';
  const b = barbersList.value.find(x => x.id === walkInForm.value.barber_id);
  return b ? b.name : '';
});

const isFormValid = computed(() => {
  return walkInForm.value.customer_name && 
         walkInForm.value.customer_phone && 
         walkInForm.value.service_id && 
         walkInForm.value.barber_id;
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

async function loadRecentWalkIns() {
  try {
    const res = await adminApi.appointments();
    const data = res.data.data || [];
    const items = Array.isArray(data) ? data : (data.items || []);
    
    // Sort descending by ID or created_at to get latest
    recentWalkIns.value = items
      .filter(a => a.booking_type === 'walk_in' || a.source === 'walk_in')
      .sort((a, b) => b.id - a.id)
      .slice(0, 3);
  } catch (e) {
    // silently fail
  }
}

function resetForm() {
  walkInForm.value = getInitialForm();
}

async function submitWalkIn() {
  if (!isFormValid.value) {
    toast.error('Please fill in all required fields');
    return;
  }
  walkInSubmitting.value = true;
  try {
    await adminApi.createWalkIn(walkInForm.value);
    toast.success('Walk-in appointment created successfully!');
    resetForm();
    await loadRecentWalkIns();
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create walk-in appointment');
  } finally {
    walkInSubmitting.value = false;
  }
}

onMounted(() => {
  loadServicesAndBarbers();
  loadRecentWalkIns();
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
