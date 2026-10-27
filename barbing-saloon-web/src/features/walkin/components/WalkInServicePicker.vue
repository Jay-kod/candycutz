<template>
  <div class="rounded-2xl border border-theme-border bg-theme-surface p-6 sm:p-7 relative overflow-hidden group hover:border-admin/40 transition-all">
    <!-- Technical Corner Ticks -->
    <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

    <!-- Section Title & Category Filter Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-theme-border">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin">
          <ScissorsIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-base font-bold text-theme-text tracking-wide">Select Service</h2>
          <p class="text-xs text-theme-text-muted">Pick requested treatment package</p>
        </div>
      </div>

      <!-- Category Filter Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
        <button
          v-for="cat in categories"
          :key="cat"
          type="button"
          @click="activeCategory = cat"
          class="px-2.5 py-1 rounded-lg text-[10px] font-semibold uppercase tracking-wider transition-all shrink-0"
          :class="activeCategory === cat 
            ? 'bg-admin text-white shadow-sm' 
            : 'bg-theme-bg text-theme-text-muted hover:bg-theme-surface-hover hover:text-theme-text'"
        >
          {{ cat }}
        </button>
      </div>
    </div>

    <!-- Service Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[360px] overflow-y-auto pr-1">
      <div
        v-for="svc in filteredServices"
        :key="svc.id"
        @click="selectService(svc.id)"
        class="group/card cursor-pointer p-4 rounded-xl border transition-all relative flex flex-col justify-between"
        :class="modelValue === svc.id 
          ? 'border-admin bg-admin/10 ring-1 ring-admin/40 shadow-lg shadow-admin/10' 
          : 'border-theme-border bg-theme-bg hover:border-theme-border hover:bg-theme-surface-hover'"
      >
        <!-- Selected Check Badge -->
        <span 
          v-if="modelValue === svc.id"
          class="absolute top-2.5 right-2.5 h-5 w-5 rounded-full bg-admin text-white flex items-center justify-center shadow-md animate-scale-in"
        >
          <CheckIcon class="h-3 w-3 stroke-[3]" />
        </span>

        <div>
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-[9px] font-mono uppercase tracking-wider px-2 py-0.5 rounded bg-theme-surface text-theme-text-muted border border-theme-border">
              {{ svc.category?.name || svc.category_name || 'Grooming' }}
            </span>
            <span class="text-[10px] font-mono text-theme-text-muted flex items-center gap-1">
              <ClockIcon class="h-3.5 w-3.5" />
              {{ svc.duration_minutes || 30 }}m
            </span>
          </div>
          <h3 class="text-sm font-bold text-theme-text group-hover/card:text-admin-light transition-colors line-clamp-1">
            {{ svc.name }}
          </h3>
          <p class="text-[11px] text-theme-text-muted line-clamp-1 mt-0.5">
            {{ svc.description || 'Premium precision styling' }}
          </p>
        </div>

        <div class="pt-3 mt-3 border-t border-theme-border flex items-baseline justify-between">
          <span class="text-[10px] font-mono text-theme-text-muted uppercase tracking-widest">Rate</span>
          <span class="text-base font-bold text-theme-text font-mono">
            ₦{{ Number(svc.price || 0).toLocaleString() }}
          </span>
        </div>
      </div>

      <div v-if="filteredServices.length === 0" class="col-span-2 text-center py-8 text-theme-text-muted text-xs font-mono">
        No services found under this category.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { ScissorsIcon, ClockIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: { type: [Number, String], default: '' },
  services: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:modelValue']);

const activeCategory = ref('All');

const categories = computed(() => {
  const set = new Set();
  set.add('All');
  props.services.forEach(s => {
    const name = s.category?.name || s.category_name;
    if (name) set.add(name);
  });
  return Array.from(set);
});

const filteredServices = computed(() => {
  if (activeCategory.value === 'All') return props.services;
  return props.services.filter(s => {
    const name = s.category?.name || s.category_name;
    return name === activeCategory.value;
  });
});

const selectService = (id) => {
  emit('update:modelValue', id);
};
</script>
