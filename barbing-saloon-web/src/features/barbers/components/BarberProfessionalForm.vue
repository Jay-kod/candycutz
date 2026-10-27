<template>
  <div class="rounded-[2.5rem] border border-theme-border bg-theme-surface overflow-hidden shadow-2xl relative">
    <div class="absolute -left-32 bottom-0 h-64 w-64 rounded-full bg-purple-500/5 blur-[80px]"></div>
    
    <div class="px-8 py-6 border-b border-theme-border/50 flex items-center gap-3 bg-theme-border/10">
      <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20">
        <BriefcaseIcon class="h-5 w-5 text-amber-500" />
      </div>
      <div>
        <h2 class="text-lg font-bold text-theme-text">Professional Profile</h2>
        <p class="text-xs text-theme-text-muted mt-0.5">Set up their public-facing details</p>
      </div>
    </div>
    
    <div class="p-8 space-y-8 relative z-10">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-[11px] uppercase tracking-[0.2em] text-theme-text-muted ml-1 font-bold">Years of Experience</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <AcademicCapIcon class="h-5 w-5 text-theme-text-muted/60 group-focus-within:text-admin/70 transition-colors" />
            </div>
            <input v-model.number="form.experience_years" required type="number" min="0" class="w-full rounded-2xl border border-theme-border bg-theme-surface pl-12 pr-4 py-4 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-4 focus:ring-admin/10" placeholder="e.g. 5" />
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-[11px] uppercase tracking-[0.2em] text-theme-text-muted ml-1 font-bold">Account Status</label>
          <div class="relative">
            <select v-model="form.status" class="w-full rounded-2xl border border-theme-border bg-theme-surface px-4 py-4 text-sm font-bold text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-4 focus:ring-admin/10 appearance-none cursor-pointer">
              <option value="active" class="bg-theme-surface text-theme-text">🟢 Active</option>
              <option value="pending_approval" class="bg-theme-surface text-theme-text">🟡 Pending Approval</option>
              <option value="suspended" class="bg-theme-surface text-theme-text">🔴 Suspended</option>
              <option value="on_leave" class="bg-theme-surface text-theme-text">⚪ On Leave</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="space-y-2">
        <label class="text-[11px] uppercase tracking-[0.2em] text-theme-text-muted ml-1 font-bold flex justify-between">
          <span>Specialties</span>
          <span class="text-theme-text-muted/60 font-normal normal-case tracking-normal">Separate with commas</span>
        </label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 pl-4 flex top-4 pointer-events-none">
            <SparklesIcon class="h-5 w-5 text-theme-text-muted/60 group-focus-within:text-admin/70 transition-colors" />
          </div>
          <input v-model="form.specialties" type="text" class="w-full rounded-2xl border border-theme-border bg-theme-surface pl-12 pr-4 py-4 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-4 focus:ring-admin/10" placeholder="Fades, Lineups, Scissor Cuts, Beard Trims" />
        </div>
      </div>
      
      <div class="space-y-2">
        <label class="text-[11px] uppercase tracking-[0.2em] text-theme-text-muted ml-1 font-bold">Biography</label>
        <div class="relative group">
          <textarea v-model="form.bio" rows="4" class="w-full rounded-2xl border border-theme-border bg-theme-surface p-5 text-sm text-theme-text outline-none transition-all focus:border-admin/50 focus:ring-4 focus:ring-admin/10 resize-none" placeholder="Write a short, engaging bio describing the barber's background and style..."></textarea>
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
