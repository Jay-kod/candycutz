<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-t-transparent" :class="colorTheme === 'gold' ? 'border-gold' : 'border-admin'"></div>
    </div>

    <!-- Empty State -->
    <div v-else-if="gallery.length === 0" class="rounded-2xl border border-theme-border bg-theme-surface/80 py-20 text-center">
      <PhotoIcon class="mx-auto h-12 w-12 text-ivory/20" />
      <h3 class="mt-4 font-display text-xl text-theme-text">No gallery images yet</h3>
      <p class="mt-2 text-sm text-ivory/50">Upload photos of your best cuts to attract customers.</p>
    </div>

    <!-- Gallery Grid -->
    <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div
        v-for="item in gallery"
        :key="item.id"
        class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 transition-all"
        :class="colorTheme === 'gold' ? 'hover:border-gold/20 hover:shadow-[0_0_30px_rgba(212,175,55,0.08)]' : 'hover:border-admin/30 hover:shadow-[0_0_30px_rgba(255,103,0,0.08)]'"
      >
        <div class="aspect-square overflow-hidden">
          <img
            :src="getImageUrl(item.image_path || item.image_url)"
            :alt="item.title"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
          />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
        <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-4 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
          <div class="flex items-end justify-between">
            <div>
              <p class="font-display text-lg text-theme-text">{{ item.title }}</p>
              <div class="flex items-center gap-2">
                <p class="text-[10px] uppercase font-bold" :class="colorTheme === 'gold' ? 'text-gold-light' : 'text-admin-light'">{{ item.category }}</p>
                <p v-if="item.barber_name" class="text-[10px] text-ivory/50 flex items-center gap-1">
                    <ScissorsIcon class="h-3 w-3" /> By {{ item.barber_name }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-1.5">
              <button
                @click="$emit('edit', item)"
                class="rounded-lg bg-black/40 p-2 text-ivory/70 transition-colors backdrop-blur-sm"
                :class="colorTheme === 'gold' ? 'hover:text-gold hover:bg-gold/10' : 'hover:text-admin-light hover:bg-admin/10'"
                title="Edit"
              >
                <PencilSquareIcon class="h-4 w-4" />
              </button>
              <button
                @click="$emit('delete', item.id)"
                class="rounded-lg bg-red-500/20 p-2 text-red-400 hover:bg-red-500/40 transition-colors backdrop-blur-sm"
                title="Delete"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { PhotoIcon, PencilSquareIcon, TrashIcon, ScissorsIcon } from '@heroicons/vue/24/outline';

defineProps({
  loading: Boolean,
  gallery: Array,
  colorTheme: {
    type: String,
    default: 'admin' // 'admin' or 'gold'
  },
  getImageUrl: Function
});

defineEmits(['edit', 'delete']);
</script>
