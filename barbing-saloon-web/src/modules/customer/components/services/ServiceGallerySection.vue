<template>
  <div class="space-y-4">
    <!-- Main Image Carousel -->
    <div class="relative rounded-3xl overflow-hidden border border-theme-border bg-theme-surface aspect-[16/10] md:aspect-[2/1] group shadow-2xl">
      <div v-if="images.length > 0" class="w-full h-full relative">
        <img :src="images[activeImage]" :alt="serviceName" class="w-full h-full object-cover transition-all duration-500" />
        <div class="absolute inset-0 bg-gradient-to-t from-obsidian/80 via-transparent to-transparent"></div>
        
        <!-- Carousel Controls -->
        <div v-if="images.length > 1" class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-10">
          <button 
            v-for="(img, idx) in images" 
            :key="idx"
            @click="$emit('update:activeImage', idx)"
            class="h-2 rounded-full transition-all duration-300"
            :class="activeImage === idx ? 'w-8 bg-gold' : 'w-2 bg-white/50 hover:bg-white'"
          ></button>
        </div>
        
        <button v-if="images.length > 1" @click="$emit('prevImage')" class="absolute left-4 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/50 backdrop-blur-md border border-white/10 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all hover:bg-gold hover:text-black">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </button>
        <button v-if="images.length > 1" @click="$emit('nextImage')" class="absolute right-4 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/50 backdrop-blur-md border border-white/10 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all hover:bg-gold hover:text-black">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </button>
      </div>
      <div v-else class="w-full h-full flex flex-col items-center justify-center text-theme-muted/40 bg-theme-bg">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.525W6.75a3.75 3.75 0 00-3.75 3.75v8.25c0 2.071 1.679 3.75 3.75 3.75h10.5a3.75 3.75 0 003.75-3.75v-8.25c0-2.071-1.679-3.75-3.75-3.75h-3.375c-.621 0-1.125-.504-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z" />
        </svg>
        <span class="text-sm">No images available</span>
      </div>
    </div>

    <!-- Image Thumbnails (Always show 3 slots) -->
    <div class="grid grid-cols-3 gap-4">
      <button 
        v-for="idx in [0, 1, 2]" 
        :key="idx"
        @click="images[idx] ? $emit('update:activeImage', idx) : null"
        class="relative aspect-[4/3] rounded-xl overflow-hidden border-2 transition-all"
        :class="[
          images[idx] ? (activeImage === idx ? 'border-gold cursor-pointer' : 'border-transparent opacity-50 hover:opacity-100 cursor-pointer') : 'border-dashed border-theme-border opacity-30 cursor-default bg-theme-surface'
        ]"
      >
        <img v-if="images[idx]" :src="images[idx]" class="w-full h-full object-cover" />
        <div v-else class="w-full h-full flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-theme-muted/30">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
          </svg>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  images: { type: Array, default: () => [] },
  serviceName: { type: String, default: '' },
  activeImage: { type: Number, default: 0 }
})

defineEmits(['update:activeImage', 'prevImage', 'nextImage'])
</script>
