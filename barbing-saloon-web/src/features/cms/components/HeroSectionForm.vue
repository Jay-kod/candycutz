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

    <!-- Image Upload -->
    <div>
      <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
        <PhotoIcon class="h-4 w-4 text-admin/80" />
        Hero Background Image
      </label>
      
      <div class="relative w-full aspect-video rounded-2xl border-2 border-dashed border-white/10 bg-black/20 hover:border-admin/40 hover:bg-admin/5 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer"
           @click="$refs.fileInput.click()">
        
        <!-- Current/Preview Image -->
        <img v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
             :src="previewUrl || getStorageUrl(currentHeroImage)" 
             @error="$emit('update:imageLoadError', true)"
             @load="$emit('update:imageLoadError', false)"
             class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-40 transition-all duration-500" />
             
        <!-- Upload Overlay -->
        <div class="relative z-10 flex flex-col items-center justify-center p-6 text-center" :class="((previewUrl || currentHeroImage) && !imageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''">
          <div class="w-16 h-16 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center mb-4 text-admin group-hover:scale-110 transition-transform">
            <CloudArrowUpIcon class="w-8 h-8" />
          </div>
          <p class="text-theme-text font-display text-lg mb-1">{{ ((previewUrl || currentHeroImage) && !imageLoadError) ? 'Change Image' : 'Upload Image' }}</p>
          <p class="text-xs text-ivory/50">Drag & drop or click to browse</p>
          <p class="text-[10px] text-ivory/30 uppercase tracking-widest mt-4 border border-white/5 bg-black/40 px-3 py-1 rounded-full">1920×1080 • Max 2MB • JPG/PNG</p>
        </div>
        
        <input 
          ref="fileInput"
          type="file" 
          class="hidden"
          @change="onChange" 
          accept="image/jpeg,image/png,image/webp" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { 
  DocumentTextIcon, 
  ChatBubbleBottomCenterTextIcon, 
  PhotoIcon, 
  CloudArrowUpIcon 
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

const onChange = (event) => {
  emit('image-upload', event.target.files[0], fileInput.value);
};
</script>
