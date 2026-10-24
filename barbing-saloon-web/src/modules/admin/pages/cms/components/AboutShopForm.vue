<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-admin/15 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_15px_rgba(255,103,0,0.15)]">
          <BookOpenIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Shop Information & Story</h2>
          <p class="text-xs text-white/50 mt-0.5">The primary introductory narrative detailing CandyCutz heritage, atmosphere, and philosophy.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 01
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Story Narrative Inputs -->
      <div class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Section Eyebrow
            </label>
            <input 
              v-model="content.about_shop_title" 
              type="text" 
              placeholder="e.g. About Us" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
            />
          </div>

          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Main Headline
            </label>
            <input 
              v-model="content.about_shop_subtitle" 
              type="text" 
              placeholder="e.g. A Sharper Standard" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
            />
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Description Paragraph 1
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.about_shop_text_1 || '').length }} chars
            </span>
          </div>
          <textarea 
            v-model="content.about_shop_text_1" 
            rows="4" 
            placeholder="Welcome to CandyCutz, where precision meets style..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed"
          ></textarea>
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Description Paragraph 2
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.about_shop_text_2 || '').length }} chars
            </span>
          </div>
          <textarea 
            v-model="content.about_shop_text_2" 
            rows="4" 
            placeholder="Whether you're here for a quick shape-up or a complete restyle..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed"
          ></textarea>
        </div>
      </div>

      <!-- Right: Salon Interior Photo Upload -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
            <PhotoIcon class="h-3.5 w-3.5 text-admin" />
            Salon Interior Showcase Photo
          </label>
          <span class="text-xs text-white/40 font-mono">Aspect: 4:3 Studio Shot</span>
        </div>
        
        <div 
          class="relative w-full aspect-[4/3] rounded-2xl border-2 border-dashed border-white/[0.12] bg-black/40 hover:border-admin/50 hover:bg-black/60 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer shadow-inner"
          @click="$refs.fileInput.click()"
        >
          <!-- Current / Preview Image -->
          <img 
            v-if="!imageLoadError" 
            :src="previewShopUrl || (currentShopImage ? getStorageUrl(currentShopImage) : 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80')" 
            @error="$emit('update:imageLoadError', true)"
            @load="$emit('update:imageLoadError', false)"
            alt="About shop interior preview"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 group-hover:opacity-40 transition-all duration-500" 
          />
               
          <!-- Upload Overlay -->
          <div 
            class="relative z-10 flex flex-col items-center justify-center p-6 text-center" 
            :class="(!imageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''"
          >
            <div class="w-14 h-14 rounded-full bg-admin/15 border border-admin/30 flex items-center justify-center mb-3 text-admin group-hover:scale-110 transition-transform shadow-[0_0_20px_rgba(255,103,0,0.2)]">
              <CloudArrowUpIcon class="w-7 h-7" />
            </div>
            <p class="text-white font-display text-base mb-1">{{ ((previewShopUrl || currentShopImage) && !imageLoadError) ? 'Replace Interior Photo' : 'Upload Interior Photo' }}</p>
            <p class="text-xs text-white/40">Drag & drop or click to browse</p>
            <p class="text-[10px] text-white/30 uppercase tracking-widest mt-3 border border-white/[0.08] bg-[#0B0F13]/80 px-2.5 py-0.5 rounded-full font-mono">Max 2MB • JPG / PNG</p>
          </div>
          
          <input 
            ref="fileInput"
            type="file" 
            class="hidden"
            @change="onChange" 
            accept="image/jpeg,image/png,image/webp" 
          />
        </div>
        <p class="text-xs text-white/40 leading-relaxed pt-1">
          Displays opposite the shop narrative on the public About Us page. A high-resolution capture of your styling floor is recommended.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { 
  BookOpenIcon, 
  PhotoIcon, 
  CloudArrowUpIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  },
  previewShopUrl: {
    type: String,
    default: null
  },
  currentShopImage: {
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

const onChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    emit('image-upload', file, fileInput.value);
  }
};
</script>
