<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/15 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_15px_rgba(255,103,0,0.15)]">
          <WindowIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Hero Section</h2>
          <p class="text-xs text-white/50 mt-0.5">Primary visual hook and main value proposition seen by website visitors.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 01
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Text Content -->
      <div class="space-y-5">
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
            <DocumentTextIcon class="h-3.5 w-3.5 text-admin" />
            Hero Headline
          </label>
          <input 
            v-model="settings.hero_title" 
            type="text" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-base font-display text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
            placeholder="e.g. Premium Grooming with a Sharper Standard."
          />
        </div>

        <div class="space-y-2">
          <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
            <ChatBubbleBottomCenterTextIcon class="h-3.5 w-3.5 text-admin" />
            Hero Subtitle
          </label>
          <textarea 
            v-model="settings.hero_subtitle" 
            rows="4" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 resize-none transition-all outline-none leading-relaxed" 
            placeholder="Write a compelling subtitle to engage visitors..."
          ></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Primary Button Text
            </label>
            <input 
              v-model="settings.hero_btn1_text" 
              type="text" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
              placeholder="e.g. Book a Service"
            />
          </div>
          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Secondary Button Text
            </label>
            <input 
              v-model="settings.hero_btn2_text" 
              type="text" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
              placeholder="e.g. About Us"
            />
          </div>
        </div>
      </div>

      <!-- Right: Image Upload & Opacity -->
      <div class="space-y-5">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
            <PhotoIcon class="h-3.5 w-3.5 text-admin" />
            Background Showcase Image
          </label>
          <span v-if="(previewUrl || currentHeroImage) && !imageLoadError" class="text-xs text-white/50 font-mono">
            Opacity: <strong class="text-admin font-semibold">{{ settings.hero_image_opacity ?? 22 }}%</strong>
          </span>
        </div>
        
        <div 
          class="relative w-full aspect-video rounded-2xl border-2 border-dashed border-white/[0.12] bg-black/40 hover:border-admin/50 hover:bg-black/60 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer shadow-inner"
          @click="$refs.fileInput.click()"
        >
          <!-- Current/Preview Image with dynamic opacity -->
          <img 
            v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
            :src="previewUrl || getStorageUrl(currentHeroImage)" 
            @error="$emit('update:imageLoadError', true)"
            @load="$emit('update:imageLoadError', false)"
            :style="{ opacity: computedPreviewOpacity }"
            alt="Hero background preview"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500" 
          />
               
          <!-- Bottom-Left Opacity Pill on Image -->
          <div 
            v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
            class="absolute bottom-3 left-3 z-10 rounded-lg bg-[#0B0F13]/90 border border-white/10 px-2.5 py-1 text-[11px] font-mono text-white/80 backdrop-blur-md pointer-events-none"
          >
            Live Dim: <span class="text-admin font-bold">{{ settings.hero_image_opacity ?? 22 }}%</span>
          </div>

          <!-- Upload Overlay -->
          <div 
            class="relative z-10 flex flex-col items-center justify-center p-6 text-center" 
            :class="((previewUrl || currentHeroImage) && !imageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''"
          >
            <div class="w-14 h-14 rounded-full bg-admin/15 border border-admin/30 flex items-center justify-center mb-3 text-admin group-hover:scale-110 transition-transform shadow-[0_0_20px_rgba(255,103,0,0.2)]">
              <CloudArrowUpIcon class="w-7 h-7" />
            </div>
            <p class="text-white font-display text-base mb-1">{{ ((previewUrl || currentHeroImage) && !imageLoadError) ? 'Replace Hero Image' : 'Upload Hero Image' }}</p>
            <p class="text-xs text-white/40">Drag & drop or click to browse</p>
            <p class="text-[10px] text-white/30 uppercase tracking-widest mt-3 border border-white/[0.08] bg-[#0B0F13]/80 px-2.5 py-0.5 rounded-full font-mono">1920×1080 • Max 2MB • JPG / PNG</p>
          </div>
          
          <input 
            ref="fileInput"
            type="file" 
            class="hidden"
            @change="onChange" 
            accept="image/jpeg,image/png,image/webp" 
          />
        </div>

        <!-- Hero Image Opacity Controller -->
        <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-3.5">
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
              <AdjustmentsHorizontalIcon class="h-3.5 w-3.5 text-admin" />
              Hero Image Opacity
            </label>
            <div class="flex items-center gap-1.5">
              <span class="text-[11px] text-white/40">Value:</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-admin/15 text-admin border border-admin/30">
                {{ settings.hero_image_opacity ?? 22 }}%
              </span>
            </div>
          </div>

          <!-- Slider and Input -->
          <div class="flex items-center gap-3">
            <div class="relative flex-1 flex items-center">
              <input 
                v-model.number="settings.hero_image_opacity"
                type="range" 
                min="0" 
                max="100" 
                step="1"
                aria-label="Hero background image opacity slider"
                class="w-full h-2 rounded-lg bg-white/10 appearance-none cursor-pointer accent-[#FF6700] hover:bg-white/20 transition-colors"
              />
            </div>
            <div class="relative w-20 shrink-0">
              <input 
                v-model.number="settings.hero_image_opacity"
                type="number" 
                min="0" 
                max="100" 
                aria-label="Hero background image opacity percentage"
                class="w-full rounded-lg border border-white/[0.08] bg-white/[0.04] px-2.5 py-1 text-center text-xs font-mono font-semibold text-white focus:border-admin/60 focus:outline-none focus:ring-1 focus:ring-admin/30"
              />
              <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-white/40 pointer-events-none font-mono">%</span>
            </div>
          </div>

          <!-- Quick Presets -->
          <div class="flex flex-wrap items-center gap-1.5 pt-1">
            <span class="text-[10px] uppercase tracking-wider font-semibold text-white/40 mr-1">Presets:</span>
            <button
              v-for="preset in [10, 22, 35, 50, 75, 100]"
              :key="preset"
              type="button"
              @click="settings.hero_image_opacity = preset"
              :class="[
                'px-2 py-0.5 rounded-md border text-[11px] font-medium transition-all',
                (settings.hero_image_opacity ?? 22) === preset
                  ? 'bg-admin text-obsidian border-admin font-bold shadow-[0_2px_8px_rgba(255,103,0,0.3)]'
                  : 'bg-white/[0.03] border-white/[0.08] text-white/60 hover:bg-white/[0.08] hover:text-white'
              ]"
            >
              {{ preset }}%{{ preset === 22 ? ' (Default)' : '' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
  WindowIcon,
  DocumentTextIcon, 
  ChatBubbleBottomCenterTextIcon, 
  PhotoIcon, 
  CloudArrowUpIcon,
  AdjustmentsHorizontalIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  previewUrl: {
    type: String,
    default: null
  },
  currentHeroImage: {
    type: String,
    default: null
  },
  imageLoadError: {
    type: Boolean,
    default: false
  },
  getStorageUrl: {
    type: Function,
    required: true
  }
});

const emit = defineEmits(['image-upload', 'update:imageLoadError']);
const fileInput = ref(null);

const computedPreviewOpacity = computed(() => {
  const raw = props.settings?.hero_image_opacity;
  if (raw === undefined || raw === null || raw === '') return 0.22;
  const num = Number(raw);
  if (isNaN(num)) return 0.22;
  const clamped = Math.max(0, Math.min(100, num));
  return clamped > 1 ? clamped / 100 : clamped;
});

const onChange = (event) => {
  emit('image-upload', event.target.files[0], fileInput.value);
};
</script>
