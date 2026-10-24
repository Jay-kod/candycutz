<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/15 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_15px_rgba(255,103,0,0.15)]">
          <SparklesIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Services Page Header Copy</h2>
          <p class="text-xs text-white/50 mt-0.5">The primary title and descriptive subtitle presented at the top of your public services catalog.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 01
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Form Inputs -->
      <div class="space-y-5">
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Page Main Headline
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.services_title || '').length }} chars
            </span>
          </div>
          <input 
            v-model="content.services_title" 
            type="text" 
            placeholder="e.g. Our Premium Services" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-base font-display text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
          />
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Page Descriptive Subtitle
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.services_subtitle || '').length }} chars
            </span>
          </div>
          <textarea 
            v-model="content.services_subtitle" 
            rows="4" 
            placeholder="Explore our full range of grooming services designed to keep you looking sharp and feeling confident..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed"
          ></textarea>
        </div>
      </div>

      <!-- Right: Real-time Public Header Preview -->
      <div class="space-y-3">
        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
          <SparklesIcon class="h-3.5 w-3.5 text-admin" />
          Live Public Header Preview
        </label>

        <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-6 sm:p-8 space-y-3 shadow-2xl text-center">
          <div class="absolute -right-10 -bottom-10 h-36 w-36 rounded-full bg-admin/10 blur-2xl pointer-events-none"></div>

          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-amber-400/30 bg-amber-400/10 text-amber-300 text-[10px] font-bold uppercase tracking-wider">
            Grooming Menu
          </div>

          <h3 class="font-display text-2xl sm:text-3xl text-white font-bold tracking-tight drop-shadow">
            {{ content.services_title || 'Our Premium Services' }}
          </h3>

          <p class="text-xs sm:text-sm text-white/60 leading-relaxed max-w-md mx-auto">
            {{ content.services_subtitle || 'Explore our full range of grooming services designed to keep you looking sharp and feeling confident.' }}
          </p>

          <!-- Mock Tab Strip -->
          <div class="pt-4 flex items-center justify-center gap-2 border-t border-white/[0.06] flex-wrap">
            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-400 text-obsidian">All Treatments</span>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-white/5 border border-white/10 text-white/50">Haircuts</span>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-white/5 border border-white/10 text-white/50">Beard Detailing</span>
          </div>
        </div>

        <p class="text-xs text-white/40 leading-relaxed">
          This title and description are rendered inside the PageHero component on the public services route.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { SparklesIcon } from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  }
});
</script>
