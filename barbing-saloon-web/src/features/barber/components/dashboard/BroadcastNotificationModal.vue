<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="$emit('close')"></div>
      <div class="relative w-full max-w-md rounded-3xl border border-white/10 bg-theme-surface p-8 shadow-[0_20px_60px_rgba(0,0,0,0.5)] overflow-hidden">
        <div class="absolute top-0 right-0 p-4">
          <button @click="$emit('close')" class="text-ivory/40 hover:text-white transition-colors bg-white/5 hover:bg-white/10 p-2 rounded-full">
            <XMarkIcon class="h-5 w-5"/>
          </button>
        </div>
        
        <div class="flex items-center gap-4 mb-6">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500/20 to-purple-600/10 border border-purple-500/20 shadow-inner">
            <MegaphoneIcon class="h-6 w-6 text-purple-400" />
          </div>
          <div>
            <h3 class="font-display text-xl text-theme-text">Broadcast Alert</h3>
            <p class="text-[11px] uppercase tracking-widest text-ivory/40 mt-1 font-bold">Notify all your customers</p>
          </div>
        </div>

        <div class="space-y-5">
          <div>
            <label class="text-[11px] font-bold uppercase tracking-widest text-ivory/50 ml-1 mb-2 block">Alert Title</label>
            <input v-model="form.title" type="text" class="w-full rounded-xl border border-white/10 bg-theme-bg px-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/50" placeholder="e.g. I am back at the shop!" />
          </div>
          <div>
            <label class="text-[11px] font-bold uppercase tracking-widest text-ivory/50 ml-1 mb-2 block">Message content</label>
            <textarea v-model="form.message" rows="3" class="w-full rounded-xl border border-white/10 bg-theme-bg px-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/50 resize-none" placeholder="Let your customers know you are available, running late, or have a new promo..."></textarea>
          </div>
          <button @click="$emit('send')" :disabled="sending" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-500 py-3.5 text-sm font-bold text-white transition-all hover:shadow-[0_0_20px_rgba(168,85,247,0.4)] disabled:opacity-50 mt-4 active:scale-[0.98]">
            <MegaphoneIcon v-if="!sending" class="h-4.5 w-4.5" />
            {{ sending ? 'Broadcasting...' : 'Send Broadcast' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { MegaphoneIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  show: { type: Boolean, required: true },
  sending: { type: Boolean, required: true },
  form: { type: Object, required: true }
});

defineEmits(['close', 'send']);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
