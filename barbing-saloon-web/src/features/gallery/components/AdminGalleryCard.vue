<template>
  <article class="group relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#12161C] transition-all duration-300 hover:border-admin/40 hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] flex flex-col">
    <!-- Glowing Top Accent Line on Hover -->
    <div class="absolute top-0 left-0 right-0 h-1 z-30 transition-opacity bg-gradient-to-r from-admin via-amber-400 to-amber-200 opacity-0 group-hover:opacity-100"></div>

    <!-- Media Cover / Image Section -->
    <div class="relative w-full aspect-square bg-obsidian overflow-hidden">
      <img
        :src="getImageUrl(item.image_path || item.image_url)"
        :alt="item.title"
        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
        @error="handleImageError"
      />

      <!-- Fallback Placeholder if Image Error -->
      <div v-if="imageError" class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-[#161B22] to-[#0D1117] p-4 text-center">
        <PhotoIcon class="h-10 w-10 text-white/20 mb-2" />
        <span class="text-[10px] text-white/30 uppercase tracking-widest font-mono">Image Unavailable</span>
      </div>

      <!-- Scrim Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-[#12161C] via-[#12161C]/50 to-transparent opacity-90 sm:opacity-70 transition-opacity duration-300 sm:group-hover:opacity-95 pointer-events-none"></div>

      <!-- Top Left: Category Badge -->
      <div class="absolute top-3.5 left-3.5 z-20 flex items-center gap-1.5">
        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border border-admin/30 bg-[#12161C]/90 text-admin backdrop-blur-md shadow-sm">
          <TagIcon class="h-3 w-3" />
          {{ item.category || 'General' }}
        </span>

        <span v-if="item.is_featured" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[10px] font-bold uppercase tracking-wider border border-amber-500/30 bg-[#12161C]/90 text-amber-400 backdrop-blur-md shadow-sm">
          <SparklesIcon class="h-3 w-3" />
          Featured
        </span>
      </div>

      <!-- Top Right: Action Buttons -->
      <div class="absolute top-3.5 right-3.5 z-20 flex items-center gap-1.5 bg-[#12161C]/80 backdrop-blur-md rounded-xl p-1 border border-white/10 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity shadow-lg">
        <button
          @click="$emit('edit', item)"
          class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors"
          title="Edit Masterpiece"
        >
          <PencilSquareIcon class="h-4 w-4" />
        </button>
        <button
          @click="$emit('delete', item.id)"
          class="p-1.5 rounded-lg text-white/70 hover:text-red-400 hover:bg-red-500/20 transition-colors"
          title="Delete Masterpiece"
        >
          <TrashIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Bottom Content Overlay -->
      <div class="absolute bottom-0 left-0 right-0 p-4 z-20 flex flex-col justify-end">
        <h3 class="font-display text-lg text-white group-hover:text-admin transition-colors leading-tight drop-shadow truncate mb-1">
          {{ item.title }}
        </h3>

        <div class="flex items-center gap-2 text-xs mb-1.5">
          <span v-if="item.barber_name" class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-400/90">
            <ScissorsIcon class="h-3 w-3 text-amber-400" />
            <span>By {{ item.barber_name }}</span>
          </span>
          <span v-else class="text-[11px] text-white/40 italic">
            General Salon Photography
          </span>
        </div>

        <p v-if="item.description" class="text-xs text-white/50 line-clamp-2 leading-relaxed">
          {{ item.description }}
        </p>
      </div>
    </div>
  </article>
</template>

<script setup>
import { ref } from 'vue';
import { 
  TagIcon, 
  SparklesIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  ScissorsIcon,
  PhotoIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  item: {
    type: Object,
    required: true
  },
  getImageUrl: {
    type: Function,
    required: true
  }
});

defineEmits(['edit', 'delete']);

const imageError = ref(false);

function handleImageError() {
  imageError.value = true;
}
</script>
