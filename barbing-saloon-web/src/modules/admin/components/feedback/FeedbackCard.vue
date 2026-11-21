<template>
  <article 
    class="group relative overflow-hidden rounded-3xl border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-0.5"
    :class="[
      item.status === 'new'
        ? 'border-amber-500/40 hover:border-amber-400'
        : 'border-theme-border hover:border-admin/40'
    ]"
  >
    <!-- Glowing Top Accent Line -->
    <div 
      class="absolute top-0 left-0 right-0 h-1 z-30 transition-opacity"
      :class="{
        'bg-amber-400 opacity-100': item.status === 'new',
        'bg-sky-400 opacity-90': item.status === 'reviewing',
        'bg-indigo-400 opacity-90': item.status === 'planned',
        'bg-emerald-400 opacity-90': item.status === 'completed',
        'bg-theme-border opacity-60': item.status === 'declined'
      }"
    ></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-start justify-between gap-6">
      <!-- Left: Content & Details -->
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2 mb-3">
          <!-- Topic Tag -->
          <span 
            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider border shadow-sm"
            :class="categoryBadgeStyle"
          >
            <component :is="categoryIcon" class="h-3.5 w-3.5" />
            {{ categoryLabel(item.category) }}
          </span>

          <!-- Status Pill -->
          <span 
            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border"
            :class="statusPillStyle"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="statusDotStyle"></span>
            {{ statusLabel(item.status) }}
          </span>

          <!-- Timestamp -->
          <span class="text-xs text-theme-text-muted font-mono ml-auto sm:ml-0 flex items-center gap-1">
            <ClockIcon class="h-3.5 w-3.5 text-theme-text-muted/60" />
            {{ formatDate(item.created_at) }}
          </span>
        </div>

        <!-- Title -->
        <h2 class="font-display text-xl text-theme-text group-hover:text-admin transition-colors leading-tight drop-shadow mb-2 break-words">
          {{ item.title }}
        </h2>

        <!-- Description -->
        <p class="whitespace-pre-wrap break-words text-xs sm:text-sm text-theme-text-muted leading-relaxed bg-theme-surface-subtle p-4 rounded-2xl border border-theme-border/50 mb-4">
          {{ item.description }}
        </p>

        <!-- Customer Submitter Metadata -->
        <div class="flex items-center gap-3 pt-2 border-t border-theme-border/50">
          <div class="h-8 w-8 rounded-full bg-admin/15 border border-admin/30 flex items-center justify-center text-admin font-display text-xs shrink-0">
            {{ (item.user?.name || 'C').charAt(0).toUpperCase() }}
          </div>
          <div class="text-xs text-theme-text-muted">
            <span class="text-theme-text-muted/60">Submitted by:</span>
            <span class="font-bold text-theme-text ml-1">{{ item.user?.name || 'Deleted customer' }}</span>
            <span v-if="item.user?.email" class="text-theme-text-muted/70 ml-1.5 font-mono">({{ item.user.email }})</span>
          </div>
        </div>
      </div>

      <!-- Right: Admin Moderation Form Panel -->
      <div class="w-full space-y-3.5 rounded-2xl border border-theme-border bg-theme-surface-subtle p-4 sm:p-5 lg:max-w-sm shrink-0">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-admin">Executive Review</span>
          <span v-if="isDirty" class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20">
            Unsaved Changes
          </span>
        </div>

        <!-- Status Dropdown -->
        <div>
          <label :for="`feedback-status-${item.id}`" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-theme-text-muted">
            Submission Status
          </label>
          <div class="relative">
            <select 
              :id="`feedback-status-${item.id}`" 
              v-model="draft.status" 
              class="appearance-none min-h-10 w-full rounded-xl border border-theme-border bg-theme-bg px-3 pr-8 text-xs text-theme-text outline-none focus:border-admin transition-all font-medium"
            >
              <option v-for="status in statuses" :key="status.value" :value="status.value" class="bg-theme-surface text-theme-text">
                {{ status.label }}
              </option>
            </select>
            <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-theme-text-muted" />
          </div>
        </div>

        <!-- Admin Note Textarea -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label :for="`feedback-note-${item.id}`" class="block text-[10px] font-bold uppercase tracking-wider text-theme-text-muted">
              Internal Note (Admins Only)
            </label>
            <span class="text-[10px] text-theme-text-muted/60 font-mono">{{ draft.admin_note?.length || 0 }}/5000</span>
          </div>
          <textarea 
            :id="`feedback-note-${item.id}`" 
            v-model="draft.admin_note" 
            rows="3" 
            maxlength="5000" 
            placeholder="Document leadership decision, bug ticket #, or implementation roadmap..." 
            class="w-full resize-y rounded-xl border border-theme-border bg-theme-bg px-3 py-2 text-xs text-theme-text placeholder:text-theme-text-muted/40 outline-none focus:border-admin transition-all"
          ></textarea>
        </div>

        <!-- Save Review Button -->
        <button
          type="button"
          class="min-h-10 w-full rounded-xl bg-gradient-to-r from-admin to-amber-500 px-4 py-2 text-xs font-bold text-obsidian transition-all hover:shadow-[0_4px_15px_rgba(255,103,0,0.3)] disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center gap-1.5 shadow"
          :disabled="savingId === item.id"
          @click="$emit('save', item)"
        >
          <span v-if="savingId === item.id" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
          <CheckIcon v-else class="h-4 w-4" />
          <span>{{ savingId === item.id ? 'Saving Review...' : 'Save Review' }}</span>
        </button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { 
  ClockIcon, 
  ChevronDownIcon, 
  CheckIcon, 
  SparklesIcon, 
  WrenchScrewdriverIcon, 
  LightBulbIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  draft: {
    type: Object,
    required: true
  },
  savingId: {
    type: [Number, String],
    default: null
  },
  statuses: {
    type: Array,
    required: true
  }
});

defineEmits(['save']);

const isDirty = computed(() => {
  return props.draft.status !== props.item.status || 
    (props.draft.admin_note || '') !== (props.item.admin_note || '');
});

const categoryIcon = computed(() => {
  if (props.item.category === 'feature_request') return LightBulbIcon;
  if (props.item.category === 'maintenance') return WrenchScrewdriverIcon;
  return SparklesIcon;
});

const categoryLabel = (cat) => ({
  feature_request: 'Feature Request',
  maintenance: 'Maintenance Report',
  improvement: 'Salon Improvement'
}[cat] || cat);

const categoryBadgeStyle = computed(() => {
  if (props.item.category === 'feature_request') return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
  if (props.item.category === 'maintenance') return 'bg-rose-500/10 text-rose-400 border-rose-500/30';
  return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
});

const statusLabel = (status) => ({
  new: 'New Submission',
  reviewing: 'Under Review',
  planned: 'Planned Roadmap',
  completed: 'Resolved / Completed',
  declined: 'Declined'
}[status] || status);

const statusPillStyle = computed(() => {
  if (props.item.status === 'new') return 'bg-amber-500/15 text-amber-400 border-amber-500/30';
  if (props.item.status === 'reviewing') return 'bg-sky-500/15 text-sky-400 border-sky-500/30';
  if (props.item.status === 'planned') return 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30';
  if (props.item.status === 'completed') return 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
  return 'bg-theme-surface-subtle text-theme-text-muted border-theme-border';
});

const statusDotStyle = computed(() => {
  if (props.item.status === 'new') return 'bg-amber-400 animate-pulse';
  if (props.item.status === 'reviewing') return 'bg-sky-400';
  if (props.item.status === 'planned') return 'bg-indigo-400';
  if (props.item.status === 'completed') return 'bg-emerald-400';
  return 'bg-theme-text-muted/40';
});

const formatDate = (val) => {
  if (!val) return 'Date unavailable';
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(val));
};
</script>
