<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-[#050505]/90 backdrop-blur-md" @click="$emit('close')"></div>
      <div class="relative w-full max-w-xl rounded-[2.5rem] border border-white/10 bg-[#151515] shadow-[0_30px_100px_rgba(0,0,0,0.8)] overflow-hidden animate-slide-up">
        
        <div class="absolute -right-32 -top-32 h-64 w-64 rounded-full bg-admin/10 blur-[80px]"></div>
        <div class="absolute -left-32 -bottom-32 h-64 w-64 rounded-full bg-purple-500/10 blur-[80px]"></div>
        
        <!-- Header -->
        <div class="relative px-10 pt-10 pb-6 flex justify-between items-start z-10">
          <div>
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-admin/20 to-purple-500/20 border border-white/10 mb-4">
              <BuildingLibraryIcon class="h-6 w-6 text-admin" />
            </div>
            <h3 class="font-display text-3xl text-white">Bank Details</h3>
            <p class="text-xs text-white/40 mt-1">Configure where customer payments are routed.</p>
          </div>
          <button @click="$emit('close')" class="text-white/30 hover:text-white hover:bg-white/10 rounded-full p-2.5 transition-all bg-white/5 border border-white/5">
            <XMarkIcon class="h-5 w-5"/>
          </button>
        </div>
        
        <div class="px-10 pb-10 relative z-10">
          <!-- Glass Credit Card Preview -->
          <div class="mb-8 relative group mx-auto max-w-md perspective-1000">
            <div class="relative rounded-3xl overflow-hidden border border-white/10 bg-gradient-to-br from-[#2A1100]/80 via-[#111] to-[#0a0a0a] p-8 shadow-2xl backdrop-blur-xl transition-transform duration-500 hover:rotate-y-12 hover:scale-[1.02] transform-style-3d">
              <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20 mix-blend-overlay"></div>
              <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
              
              <div class="relative z-10 flex justify-between items-start mb-8">
                <div class="h-8 w-12 rounded bg-gradient-to-r from-amber-200 to-amber-500 shadow-sm opacity-80"></div>
                <svg class="w-10 h-10 text-white/20" viewBox="0 0 24 24" fill="currentColor"><path d="M2 10V8a2 2 0 012-2h16a2 2 0 012 2v2H2zm0 2v4a2 2 0 002 2h16a2 2 0 002-2v-4H2zm4 2h4v2H6v-2z"/></svg>
              </div>
              
              <div class="relative z-10 space-y-5">
                <p class="text-[22px] font-mono text-white tracking-[0.2em] drop-shadow-md">
                  {{ form.account_number || '0000 0000 0000' }}
                </p>
                <div class="flex items-end justify-between">
                  <div>
                    <p class="text-[8px] uppercase tracking-[0.2em] text-white/40 mb-1">Account Name</p>
                    <p class="text-sm font-bold text-white tracking-widest uppercase">{{ form.account_name || 'CANDYCUTZ SALOON' }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-[8px] uppercase tracking-[0.2em] text-white/40 mb-1">Bank</p>
                    <p class="text-xs font-bold text-admin tracking-wider uppercase">{{ form.bank_name || 'BANK NAME' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Fields -->
          <div class="space-y-4">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <BuildingLibraryIcon class="h-5 w-5 text-white/30" />
              </div>
              <input v-model="form.bank_name" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20 font-medium" placeholder="Bank Name (e.g. Zenith Bank)" />
            </div>
            
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <UserIcon class="h-5 w-5 text-white/30" />
              </div>
              <input v-model="form.account_name" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20 font-medium" placeholder="Account Name" />
            </div>
            
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <HashtagIcon class="h-5 w-5 text-white/30" />
              </div>
              <input v-model="form.account_number" type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.03] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20 font-mono tracking-widest text-lg" placeholder="Account Number" />
            </div>
          </div>
          
          <div class="flex items-center gap-3 pt-8 mt-4">
            <button @click="$emit('close')" class="flex-1 py-4 px-6 rounded-2xl border border-white/10 text-sm font-bold text-white/60 hover:text-white hover:bg-white/5 transition-all">
              Cancel
            </button>
            <button @click="$emit('save', form)" :disabled="loading" class="flex-[2] flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 px-6 py-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.4)] disabled:opacity-50 active:scale-[0.98]">
              <span v-if="loading" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
              <CheckCircleIcon v-else class="h-5 w-5" />
              {{ loading ? 'Saving...' : 'Update Bank Details' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { BuildingLibraryIcon, XMarkIcon, UserIcon, HashtagIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import { watch, ref } from 'vue';

const props = defineProps({
  show: Boolean,
  loading: Boolean,
  initialData: Object
});

const emit = defineEmits(['close', 'save']);

const form = ref({
  bank_name: '',
  account_name: '',
  account_number: ''
});

watch(() => props.initialData, (newVal) => {
  if (newVal) {
    form.value = { ...newVal };
  }
}, { immediate: true, deep: true });
</script>

<style scoped>
.perspective-1000 { perspective: 1000px; }
.transform-style-3d { transform-style: preserve-3d; }
.animate-slide-up { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
