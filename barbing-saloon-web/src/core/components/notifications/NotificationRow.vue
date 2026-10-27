<template>
  <div 
    class="group p-6 md:p-8 flex items-start gap-5 transition-all duration-300 relative overflow-hidden bg-[var(--color-bg-secondary)] hover:bg-[var(--color-bg-tertiary)]"
  >
    <!-- Unread indicator bar -->
    <div v-if="!notif.is_read" class="absolute left-0 top-0 bottom-0 w-1.5 bg-[var(--color-accent)] shadow-[0_0_15px_rgba(255,103,0,0.4)]"></div>
    
    <div class="flex-shrink-0 mt-1">
      <div 
        class="h-14 w-14 rounded-2xl flex items-center justify-center border shadow-sm transition-transform duration-300 group-hover:scale-105" 
        :class="!notif.is_read ? getBgColorForType(notif.type) : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] grayscale opacity-70'"
      >
        <component :is="getIconForType(notif.type)" class="w-7 h-7" :class="!notif.is_read ? getIconColorForType(notif.type) : 'text-[var(--color-text-muted)]'" />
      </div>
    </div>
    
    <div class="flex-1 min-w-0">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
        <div class="flex items-center gap-2.5 flex-wrap">
          <h4 class="text-xl font-display transition-colors duration-200" :class="!notif.is_read ? 'text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)]' : 'text-[var(--color-text-muted)]'">
            {{ notif.title }}
          </h4>
          <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-md border" :class="getBgColorForType(notif.type) + ' ' + getIconColorForType(notif.type)">
            {{ getLabelForType(notif.type) }}
          </span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <span class="text-xs font-bold text-[var(--color-text-secondary)] flex items-center gap-1.5 bg-[var(--color-bg-tertiary)] px-3 py-1.5 rounded-xl border border-[var(--color-border)]" :title="formatFullDate(notif.created_at)">
            <ClockIcon class="w-3.5 h-3.5 text-[var(--color-text-muted)]/50" />
            {{ formatRelativeTime(notif.created_at) }}
          </span>
        </div>
      </div>
      
      <p class="text-sm text-[var(--color-text-secondary)] mb-1.5" :title="formatFullDate(notif.created_at)">{{ formatFullDate(notif.created_at) }}</p>
      
      <p class="text-base leading-relaxed" :class="!notif.is_read ? 'text-[var(--color-text-primary)]' : 'text-[var(--color-text-secondary)]'">{{ notif.message }}</p>
      
      <div class="mt-5 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-2 group-hover:translate-y-0">
        <button 
          v-if="!notif.is_read" 
          @click="$emit('read', notif.id)" 
          class="inline-flex items-center gap-2 text-xs font-bold text-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors bg-[var(--color-accent)]/10 hover:bg-[var(--color-accent)] border border-[var(--color-accent)]/20 hover:border-[var(--color-accent)] px-4 py-2 rounded-xl"
        >
          <CheckIcon class="w-4 h-4" />
          Mark as read
        </button>
        
        <button 
          @click="$emit('delete', notif.id)" 
          class="inline-flex items-center gap-2 text-xs font-bold text-[var(--color-danger)] hover:text-white transition-colors bg-[var(--color-danger)]/10 hover:bg-[var(--color-danger)] border border-[var(--color-danger)]/20 hover:border-[var(--color-danger)] px-4 py-2 rounded-xl"
        >
          <TrashIcon class="w-4 h-4" />
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ClockIcon, CheckIcon, TrashIcon } from '@heroicons/vue/24/outline'
import {
  formatRelativeTime,
  formatFullDate,
  getIconForType,
  getLabelForType,
  getIconColorForType,
  getBgColorForType
} from './notificationUtils'

defineProps({
  notif: { type: Object, required: true },
})

defineEmits(['read', 'delete'])
</script>
