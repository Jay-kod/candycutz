<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-6">
    <!-- Header with Counter and Add Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-white/[0.06] pb-4">
      <div>
        <h3 class="text-base font-bold text-white">
          Feature Cards ({{ items.length }})
        </h3>
        <p class="mt-0.5 text-xs text-white/50">
          Configure individual highlight columns, icons, headline titles, and value guarantees.
        </p>
      </div>

      <button
        type="button"
        @click="$emit('add-feature')"
        class="inline-flex items-center gap-1.5 rounded-xl border border-[#FF6700]/30 bg-[#FF6700]/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#FF6700] hover:bg-[#FF6700]/20 transition-all self-start sm:self-auto"
      >
        <PlusIcon class="h-4 w-4" />
        <span>Add Feature Card</span>
      </button>
    </div>

    <!-- Features Cards List -->
    <div class="space-y-5">
      <div
        v-for="(item, idx) in items"
        :key="idx"
        class="group relative rounded-xl border border-white/[0.08] bg-[#0B0F13]/70 p-5 transition-all hover:border-[#FF6700]/30 hover:shadow-lg"
      >
        <!-- Card Top Bar -->
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/[0.06]">
          <div class="flex items-center gap-2.5">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#FF6700]/20 border border-[#FF6700]/40 text-[#FF6700] text-xs font-bold font-mono">
              {{ idx + 1 }}
            </span>
            <span class="text-xs font-bold text-white">
              {{ item.title || 'Untitled Feature' }}
            </span>
          </div>

          <button
            v-if="items.length > 1"
            type="button"
            @click="$emit('remove-feature', idx)"
            class="text-white/40 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition-colors"
            title="Remove this feature card"
          >
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- 2-Column Item Config -->
        <div class="grid gap-6 md:grid-cols-12">
          <!-- Left: Icon Selection -->
          <div class="md:col-span-5 space-y-3">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/60">
              Card Icon
            </label>

            <!-- Active Selected Icon Badge -->
            <div class="flex items-center gap-3 p-3 rounded-xl border border-white/[0.08] bg-[#12161C]">
              <div class="h-10 w-10 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 shrink-0">
                <component :is="resolveIconComponent(item.icon)" class="h-5 w-5" />
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-white capitalize truncate">
                  {{ item.icon || 'scissors' }}
                </p>
                <p class="text-[10px] text-white/40">Active Storefront Icon</p>
              </div>
            </div>

            <!-- Icon Picker Grid (15 Options) -->
            <div class="grid grid-cols-5 gap-1.5 pt-1">
              <button
                v-for="opt in availableIcons"
                :key="opt.name"
                type="button"
                @click="item.icon = opt.name"
                :title="opt.label"
                class="flex items-center justify-center p-2 rounded-lg border transition-all"
                :class="[
                  item.icon === opt.name
                    ? 'border-[#FF6700] bg-[#FF6700]/20 text-[#FF6700] shadow-sm shadow-[#FF6700]/20'
                    : 'border-white/[0.05] bg-white/[0.02] text-white/50 hover:bg-white/[0.06] hover:text-white'
                ]"
              >
                <component :is="opt.comp" class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Right: Content Inputs -->
          <div class="md:col-span-7 space-y-4">
            <!-- Feature Title -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold uppercase tracking-wider text-white/60">
                  Feature Title <span class="text-[#FF6700]">*</span>
                </label>
                <span class="text-[10px] font-mono text-white/30">{{ (item.title || '').length }}/40</span>
              </div>
              <input
                v-model="item.title"
                type="text"
                maxlength="40"
                required
                placeholder="e.g. Precision Craft"
                class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] px-3.5 py-2 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50"
              />
            </div>

            <!-- Feature Description -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold uppercase tracking-wider text-white/60">
                  Description
                </label>
                <span class="text-[10px] font-mono text-white/30">{{ (item.description || '').length }}/120</span>
              </div>
              <textarea
                v-model="item.description"
                rows="2"
                maxlength="120"
                placeholder="e.g. Master barbers dedicated to flawless fades, lines, and custom contours."
                class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] p-3 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 resize-none leading-relaxed"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Add Button -->
    <div class="pt-2 border-t border-white/[0.06] flex items-center justify-between">
      <button
        type="button"
        @click="$emit('add-feature')"
        class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#FF6700] hover:text-amber-400 transition-colors"
      >
        <PlusIcon class="h-4 w-4" />
        <span>Add Another Feature Card</span>
      </button>

      <span class="text-[11px] text-white/40">
        Re-renders automatically in the live preview above
      </span>
    </div>
  </div>
</template>

<script setup>
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

defineProps({
  items: {
    type: Array,
    required: true
  },
  availableIcons: {
    type: Array,
    required: true
  },
  resolveIconComponent: {
    type: Function,
    required: true
  }
});

defineEmits(['add-feature', 'remove-feature']);
</script>

