<template>
  <div class="overflow-hidden rounded-[2rem] border border-white/[0.08] bg-[#12161C] shadow-2xl transition-all">
    <!-- Group Header -->
    <div class="flex items-center justify-between border-b border-white/[0.06] bg-white/[0.02] px-6 py-4">
      <div>
        <h2 class="font-display text-lg font-bold text-white tracking-tight">{{ group.name }}</h2>
        <p class="mt-0.5 text-xs text-white/50">{{ group.description }}</p>
      </div>
      <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-xs font-mono font-bold text-white/70">
        {{ group.endpoints.length }} {{ group.endpoints.length === 1 ? 'route' : 'routes' }}
      </span>
    </div>

    <!-- Endpoints List -->
    <div class="divide-y divide-white/[0.04]">
      <ApiEndpointCard
        v-for="endpoint in group.endpoints"
        :key="`${endpoint.method}-${endpoint.path}`"
        :endpoint="endpoint"
        :copied="copiedPath === endpoint.path"
        @copy="$emit('copy', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import ApiEndpointCard from './ApiEndpointCard.vue';

defineProps({
  group: {
    type: Object,
    required: true
  },
  copiedPath: {
    type: String,
    default: null
  }
});

defineEmits(['copy']);
</script>
