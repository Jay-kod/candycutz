<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/15 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_15px_rgba(255,103,0,0.15)]">
          <PhotoIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Gallery Header & Cover Photography</h2>
          <p class="text-xs text-white/50 mt-0.5">Title, descriptive hook, studio cover image, and lighting overlay dim for your portfolio.</p>
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
              {{ (content.gallery_title || '').length }} chars
            </span>
          </div>
          <input 
            v-model="content.gallery_title" 
            type="text" 
            placeholder="e.g. Our Gallery" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-base font-display text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
          />
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Page Descriptive Subtitle
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.gallery_subtitle || '').length }} chars
            </span>
          </div>
          <textarea 
            v-model="content.gallery_subtitle" 
            rows="3" 
            placeholder="Browse our portfolio of premium cuts, styles, and grooming transformations..." 
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
            <p class="text-[11px] text-white/40">Upload JPG, PNG, or WEBP up to 5MB. Appears behind the public hero title.</p>
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
                :class="[
                  'px-2 py-0.5 rounded-md border text-[11px] font-medium transition-all',
                  modelHeroOpacity === preset
                    ? 'bg-admin text-obsidian border-admin font-bold shadow-[0_2px_8px_rgba(255,103,0,0.3)]'
                    : 'bg-white/[0.03] border-white/[0.08] text-white/60 hover:bg-white/[0.08] hover:text-white'
                ]"
              >
                {{ preset }}%{{ preset === 76 ? ' (Default)' : '' }}
              </button>
            </div>
          </div>

          <div v-if="previewImage" class="pt-2 flex justify-end">
            <button
              type="button"
              @click="$emit('clear-image')"
              class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-500/30 text-red-400 bg-red-500/10 hover:bg-red-500/20 transition-all"
            >
              Remove Cover Image
            </button>
          </div>
        </div>
      </div>

      <!-- Right: Real-time PageHero Preview -->
      <div class="space-y-3">
        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
          <SparklesIcon class="h-3.5 w-3.5 text-admin" />
          Live Hero Component Preview
        </label>

        <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
          <PageHero
            page-key="gallery"
            :fallback-title="content.gallery_title || 'Our Gallery'"
            :fallback-subtitle="content.gallery_subtitle || 'Browse our portfolio of premium cuts and styles.'"
            :background-image="previewImage"
            :overlay-opacity="modelHeroOpacity"
            :load-settings="false"
          />
        </div>

        <p class="text-xs text-white/40 leading-relaxed">
          Displays the authentic PageHero component as rendered to visitors at /gallery with selected overlay darkness.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { 
  PhotoIcon, 
  SparklesIcon, 
  AdjustmentsHorizontalIcon 
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
  previewImage: {
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

