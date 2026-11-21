<template>
  <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-bg py-3 shadow-inner">
    <!-- Mask edges -->
    <div class="pointer-events-none absolute left-0 top-0 bottom-0 z-10 w-12 bg-gradient-to-r from-theme-bg to-transparent"></div>
    <div class="pointer-events-none absolute right-0 top-0 bottom-0 z-10 w-12 bg-gradient-to-l from-theme-bg to-transparent"></div>

    <div class="preview-marquee-wrapper flex select-none overflow-hidden group">
      <!-- Loop 1 -->
      <div
        class="preview-track flex shrink-0 items-stretch"
        :class="direction === 'right' ? 'preview-track-right' : 'preview-track-left'"
        :style="{ animationDuration: `${speed}s` }"
      >
        <div
          v-for="(card, i) in cards"
          :key="`preview-1-${i}`"
          class="w-[260px] sm:w-[300px] shrink-0 border-r border-theme-border p-3.5 flex flex-col justify-between bg-theme-bg"
        >
          <div class="flex items-center justify-between gap-2">
            <span class="text-theme-text-muted/60 font-serif text-sm">”</span>
            <span class="rounded-full border border-theme-border bg-theme-surface-subtle px-2 py-0.5 text-[10px] text-theme-text-muted">
              {{ card.source }}
            </span>
          </div>
          <p class="text-xs text-theme-text line-clamp-2 my-2 leading-relaxed">
            "{{ card.text }}"
          </p>
          <div class="flex items-center gap-2 pt-1 border-t border-theme-border/50">
            <div class="size-6 rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-[10px] font-bold text-admin">
              {{ card.author.charAt(0) }}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-theme-text truncate">{{ card.author }}</p>
              <p class="text-[10px] text-theme-text-muted truncate">{{ card.role }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Loop 2 (Symmetric duplicate for seamless loop) -->
      <div
        class="preview-track flex shrink-0 items-stretch"
        :class="direction === 'right' ? 'preview-track-right' : 'preview-track-left'"
        :style="{ animationDuration: `${speed}s` }"
        aria-hidden="true"
      >
        <div
          v-for="(card, i) in cards"
          :key="`preview-2-${i}`"
          class="w-[260px] sm:w-[300px] shrink-0 border-r border-theme-border p-3.5 flex flex-col justify-between bg-theme-bg"
        >
          <div class="flex items-center justify-between gap-2">
            <span class="text-theme-text-muted/60 font-serif text-sm">”</span>
            <span class="rounded-full border border-theme-border bg-theme-surface-subtle px-2 py-0.5 text-[10px] text-theme-text-muted">
              {{ card.source }}
            </span>
          </div>
          <p class="text-xs text-theme-text line-clamp-2 my-2 leading-relaxed">
            "{{ card.text }}"
          </p>
          <div class="flex items-center gap-2 pt-1 border-t border-theme-border/50">
            <div class="size-6 rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-[10px] font-bold text-admin">
              {{ card.author.charAt(0) }}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-theme-text truncate">{{ card.author }}</p>
              <p class="text-[10px] text-theme-text-muted truncate">{{ card.role }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  cards: {
    type: Array,
    required: true,
  },
  speed: {
    type: Number,
    default: 45,
  },
  direction: {
    type: String,
    default: 'left',
    validator: (v) => ['left', 'right'].includes(v),
  },
});
</script>

<style scoped>
.preview-marquee-wrapper {
  mask-image: linear-gradient(to right, transparent, black 30px, black calc(100% - 30px), transparent);
  -webkit-mask-image: linear-gradient(to right, transparent, black 30px, black calc(100% - 30px), transparent);
}

.preview-track {
  display: flex;
  flex-shrink: 0;
  will-change: transform;
  transition: none !important;
}

.preview-track-left {
  animation: preview-scroll-left 45s linear infinite;
}

.preview-track-right {
  animation: preview-scroll-right 45s linear infinite;
}

.preview-marquee-wrapper:hover .preview-track {
  animation-play-state: paused;
}

@keyframes preview-scroll-left {
  0% { transform: translateX(0%); }
  100% { transform: translateX(-100%); }
}

@keyframes preview-scroll-right {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(0%); }
}
</style>
