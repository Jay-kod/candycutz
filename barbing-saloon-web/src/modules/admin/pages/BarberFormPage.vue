<template>
  <AdminLayout>
    <section class="animate-fade-in pb-10">
      
      <!-- Premium Header -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6 backdrop-blur-3xl mb-8">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-amber-500/10 blur-[80px]"></div>
        
        <div class="relative z-10 flex items-center gap-6">
          <router-link to="/admin/barbers" class="group flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition-all hover:shadow-[0_0_20px_rgba(255,103,0,0.15)] hover:border-admin/30">
            <ArrowLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
          </router-link>
          <div>
            <div class="flex items-center gap-3 mb-1">
              <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">{{ isEditing ? 'Edit Profile' : 'New Staff' }}</p>
            </div>
            <h1 class="font-display text-3xl text-white drop-shadow-md">
              {{ isEditing ? 'Update Barber Details' : 'Add New Barber' }}
            </h1>
          </div>
        </div>
        
        <div class="relative z-10 hidden md:block">
           <button @click="submitForm" :disabled="submitting || loadingData" class="flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 py-3.5 px-8 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.4)] disabled:opacity-50 active:scale-[0.98]">
            <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
            <CheckIcon v-else class="h-5 w-5" />
            {{ submitting ? (isEditing ? 'Saving...' : 'Creating...') : 'Save Changes' }}
          </button>
        </div>
      </div>

      <div v-if="loadingData" class="flex justify-center py-32">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-admin/30 border-t-admin"></div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 items-start">
        
        <!-- Left Side: Live Profile Preview Card -->
        <div class="lg:sticky lg:top-8 space-y-6">
          <div class="flex items-center gap-2 px-2">
            <EyeIcon class="h-4 w-4 text-white/30" />
            <h3 class="text-[10px] uppercase tracking-[0.2em] font-bold text-white/30">Live Profile Preview</h3>
          </div>
          
          <div class="group relative flex flex-col rounded-[2.5rem] border border-white/[0.08] bg-gradient-to-br from-[#1a1a1a] to-[#111] overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.6)]">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay"></div>
            
            <div class="p-8 flex-1 flex flex-col relative z-10">
              <div class="flex flex-col items-center text-center mb-6">
                <!-- Large Avatar Preview -->
                <div class="relative h-28 w-28 overflow-hidden rounded-full bg-gradient-to-br from-admin/20 to-amber-500/20 flex items-center justify-center text-white font-display text-4xl border-[3px] border-admin/30 shadow-[0_0_30px_rgba(255,103,0,0.15)] mb-4">
                  <img v-if="form.avatar" :src="form.avatar" alt="Avatar" class="h-full w-full object-cover" />
                  <span v-else>{{ form.name ? form.name.charAt(0).toUpperCase() : 'B' }}</span>
                  
                  <div class="absolute bottom-1 right-1 h-5 w-5 rounded-full border-[3px] border-[#151515] shadow-sm flex items-center justify-center z-10" :class="statusBgColor(form.status)">
                  </div>
                </div>
                
                <h3 class="font-display font-bold text-white text-2xl leading-tight">{{ form.name || 'Barber Name' }}</h3>
                <p class="text-sm text-admin/80 font-bold mt-1 tracking-wider uppercase">{{ form.experience_years ? form.experience_years + ' Yrs Exp.' : '0 Yrs Exp.' }}</p>
                <div class="mt-2 text-xs text-white/40 flex items-center justify-center gap-1.5">
                  <EnvelopeIcon class="h-3.5 w-3.5" />
                  {{ form.email || 'email@candycutz.com' }}
                </div>
              </div>

              <!-- Specialties Tags Preview -->
              <div class="mt-2 pt-6 border-t border-white/[0.05] w-full">
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold mb-3 text-center">Specialties</p>
                <div class="flex flex-wrap justify-center gap-2">
                  <template v-if="parsedSpecialties.length > 0">
                    <span v-for="spec in parsedSpecialties" :key="spec" class="rounded-xl bg-white/[0.03] px-3 py-1.5 text-[10px] uppercase tracking-wider font-bold text-white/60 border border-white/[0.08] shadow-inner">
                      {{ spec }}
                    </span>
                  </template>
                  <span v-else class="text-[11px] text-white/20 italic bg-white/5 px-4 py-2 rounded-xl">No specialties listed</span>
                </div>
              </div>
              
              <div class="mt-6 pt-6 border-t border-white/[0.05] w-full text-center">
                 <p class="text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold mb-2">Short Bio</p>
                 <p class="text-xs text-white/50 italic leading-relaxed">{{ form.bio || 'This barber hasn\'t written a bio yet.' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: Forms -->
        <div class="space-y-8">
          
          <!-- Form Section 1: Credentials -->
          <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
            <div class="absolute -right-32 -top-32 h-64 w-64 rounded-full bg-admin/5 blur-[80px]"></div>
            
            <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
              <div class="p-2.5 rounded-xl bg-admin/10 border border-admin/20">
                <ShieldCheckIcon class="h-5 w-5 text-admin" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Account Credentials</h2>
                <p class="text-xs text-white/40 mt-0.5">Basic details and login information</p>
              </div>
            </div>
            
            <div class="p-8 space-y-6 relative z-10">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Full Name</label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-admin">
                      <UserIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
                    </div>
                    <input v-model="form.name" required type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="e.g. John Doe" />
                  </div>
                </div>
                
                <div class="space-y-2">
                  <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Email Address</label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <EnvelopeIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
                    </div>
                    <input v-model="form.email" required type="email" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="john@candycutz.com" />
                  </div>
                </div>
              </div>
              
              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">{{ isEditing ? 'Update Password (Optional)' : 'Initial Password' }}</label>
                <div class="relative group max-w-md">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <KeyIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
                  </div>
                  <input v-model="form.password" :required="!isEditing" type="password" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04] tracking-widest" placeholder="••••••••" />
                </div>
                <p class="text-[10px] text-white/30 ml-1 mt-1.5 flex items-center gap-1">
                  <InformationCircleIcon class="h-3 w-3" />
                  {{ isEditing ? 'Leave blank to keep current password.' : 'They will use this to log into the system.' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Form Section 2: Professional Profile -->
          <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
            <div class="absolute -left-32 bottom-0 h-64 w-64 rounded-full bg-purple-500/5 blur-[80px]"></div>
            
            <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
              <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20">
                <BriefcaseIcon class="h-5 w-5 text-amber-500" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Professional Profile</h2>
                <p class="text-xs text-white/40 mt-0.5">Set up their public-facing details</p>
              </div>
            </div>
            
            <div class="p-8 space-y-8 relative z-10">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Years of Experience</label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <AcademicCapIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
                    </div>
                    <input v-model.number="form.experience_years" required type="number" min="0" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="e.g. 5" />
                  </div>
                </div>
                
                <div class="space-y-2">
                  <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Account Status</label>
                  <div class="relative">
                    <select v-model="form.status" class="w-full rounded-2xl border border-white/[0.08] bg-[#1a1a1a] px-4 py-4 text-sm font-bold text-white outline-none transition-all focus:border-admin/50 focus:ring-4 focus:ring-admin/10 appearance-none cursor-pointer hover:border-white/20">
                      <option value="active">🟢 Active</option>
                      <option value="pending_approval">🟡 Pending Approval</option>
                      <option value="suspended">🔴 Suspended</option>
                      <option value="on_leave">⚪ On Leave</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold flex justify-between">
                  <span>Specialties</span>
                  <span class="text-white/20 font-normal normal-case tracking-normal">Separate with commas</span>
                </label>
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-4 flex top-4 pointer-events-none">
                    <SparklesIcon class="h-5 w-5 text-white/30 group-focus-within:text-admin/70 transition-colors" />
                  </div>
                  <input v-model="form.specialties" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 hover:bg-white/[0.04]" placeholder="Fades, Lineups, Scissor Cuts, Beard Trims" />
                </div>
              </div>
              
              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Biography</label>
                <div class="relative group">
                  <textarea v-model="form.bio" rows="4" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] p-5 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 resize-none hover:bg-white/[0.04]" placeholder="Write a short, engaging bio describing the barber's background and style..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Action Bar (Mobile only since desktop has header button) -->
          <div class="md:hidden flex gap-4 pt-4">
            <router-link to="/admin/barbers" class="flex-[1] flex items-center justify-center py-4 px-4 rounded-2xl border border-white/10 text-sm font-bold text-white/60 hover:text-white hover:bg-white/5 transition-all">
              Cancel
            </router-link>
            <button @click="submitForm" :disabled="submitting" class="flex-[2] flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 py-4 px-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.4)] disabled:opacity-50 active:scale-[0.98]">
              <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
              <CheckIcon v-else class="h-5 w-5" />
              {{ submitting ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>

        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { 
  EnvelopeIcon, 
  SparklesIcon, 
  AcademicCapIcon, 
  KeyIcon, 
  BriefcaseIcon, 
  CheckIcon,
  ShieldCheckIcon,
  ArrowLeftIcon,
  EyeIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline';
import { UserIcon } from '@heroicons/vue/24/solid';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const isEditing = computed(() => route.path.includes('/edit'));
const barberId = computed(() => route.params.id);

const loadingData = ref(false);
const submitting = ref(false);

const form = ref({
  name: '',
  email: '',
  password: '',
  experience_years: '',
  specialties: '',
  bio: '',
  status: 'active',
  avatar: ''
});

const parsedSpecialties = computed(() => {
  if (!form.value.specialties) return [];
  return form.value.specialties.split(',').map(s => s.trim()).filter(Boolean);
});

const statusBgColor = (status) => {
  const map = {
    active: 'bg-emerald-500',
    pending_approval: 'bg-amber-500',
    suspended: 'bg-red-500',
    on_leave: 'bg-white/50'
  };
  return map[status] || 'bg-white/20';
};

async function loadBarberData() {
  if (!isEditing.value) {
    form.value.status = 'pending_approval';
    return;
  }
  
  loadingData.value = true;
  try {
    const res = await adminApi.barbers();
    const barbers = res.data.data || [];
    const barber = barbers.find(b => String(b.id) === String(barberId.value));
    
    if (barber) {
      form.value = {
        name: barber.name || '',
        email: barber.email || '',
        password: '',
        experience_years: barber.experience_years || 0,
        specialties: (barber.specialties || []).join(', '),
        bio: barber.bio || '',
        status: barber.status || 'active',
        avatar: barber.avatar || ''
      };
    } else {
      toast.error('Barber not found');
      router.push('/admin/barbers');
    }
  } catch (error) {
    toast.error('Failed to load barber details');
  } finally {
    loadingData.value = false;
  }
}

async function submitForm() {
  if (submitting.value || loadingData.value) return;
  
  submitting.value = true;
  try {
    if (isEditing.value) {
      await adminApi.updateBarber(barberId.value, form.value);
      toast.success('Barber profile updated successfully!');
    } else {
      await adminApi.createBarber(form.value);
      toast.success('Barber added to team successfully!');
    }
    router.push('/admin/barbers');
  } catch (error) {
    if (error.response?.status === 409) {
      toast.error('A user with this email already exists');
    } else {
      toast.error(error.response?.data?.error || `Failed to ${isEditing.value ? 'update' : 'create'} barber`);
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  loadBarberData();
});
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
