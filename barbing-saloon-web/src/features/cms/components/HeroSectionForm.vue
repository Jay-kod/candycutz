<template>
  <div class="grid lg:grid-cols-2 gap-8">
    <!-- Text Content -->
    <div class="space-y-6">
      <div class="group">
        <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
          <DocumentTextIcon class="h-4 w-4 text-admin/80 group-hover:text-admin transition-colors" />
          Hero Title
        </label>
        <div class="relative">
          <input 
            v-model="settings.hero_title" 
            type="text" 
            class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-4 text-lg font-display text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all hover:border-white/20" 
            placeholder="e.g. Premium Grooming Experience"
          />
          <div class="absolute inset-0 rounded-xl pointer-events-none border border-admin/0 group-hover:border-admin/20 transition-colors"></div>
        </div>
      </div>

      <div class="group">
        <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
          <ChatBubbleBottomCenterTextIcon class="h-4 w-4 text-admin/80 group-hover:text-admin transition-colors" />
          Hero Subtitle
        </label>
        <div class="relative">
          <textarea 
            v-model="settings.hero_subtitle" 
            rows="4" 
            class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-4 text-sm text-ivory/80 placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 resize-none transition-all hover:border-white/20 leading-relaxed" 
            placeholder="Write a compelling subtitle to engage visitors..."
          ></textarea>
          <div class="absolute inset-0 rounded-xl pointer-events-none border border-admin/0 group-hover:border-admin/20 transition-colors"></div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="group">
          <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
            Primary Button Text
          </label>
          <input 
            v-model="settings.hero_btn1_text" 
            type="text" 
            class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-sm font-display text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all hover:border-white/20" 
            placeholder="e.g. Book a Service"
          />
        </div>
        <div class="group">
          <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
            Secondary Button Text
          </label>
          <input 
            v-model="settings.hero_btn2_text" 
            type="text" 
            class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-sm font-display text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all hover:border-white/20" 
            placeholder="e.g. About Us"
          />
        </div>
      </div>
    </div>

    <!-- Image Upload & Opacity -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 uppercase tracking-wider">
          <PhotoIcon class="h-4 w-4 text-admin/80" />
          Hero Background Image
        </label>
        <span v-if="(previewUrl || currentHeroImage) && !imageLoadError" class="text-xs text-ivory/50 font-mono">
          Live Opacity: <strong class="text-admin font-semibold">{{ settings.hero_image_opacity ?? 22 }}%</strong>
        </span>
      </div>
      
      <div class="relative w-full aspect-video rounded-2xl border-2 border-dashed border-white/10 bg-[#090909] hover:border-admin/40 hover:bg-black/80 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer shadow-inner"
           @click="$refs.fileInput.click()">
        
        <!-- Current/Preview Image with dynamic opacity -->
        <img v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
             :src="previewUrl || getStorageUrl(currentHeroImage)" 
             @error="$emit('update:imageLoadError', true)"
             @load="$emit('update:imageLoadError', false)"
             :style="{ opacity: computedPreviewOpacity }"
             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500" />
             
        <!-- Bottom-Left Opacity Pill on Image -->
        <div v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
             class="absolute bottom-3 left-3 z-10 rounded-lg bg-black/80 border border-white/10 px-2.5 py-1 text-[11px] font-mono text-ivory/80 backdrop-blur-sm pointer-events-none">
          Opacity: <span class="text-admin font-bold">{{ settings.hero_image_opacity ?? 22 }}%</span>
        </div>

        <!-- Upload Overlay -->
        <div class="relative z-10 flex flex-col items-center justify-center p-6 text-center" :class="((previewUrl || currentHeroImage) && !imageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''">
          <div class="w-16 h-16 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center mb-4 text-admin group-hover:scale-110 transition-transform">
            <CloudArrowUpIcon class="w-8 h-8" />
          </div>
          <p class="text-theme-text font-display text-lg mb-1">{{ ((previewUrl || currentHeroImage) && !imageLoadError) ? 'Change Image' : 'Upload Image' }}</p>
          <p class="text-xs text-ivory/50">Drag & drop or click to browse</p>
          <p class="text-[10px] text-ivory/30 uppercase tracking-widest mt-4 border border-white/5 bg-black/60 px-3 py-1 rounded-full">1920×1080 • Max 2MB • JPG/PNG</p>
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
      <div class="rounded-xl border border-white/10 bg-black/40 p-5 space-y-4 shadow-lg">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-xs font-bold text-ivory/80 uppercase tracking-wider">
            <AdjustmentsHorizontalIcon class="h-4 w-4 text-admin" />
            Hero Image Opacity
          </label>
          <div class="flex items-center gap-2">
            <span class="text-xs text-ivory/40">Value:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-admin/15 text-admin border border-admin/30">
              {{ settings.hero_image_opacity ?? 22 }}%
            </span>
          </div>
        </div>

        <!-- Slider and Input -->
        <div class="flex items-center gap-4">
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
          <div class="relative w-24 shrink-0">
            <input 
              v-model.number="settings.hero_image_opacity"
              type="number" 
              min="0" 
              max="100"
              aria-label="Hero background image opacity percentage"
              class="w-full rounded-lg border border-white/10 bg-black/60 px-3 py-1.5 text-center text-sm font-mono font-semibold text-theme-text focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50"
            />
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-ivory/40 pointer-events-none font-mono">%</span>
          </div>
        </div>

        <!-- Presets -->
        <div class="flex flex-wrap items-center gap-2 pt-1">
          <span class="text-[11px] uppercase tracking-wider font-semibold text-ivory/40 mr-1">Presets:</span>
          <button
            v-for="preset in [10, 22, 35, 50, 75, 100]"
            :key="preset"
            type="button"
            @click="settings.hero_image_opacity = preset"
            :class="[
              'px-2.5 py-1 rounded-md border text-xs font-medium transition-all',
              (settings.hero_image_opacity ?? 22) === preset
                ? 'bg-admin text-obsidian border-admin font-bold shadow-[0_2px_10px_rgba(255,103,0,0.3)]'
                : 'bg-white/5 border-white/10 text-ivory/70 hover:bg-white/10 hover:text-white'
            ]"
          >
            {{ preset }}%{{ preset === 22 ? ' (Default)' : '' }}
          </button>
        </div>

        <p class="text-xs text-ivory/50 leading-relaxed">
          Controls the background image opacity on the landing page hero section. Recommended 15%–30% for optimal text contrast and readability.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { 
  DocumentTextIcon, 
  ChatBubbleBottomCenterTextIcon, 
  PhotoIcon, 
  CloudArrowUpIcon,
  AdjustmentsHorizontalIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  settings: Object,
  previewUrl: String,
  currentHeroImage: String,
  imageLoadError: Boolean,
  getStorageUrl: Function
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
