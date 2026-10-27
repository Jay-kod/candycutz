<template>
  <article 
    class="group relative overflow-hidden rounded-3xl border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-0.5"
    :class="item.is_approved 
      ? 'border-theme-border hover:border-admin/40' 
      : 'border-amber-500/40 hover:border-amber-400 bg-theme-surface'"
  >
    <!-- Glowing Top Accent Line -->
    <div 
      class="absolute top-0 left-0 right-0 h-1 z-30 transition-opacity"
      :class="!item.is_approved 
        ? 'bg-amber-400 opacity-100' 
        : (item.is_featured ? 'bg-gradient-to-r from-admin via-amber-400 to-amber-200 opacity-100' : 'bg-gradient-to-r from-admin to-amber-400 opacity-0 group-hover:opacity-100')"
    ></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
      <div class="flex-1">
        <div class="flex items-center gap-4 mb-3">
          <img
            v-if="item.avatar_url"
            :src="item.avatar_url"
            :alt="`${item.client_name || item.customer_name || 'Customer'} profile`"
            class="h-12 w-12 shrink-0 rounded-full border border-admin/30 object-cover shadow-sm"
            loading="lazy"
          />
          <div v-else class="h-12 w-12 rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-admin font-display text-xl shrink-0">
            {{ (item.client_name || item.customer_name || 'C').charAt(0).toUpperCase() }}
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="font-display text-xl text-theme-text group-hover:text-admin transition-colors">{{ item.client_name || item.customer_name }}</h2>
              <span
                v-if="item.client_name?.startsWith('Sample Customer ') || item.customer_name?.startsWith('Sample Customer ')"
                class="rounded-full border border-sky-400/30 bg-sky-400/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-sky-300"
              >
                Demo sample
              </span>
              <span v-if="item.service?.name || item.service_name" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-admin/15 border border-admin/30 text-admin text-xs font-semibold">
                ✂️ {{ item.service?.name || item.service_name }}
              </span>
            </div>
            <div class="flex items-center gap-2 mt-1">
              <div class="flex text-amber-400">
                <span v-for="i in 5" :key="i">
                  <svg v-if="i <= item.rating" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 opacity-20"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385c.148.621-.531 1.114-1.005.777l-4.783-2.738a.562.562 0 00-.554 0l-4.783 2.738c-.474.337-1.153-.156-1.005-.777l1.285-5.385a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                </span>
              </div>
              <span class="text-xs text-theme-text-muted flex items-center gap-1 font-mono">
                <ClockIcon class="h-3.5 w-3.5 text-theme-text-muted" />
                {{ new Date(item.created_at).toLocaleDateString() }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="relative mt-2">
          <ChatBubbleBottomCenterTextIcon class="absolute -left-2 -top-2 h-8 w-8 text-theme-text-muted/10 rotate-180" />
          <p class="text-theme-text/80 leading-relaxed italic relative z-10 pl-6 border-l-2 border-admin/30 py-1 text-sm">"{{ item.review || item.comment }}"</p>
        </div>
      </div>

      <!-- Actions Panel -->
      <div class="flex flex-col gap-2 min-w-[160px] shrink-0 border-t md:border-t-0 md:border-l border-theme-border pt-4 md:pt-0 md:pl-6">
        <div class="flex items-center gap-2 mb-2">
          <div class="h-2 w-2 rounded-full" :class="item.is_approved ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'"></div>
          <span class="text-[11px] font-bold uppercase tracking-wider" :class="item.is_approved ? 'text-emerald-400' : 'text-amber-400'">
            {{ item.is_approved ? (item.is_featured ? 'Approved · Ribbon Live' : 'Approved · Hidden') : 'Pending Moderation' }}
          </span>
        </div>

        <!-- Approve / Unapprove Button -->
        <button
          :disabled="isModerating"
          @click="$emit('toggle-approval', item)"
          class="flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all disabled:cursor-wait disabled:opacity-60 shadow-sm"
          :class="item.is_approved 
            ? 'bg-theme-surface-subtle border border-theme-border text-theme-text-muted hover:text-theme-text hover:bg-theme-surface' 
            : 'bg-emerald-500 hover:bg-emerald-400 text-obsidian shadow-[0_0_15px_rgba(16,185,129,0.3)]'"
        >
          <CheckCircleIcon class="h-4 w-4" />
          {{ isModerating ? 'Saving...' : item.is_approved ? 'Unapprove' : 'Approve Review' }}
        </button>

        <!-- Feature on Website Button -->
        <button
          :disabled="isModerating || !item.is_approved"
          @click="$emit('toggle-visibility', item)"
          class="flex items-center justify-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold transition-all disabled:cursor-not-allowed disabled:opacity-40"
          :class="item.is_featured
            ? 'border-amber-400/30 bg-amber-400/10 text-amber-300 hover:bg-amber-400 hover:text-obsidian'
            : 'border-theme-border bg-theme-surface-subtle text-theme-text-muted hover:bg-theme-surface hover:text-theme-text'"
          :title="item.is_approved ? '' : 'Approve review first before featuring on homepage ribbon'"
        >
          <EyeSlashIcon v-if="item.is_featured" class="h-4 w-4" />
          <EyeIcon v-else class="h-4 w-4" />
          {{ isModerating ? 'Saving...' : !item.is_approved ? 'Approve First' : item.is_featured ? 'Hide from Ribbon' : 'Feature on Ribbon' }}
        </button>

        <!-- Delete Button -->
        <button 
          @click="$emit('delete', item.id)" 
          class="flex items-center justify-center gap-2 rounded-xl border border-red-500/20 bg-red-500/5 text-red-400 px-4 py-2 text-xs font-bold hover:bg-red-500 hover:text-white hover:border-red-500 transition-all"
        >
          <TrashIcon class="h-4 w-4" />
          Delete
        </button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { 
  ChatBubbleBottomCenterTextIcon, 
  CheckCircleIcon, 
  TrashIcon, 
  ClockIcon, 
  EyeIcon, 
  EyeSlashIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  item: {
    type: Object,
    required: true
  },
  isModerating: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-approval', 'toggle-visibility', 'delete']);
</script>
