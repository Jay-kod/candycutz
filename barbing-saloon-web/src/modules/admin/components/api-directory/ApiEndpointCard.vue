<template>
  <div class="group relative flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 hover:bg-white/[0.02] transition-colors">
    <!-- Left: Method + Path -->
    <div class="flex items-center gap-3.5 min-w-0 flex-1">
      <!-- Method Badge -->
      <span 
        class="w-16 shrink-0 rounded-lg border py-1 text-center text-[10px] font-black tracking-wider uppercase"
        :class="methodBadgeClass"
      >
        {{ endpoint.method }}
      </span>

      <!-- Path with Click to Copy -->
      <div class="flex items-center gap-2 min-w-0">
        <code class="font-mono text-xs text-white/90 truncate group-hover:text-admin-light transition-colors">
          {{ endpoint.path }}
        </code>
        <button 
          @click="$emit('copy', endpoint.path)" 
          type="button" 
          class="p-1 rounded-md text-white/30 hover:text-white hover:bg-white/10 transition-colors shrink-0"
          :title="copied ? 'Copied!' : 'Copy path to clipboard'"
        >
          <CheckIcon v-if="copied" class="h-3.5 w-3.5 text-emerald-400" />
          <DocumentDuplicateIcon v-else class="h-3.5 w-3.5" />
        </button>
      </div>
    </div>

    <!-- Right: Description & Auth Scope -->
    <div class="flex flex-wrap items-center gap-3 justify-between md:justify-end shrink-0">
      <p class="text-xs text-white/50 max-w-sm truncate md:text-right">
        {{ endpoint.description }}
      </p>

      <!-- Auth Scope Pill -->
      <span 
        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
        :class="endpoint.auth === 'protected' 
          ? 'bg-violet-950/70 text-violet-300 border-violet-500/30' 
          : 'bg-sky-950/70 text-sky-300 border-sky-500/30'"
      >
        <LockClosedIcon v-if="endpoint.auth === 'protected'" class="h-2.5 w-2.5" />
        <GlobeAltIcon v-else class="h-2.5 w-2.5" />
        <span>{{ endpoint.auth === 'protected' ? 'Sanctum Bearer' : 'Public Access' }}</span>
      </span>

      <!-- Standard Envelope Tag -->
      <span class="text-[9px] font-mono font-medium text-white/30 hidden xl:inline">
        ApiResponse
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  DocumentDuplicateIcon, 
  CheckIcon, 
  LockClosedIcon, 
  GlobeAltIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  endpoint: {
    type: Object,
    required: true
  },
  copied: {
    type: Boolean,
    default: false
  }
});

defineEmits(['copy']);

const methodBadgeClass = computed(() => {
  const map = {
    GET: 'border-sky-500/30 bg-sky-500/10 text-sky-300',
    POST: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
    PUT: 'border-amber-500/30 bg-amber-500/10 text-amber-300',
    PATCH: 'border-orange-500/30 bg-orange-500/10 text-orange-300',
    DELETE: 'border-rose-500/30 bg-rose-500/10 text-rose-300',
  };
  return map[props.endpoint.method] || 'border-white/10 bg-white/5 text-white/60';
});
</script>

