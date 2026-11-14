<template>
  <div class="flex flex-wrap items-center gap-1.5 p-2 rounded-xl bg-slate-100 dark:bg-[#0B0F13] border border-slate-200 dark:border-white/[0.08]">
    <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 px-2 py-1 select-none">
      Format:
    </span>

    <button
      type="button"
      @click="$emit('format', 'bold')"
      class="px-2.5 py-1 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors"
      title="Bold (**text**)"
    >
      B
    </button>

    <button
      type="button"
      @click="$emit('format', 'italic')"
      class="px-2.5 py-1 rounded-lg text-xs font-serif italic text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors"
      title="Italic (*text*)"
    >
      I
    </button>

    <div class="h-4 w-[1px] bg-slate-300 dark:bg-white/[0.08] mx-0.5"></div>

    <button
      type="button"
      @click="$emit('format', 'h2')"
      class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors"
      title="Heading 2 (## Title)"
    >
      H2
    </button>

    <button
      type="button"
      @click="$emit('format', 'h3')"
      class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors"
      title="Subheading (### Subtitle)"
    >
      H3
    </button>

    <div class="h-4 w-[1px] bg-slate-300 dark:bg-white/[0.08] mx-0.5"></div>

    <button
      type="button"
      @click="$emit('format', 'bullet')"
      class="px-2 py-1 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors flex items-center gap-1"
      title="Bullet List (- item)"
    >
      <span>•</span>
      <span class="text-[11px]">List</span>
    </button>

    <button
      type="button"
      @click="$emit('format', 'number')"
      class="px-2 py-1 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors flex items-center gap-1"
      title="Numbered Steps (1. step)"
    >
      <span class="text-[11px] font-mono">1.</span>
      <span class="text-[11px]">Steps</span>
    </button>

    <div class="h-4 w-[1px] bg-slate-300 dark:bg-white/[0.08] mx-0.5"></div>

    <button
      type="button"
      @click="$emit('format', 'quote')"
      class="px-2 py-1 rounded-lg text-xs text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors"
      title="Blockquote (> quote)"
    >
      “ Quote
    </button>

    <button
      type="button"
      @click="$emit('format', 'tip')"
      class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-amber-800 dark:text-amber-300 bg-amber-500/10 border border-amber-500/20 hover:bg-amber-500/20 transition-colors flex items-center gap-1"
      title="Insert Barber Pro Tip Box"
    >
      <span>💡 Pro Tip</span>
    </button>

    <button
      type="button"
      @click="$emit('format', 'divider')"
      class="px-2 py-1 rounded-lg text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-colors ml-auto"
      title="Insert Horizontal Divider (---)"
    >
      ― Divider
    </button>
  </div>
</template>

<script setup>
defineEmits(['format']);
</script>
