<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-8">
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.15)]">
          <ShareIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Social Media Constellation & Visibility</h2>
          <p class="text-xs text-white/50 mt-0.5">Control your profile links and selectively toggle their display on the Website Footer, Contact Page, or both.</p>
        </div>
      </div>
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <span class="text-xs font-mono px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 font-bold">
          {{ activeSocialCount }} Active Profile{{ activeSocialCount === 1 ? '' : 's' }}
        </span>
      </div>
    </div>

    <!-- 7 Platforms Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
      <div
        v-for="platform in platformsList"
        :key="platform.id"
        class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-5 hover:border-white/[0.15] transition-all space-y-4"
      >
        <!-- Header: Platform Info & Status Badge -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div
              class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors shadow-sm"
              :class="content[platform.linkKey] ? platform.activeBg : 'bg-white/5 text-white/40'"
            >
              <SocialIcon :name="platform.id" class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">{{ platform.name }}</h3>
              <p class="text-[11px] text-white/40">{{ platform.hint }}</p>
            </div>
          </div>

          <!-- Status Badge -->
          <span
            class="text-[10px] uppercase font-bold tracking-wider px-2.5 py-0.5 rounded-full border transition-colors font-mono"
            :class="getPlatformStatusBadge(platform)"
          >
            {{ getPlatformStatusText(platform) }}
          </span>
        </div>

        <!-- URL / Link Input -->
        <div class="space-y-1.5">
          <label class="block text-[10px] uppercase tracking-wider text-white/50 font-bold">
            Profile Link / Phone Handle
          </label>
          <div class="relative">
            <input
              v-model="content[platform.linkKey]"
              :type="platform.inputType || 'url'"
              :placeholder="platform.placeholder"
              class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13] px-3.5 py-2.5 text-xs text-white placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all font-mono"
            />
            <button
              v-if="content[platform.linkKey]"
              @click="content[platform.linkKey] = ''"
              type="button"
              title="Clear link"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-rose-400 text-xs transition-colors p-1"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Appearance Checkboxes -->
        <div class="pt-3 border-t border-white/[0.04] flex flex-wrap items-center gap-2.5 text-xs">
          <span class="text-white/40 font-medium text-[11px]">Display On:</span>

          <!-- Footer Checkbox -->
          <label
            class="flex items-center gap-2 cursor-pointer select-none rounded-lg px-2.5 py-1.5 transition-all text-xs"
            :class="isOptionChecked(platform.footerKey) && content[platform.linkKey] 
              ? 'bg-admin/15 text-admin border border-admin/30 shadow-sm' 
              : 'bg-white/5 text-white/60 hover:text-white border border-transparent'"
          >
            <input
              type="checkbox"
              :checked="isOptionChecked(platform.footerKey)"
              @change="toggleOption(platform.footerKey, $event)"
              class="rounded border-white/20 text-admin focus:ring-0 bg-black/40 w-3.5 h-3.5"
            />
            <span class="font-semibold text-[11px]">Website Footer</span>
          </label>

          <!-- Contact Page Checkbox -->
          <label
            class="flex items-center gap-2 cursor-pointer select-none rounded-lg px-2.5 py-1.5 transition-all text-xs"
            :class="isOptionChecked(platform.contactKey) && content[platform.linkKey] 
              ? 'bg-admin/15 text-admin border border-admin/30 shadow-sm' 
              : 'bg-white/5 text-white/60 hover:text-white border border-transparent'"
          >
            <input
              type="checkbox"
              :checked="isOptionChecked(platform.contactKey)"
              @change="toggleOption(platform.contactKey, $event)"
              class="rounded border-white/20 text-admin focus:ring-0 bg-black/40 w-3.5 h-3.5"
            />
            <span class="font-semibold text-[11px]">Contact Page</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Live Mockup Previews -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-6 space-y-6">
      <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
        <h3 class="text-xs font-bold uppercase tracking-widest text-admin flex items-center gap-2">
          <EyeIcon class="w-4 h-4" /> Live Appearance Previews
        </h3>
        <span class="text-[11px] text-white/40 font-mono">Real-time simulation</span>
      </div>

      <!-- Preview 1: Footer Social Bar -->
      <div class="space-y-2">
        <div class="flex items-center justify-between text-[11px] text-white/60">
          <span class="font-semibold">1. Footer Social Bar Preview</span>
          <span class="font-mono text-[10px] text-admin">{{ activeFooterPreviewCount }} icon{{ activeFooterPreviewCount === 1 ? '' : 's' }} showing</span>
        </div>
        <div class="rounded-xl border border-white/[0.06] bg-[#070708] p-5 text-center shadow-inner">
          <div class="relative flex items-center">
            <div class="flex-grow border-t border-white/[0.08]"></div>
            <div v-if="activeFooterPreviewCount > 0" class="flex items-center gap-4 px-4 text-zinc-400">
              <span
                v-for="p in activeFooterPreviewList"
                :key="p.id"
                class="text-zinc-400 hover:text-amber-400 transition-colors"
                :title="p.name + ': ' + content[p.linkKey]"
              >
                <SocialIcon :name="p.id" class="h-4 w-4" />
              </span>
            </div>
            <div v-else class="px-4 text-[11px] text-zinc-600 italic">
              (No social icons enabled for website footer)
            </div>
            <div class="flex-grow border-t border-white/[0.08]"></div>
          </div>
        </div>
      </div>

      <!-- Preview 2: Contact Page "Connect With Us" Preview -->
      <div class="space-y-2">
        <div class="flex items-center justify-between text-[11px] text-white/60">
          <span class="font-semibold">2. Contact Page "Connect With Us" Preview</span>
          <span class="font-mono text-[10px] text-admin">{{ activeContactPreviewCount }} icon{{ activeContactPreviewCount === 1 ? '' : 's' }} showing</span>
        </div>
        <div class="rounded-xl border border-white/[0.06] bg-[#12161C] p-5 shadow-inner">
          <h4 class="text-amber-400 font-semibold uppercase tracking-widest text-xs mb-3">Connect With Us</h4>
          <div v-if="activeContactPreviewCount > 0" class="flex flex-wrap gap-3">
            <div
              v-for="p in activeContactPreviewList"
              :key="p.id"
              class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white/80 shadow-md hover:border-amber-400/50 hover:text-amber-400 transition-all cursor-default"
              :title="p.name + ': ' + content[p.linkKey]"
            >
              <SocialIcon :name="p.id" class="w-5 h-5" />
            </div>
          </div>
          <div v-else class="text-xs text-white/40 italic">
            (No social icons enabled for Contact page)
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  ShareIcon, 
  EyeIcon 
} from '@heroicons/vue/24/outline';
import SocialIcon from '@/components/SocialIcon.vue';

defineProps({
  content: {
    type: Object,
    required: true
  },
  platformsList: {
    type: Array,
    required: true
  },
  activeSocialCount: {
    type: Number,
    default: 0
  },
  activeFooterPreviewList: {
    type: Array,
    default: () => []
  },
  activeFooterPreviewCount: {
    type: Number,
    default: 0
  },
  activeContactPreviewList: {
    type: Array,
    default: () => []
  },
  activeContactPreviewCount: {
    type: Number,
    default: 0
  },
  isOptionChecked: {
    type: Function,
    required: true
  },
  toggleOption: {
    type: Function,
    required: true
  },
  getPlatformStatusText: {
    type: Function,
    required: true
  },
  getPlatformStatusBadge: {
    type: Function,
    required: true
  }
});
</script>
