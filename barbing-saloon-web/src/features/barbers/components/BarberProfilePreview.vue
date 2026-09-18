<template>
  <div class="lg:sticky lg:top-8 space-y-6">
    <div class="flex items-center gap-2 px-2">
      <EyeIcon class="h-4 w-4 text-white/30" />
      <h3 class="text-[10px] uppercase tracking-[0.2em] font-bold text-white/30">Live Profile Preview</h3>
    </div>
    
    <div class="group relative flex flex-col rounded-[2.5rem] border border-white/[0.08] bg-gradient-to-br from-[#1a1a1a] to-[#111] overflow-hidden shadow-[0_20px_60px_rgba(0,0,0,0.6)]">
      <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay"></div>
      
      <div class="p-8 flex-1 flex flex-col relative z-10">
        <div class="flex flex-col items-center text-center mb-6">
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
</template>

<script setup>
import { EyeIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: Object,
  parsedSpecialties: Array
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
</script>
