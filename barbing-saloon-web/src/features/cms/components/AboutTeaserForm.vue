<template>
  <div class="border-t border-white/5 px-8 py-8 space-y-6">
    <div>
      <h3 class="font-display text-xl text-theme-text">About Us Teaser</h3>
      <p class="text-sm text-ivory/50">The introductory philosophy section on the home page.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-6">
        <div>
          <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Title</label>
          <input v-model="settings.about_teaser_title" type="text" placeholder="Our Philosophy" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
        </div>
        <div>
          <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Heading</label>
          <input v-model="settings.about_teaser_subtitle" type="text" placeholder="A Sharper Standard" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
        </div>
      </div>
      <div>
        <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description Text</label>
        <textarea v-model="settings.about_teaser_text" rows="5" placeholder="Welcome to CandyCutz..." class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all resize-none"></textarea>
      </div>
    </div>
    
    <div class="mt-6">
      <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
        <PhotoIcon class="h-4 w-4 text-admin/80" />
        Side Image
      </label>
      
      <div class="relative w-full max-w-md aspect-[4/3] rounded-2xl border-2 border-dashed border-white/10 bg-black/20 hover:border-admin/40 hover:bg-admin/5 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer"
           @click="$refs.aboutFileInput.click()">
        
        <!-- Current/Preview Image -->
        <img v-if="!aboutImageLoadError" 
             :src="previewAboutUrl || (currentAboutImage ? getStorageUrl(currentAboutImage) : 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80')" 
             @error="$emit('update:aboutImageLoadError', true)"
             @load="$emit('update:aboutImageLoadError', false)"
             class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-40 transition-all duration-500" />
             
        <!-- Upload Overlay -->
        <div class="relative z-10 flex flex-col items-center justify-center p-6 text-center" :class="(!aboutImageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''">
          <div class="w-16 h-16 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center mb-4 text-admin group-hover:scale-110 transition-transform">
            <CloudArrowUpIcon class="w-8 h-8" />
          </div>
          <p class="text-theme-text font-display text-lg mb-1">{{ ((previewAboutUrl || currentAboutImage) && !aboutImageLoadError) ? 'Change Image' : 'Upload Image' }}</p>
          <p class="text-[10px] text-ivory/30 uppercase tracking-widest mt-2 border border-white/5 bg-black/40 px-3 py-1 rounded-full">Max 2MB • JPG/PNG</p>
        </div>
        
        <input 
          ref="aboutFileInput"
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
import { PhotoIcon, CloudArrowUpIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  settings: Object,
  previewAboutUrl: String,
  currentAboutImage: String,
  aboutImageLoadError: Boolean,
  getStorageUrl: Function
});

const emit = defineEmits(['image-upload', 'update:aboutImageLoadError']);
const aboutFileInput = ref(null);

const onChange = (event) => {
  emit('image-upload', event.target.files[0], aboutFileInput.value);
};
</script>
