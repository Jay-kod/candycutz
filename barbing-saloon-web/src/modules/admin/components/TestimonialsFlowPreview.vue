<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0C0C0E] py-3 shadow-inner">
    <!-- Mask edges -->
    <div class="pointer-events-none absolute left-0 top-0 bottom-0 z-10 w-12 bg-gradient-to-r from-[#0C0C0E] to-transparent"></div>
    <div class="pointer-events-none absolute right-0 top-0 bottom-0 z-10 w-12 bg-gradient-to-l from-[#0C0C0E] to-transparent"></div>

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
          class="w-[260px] sm:w-[300px] shrink-0 border-r border-white/10 p-3.5 flex flex-col justify-between bg-[#0C0C0E]"
        >
          <div class="flex items-center justify-between gap-2">
            <span class="text-zinc-500 font-serif text-sm">”</span>
            <span class="rounded-full border border-zinc-700/60 bg-zinc-800/40 px-2 py-0.5 text-[10px] text-zinc-300">
              {{ card.source }}
            </span>
          </div>
          <p class="text-xs text-zinc-200 line-clamp-2 my-2 leading-relaxed">
            "{{ card.text }}"
          </p>
          <div class="flex items-center gap-2 pt-1 border-t border-white/5">
            <div class="size-6 rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-[10px] font-bold text-admin">
              {{ card.author.charAt(0) }}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-white truncate">{{ card.author }}</p>
              <p class="text-[10px] text-zinc-400 truncate">{{ card.role }}</p>
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
          class="w-[260px] sm:w-[300px] shrink-0 border-r border-white/10 p-3.5 flex flex-col justify-between bg-[#0C0C0E]"
        >
          <div class="flex items-center justify-between gap-2">
            <span class="text-zinc-500 font-serif text-sm">”</span>
            <span class="rounded-full border border-zinc-700/60 bg-zinc-800/40 px-2 py-0.5 text-[10px] text-zinc-300">
              {{ card.source }}
            </span>
          </div>
          <p class="text-xs text-zinc-200 line-clamp-2 my-2 leading-relaxed">
            "{{ card.text }}"
          </p>
          <div class="flex items-center gap-2 pt-1 border-t border-white/5">
            <div class="size-6 rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-[10px] font-bold text-admin">
              {{ card.author.charAt(0) }}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-white truncate">{{ card.author }}</p>
              <p class="text-[10px] text-zinc-400 truncate">{{ card.role }}</p>
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

