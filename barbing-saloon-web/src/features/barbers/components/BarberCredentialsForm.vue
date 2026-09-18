<template>
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
</template>

<script setup>
import { ShieldCheckIcon, EnvelopeIcon, KeyIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';
import { UserIcon } from '@heroicons/vue/24/solid';

defineProps({
  form: Object,
  isEditing: Boolean
});
</script>
