<template>
  <div 
    v-if="isDirty || saving" 
    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-[92%] max-w-xl animate-bounce-subtle"
  >
    <div class="rounded-2xl border border-white/10 bg-[#12161C]/95 backdrop-blur-xl px-5 py-3.5 shadow-[0_10px_35px_rgba(0,0,0,0.6)] flex items-center justify-between gap-4">
      <div class="flex items-center gap-2.5 min-w-0">
        <span class="flex h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
        <div class="truncate">
          <p class="text-xs font-bold text-white truncate">Unsaved Policy Changes</p>
          <p class="text-[10px] text-white/50 truncate">Privacy clauses or headline modifications are pending publication.</p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button 
          @click="$emit('save')" 
          :disabled="saving" 
          class="flex items-center justify-center min-h-[38px] gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-4 py-2 text-xs font-bold text-obsidian shadow-[0_4px_15px_rgba(255,103,0,0.3)] hover:shadow-[0_6px_20px_rgba(255,103,0,0.45)] active:scale-[0.98] transition-all disabled:opacity-50"
        >
          <span v-if="saving" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="h-4 w-4" />
          <span>{{ saving ? 'Saving...' : 'Save Now' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CheckIcon } from '@heroicons/vue/24/outline';

defineProps({
  isDirty: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  }
});

defineEmits(['save']);
</script>

<style scoped>
@keyframes bounceSubtle {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}
.animate-bounce-subtle {
  animation: bounceSubtle 3s ease-in-out infinite;
}
</style>

