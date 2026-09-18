<template>
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
</template>

<script setup>
import { BriefcaseIcon, AcademicCapIcon, SparklesIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: Object
});
</script>
