<template>
  <div 
    class="group p-6 md:p-8 flex items-start gap-5 transition-all duration-300 relative overflow-hidden bg-theme-surface hover:bg-theme-bg"
  >
    <!-- Unread indicator bar -->
    <div v-if="!notif.is_read" class="absolute left-0 top-0 bottom-0 w-1.5 bg-gold shadow-[0_0_15px_rgba(255,103,0,0.6)]"></div>
    
    <div class="flex-shrink-0 mt-1">
      <div 
        class="h-14 w-14 rounded-2xl flex items-center justify-center border shadow-sm transition-transform duration-300 group-hover:scale-105" 
        :class="!notif.is_read ? getBgColorForType(notif.type) : 'bg-theme-bg/50 border-theme-border/50 grayscale opacity-70'"
      >
        <component :is="getIconForType(notif.type)" class="w-7 h-7" :class="!notif.is_read ? getIconColorForType(notif.type) : 'text-theme-muted'" />
      </div>
    </div>
    
    <div class="flex-1 min-w-0">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
        <div class="flex items-center gap-2.5 flex-wrap">
          <h4 class="text-xl font-display transition-colors duration-200" :class="!notif.is_read ? 'text-theme-text group-hover:text-gold' : 'text-theme-muted'">
            {{ notif.title }}
          </h4>
          <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-md border" :class="getBgColorForType(notif.type) + ' ' + getIconColorForType(notif.type)">
            {{ getLabelForType(notif.type) }}
          </span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <span class="text-xs font-bold text-theme-muted flex items-center gap-1.5 bg-theme-bg px-3 py-1.5 rounded-xl border border-theme-border" :title="formatFullDate(notif.created_at)">
            <ClockIcon class="w-3.5 h-3.5 text-theme-muted/50" />
            {{ formatRelativeTime(notif.created_at) }}
          </span>
        </div>
      </div>
      
      <p class="text-sm text-theme-muted mb-1.5" :title="formatFullDate(notif.created_at)">{{ formatFullDate(notif.created_at) }}</p>
      
      <p class="text-base leading-relaxed" :class="!notif.is_read ? 'text-theme-text' : 'text-theme-muted'">{{ notif.message }}</p>
      
      <div class="mt-5 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-2 group-hover:translate-y-0">
        <button 
          v-if="!notif.is_read" 
          @click="$emit('read', notif.id)" 
          class="inline-flex items-center gap-2 text-xs font-bold text-gold hover:text-obsidian transition-colors bg-gold/10 hover:bg-gold border border-gold/20 hover:border-gold px-4 py-2 rounded-xl"
        >
          <CheckIcon class="w-4 h-4" />
          Mark as read
        </button>
        
        <button 
          @click="$emit('delete', notif.id)" 
          class="inline-flex items-center gap-2 text-xs font-bold text-red-400 hover:text-white transition-colors bg-red-500/10 hover:bg-red-500 border border-red-500/20 hover:border-red-500 px-4 py-2 rounded-xl"
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
