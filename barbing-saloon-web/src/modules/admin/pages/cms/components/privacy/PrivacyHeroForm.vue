<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/15 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_15px_rgba(255,103,0,0.15)]">
          <PhotoIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Privacy Page Header & Cover Photography</h2>
          <p class="text-xs text-white/50 mt-0.5">Title, legal intro subtitle, banner cover photography, and lighting overlay dim.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 01
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Form Inputs & Image Controller -->
      <div class="space-y-5">
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Page Main Headline
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.privacy_title || '').length }} chars
            </span>
          </div>
          <input 
            v-model="content.privacy_title" 
            type="text" 
            placeholder="e.g. Privacy Policy" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-base font-display text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
          />
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Page Introductory Subtitle
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.privacy_subtitle || '').length }} chars
            </span>
          </div>
          <textarea 
            v-model="content.privacy_subtitle" 
            rows="3" 
            placeholder="How we collect, use, and protect your personal information across all platforms..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed"
          ></textarea>
        </div>

        <!-- Cover Photo Upload & Overlay Controls -->
        <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-4">
          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Hero Cover Image
            </label>
            <input 
              ref="imageInput"
              type="file" 
              accept="image/jpeg,image/png,image/webp" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs text-white file:mr-3 file:rounded-lg file:border-0 file:bg-admin/20 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-admin transition-all"
              @change="onFileChange" 
            />
            <p class="text-[11px] text-white/40">Upload JPG, PNG, or WEBP up to 5MB. Rendered with dark ambient luxury tint.</p>
          </div>

          <!-- Opacity Slider -->
          <div class="space-y-3 pt-2 border-t border-white/[0.06]">
            <div class="flex items-center justify-between">
              <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
                <AdjustmentsHorizontalIcon class="h-3.5 w-3.5 text-admin" />
                Image Overlay Opacity
              </label>
              <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-admin/15 text-admin border border-admin/30">
                {{ modelHeroOpacity }}%
              </span>
            </div>

            <div class="flex items-center gap-3">
              <input 
                v-model.number="modelHeroOpacity"
                type="range" 
                min="0" 
                max="100" 
                class="w-full h-2 rounded-lg bg-white/10 appearance-none cursor-pointer accent-[#FF6700]"
              />
              <span class="text-xs font-mono text-white/50 w-12 text-right">{{ modelHeroOpacity }}%</span>
            </div>

            <!-- Presets -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1">
              <span class="text-[10px] uppercase tracking-wider font-semibold text-white/40 mr-1">Presets:</span>
              <button
                v-for="preset in [25, 50, 76, 85, 90, 100]"
                :key="preset"
                type="button"
                @click="modelHeroOpacity = preset"
                class="px-2 py-0.5 rounded text-[10px] font-mono border transition-all"
                :class="modelHeroOpacity === preset 
                  ? 'bg-admin text-obsidian font-bold border-admin' 
                  : 'bg-white/5 text-white/60 border-white/10 hover:bg-white/10 hover:text-white'"
              >
                {{ preset }}%
              </button>
            </div>
          </div>

          <!-- Remove Image Button -->
          <button 
            v-if="previewHeroImage"
            type="button"
            @click="$emit('clear-image')"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-400 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition-all"
          >
            <TrashIcon class="h-3.5 w-3.5" />
            <span>Remove Cover Image (Revert to Default)</span>
          </button>
        </div>
      </div>

      <!-- Right: Live Component Preview -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-white/60 flex items-center gap-1.5">
            <EyeIcon class="h-3.5 w-3.5 text-admin" />
            Live Component Simulation
          </span>
          <span class="text-[10px] font-mono text-white/40">PageHero Component</span>
        </div>

        <div class="rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-4 sm:p-6 overflow-hidden shadow-inner">
          <PageHero
            page-key="privacy"
            eyebrow="Legal"
            :fallback-title="content.privacy_title || 'Privacy Policy'"
            :fallback-subtitle="content.privacy_subtitle || 'How we collect, use, and protect your information.'"
            :background-image="previewHeroImage"
            :overlay-opacity="modelHeroOpacity"
            :load-settings="false"
          />
        </div>
        <p class="text-[11px] text-white/40 italic">
          Renders identical to the legal header displayed on public web and mobile client views.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
  PhotoIcon, 
  AdjustmentsHorizontalIcon, 
  TrashIcon, 
  EyeIcon 
} from '@heroicons/vue/24/outline';
import PageHero from '@/shared/ui/PageHero.vue';

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  heroOpacity: {
    type: Number,
    default: 76
  },
  previewHeroImage: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:heroOpacity', 'image-selected', 'clear-image']);

const imageInput = ref(null);

const modelHeroOpacity = computed({
  get: () => props.heroOpacity,
  set: (val) => emit('update:heroOpacity', val)
});

const onFileChange = (e) => {
  const file = e.target.files?.[0];
  if (file) {
    emit('image-selected', file);
  }
};

defineExpose({
  imageInput
});
</script>
