<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4 sm:p-6" @click.self="$emit('close')">
      <div class="w-full max-w-6xl h-[90vh] sm:h-[85vh] rounded-3xl border bg-theme-surface shadow-2xl animate-fade-in flex flex-col md:flex-row overflow-hidden" :class="colorTheme === 'gold' ? 'border-gold/20' : 'border-admin/20'">
        
        <!-- Left Side: Image Preview -->
        <div class="md:w-1/2 h-64 md:h-full bg-obsidian border-b md:border-b-0 md:border-r relative overflow-hidden flex items-center justify-center group" :class="colorTheme === 'gold' ? 'border-gold/10' : 'border-admin/10'">
          <div class="absolute inset-0 bg-gradient-to-br pointer-events-none" :class="colorTheme === 'gold' ? 'from-gold/5 to-transparent' : 'from-admin/5 to-transparent'"></div>
          
          <template v-if="previewImage">
            <img :src="previewImage" alt="Preview" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" @error="handleImageError" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent pointer-events-none"></div>
            <div class="absolute bottom-6 left-6 right-6">
               <p class="font-display text-3xl text-white drop-shadow-lg">{{ uploadForm.title || 'Untitled' }}</p>
               <p class="mt-1 uppercase tracking-widest text-xs font-bold" :class="colorTheme === 'gold' ? 'text-gold' : 'text-admin'">{{ uploadForm.category }}</p>
            </div>
          </template>
          <template v-else>
            <div class="text-center p-8">
              <div class="w-24 h-24 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-6 shadow-inner">
                <PhotoIcon class="h-10 w-10" :class="colorTheme === 'gold' ? 'text-gold/40' : 'text-admin/40'" />
              </div>
              <h3 class="font-display text-xl text-theme-text mb-2">Image Preview</h3>
              <p class="text-sm text-ivory/40">Select an image to see how it will look in your gallery.</p>
            </div>
          </template>
        </div>

        <!-- Right Side: Form Details -->
        <div class="md:w-1/2 h-full flex flex-col bg-theme-surface relative">
          <!-- Close Button -->
          <button @click="$emit('close')" class="absolute top-6 right-6 p-2 rounded-xl bg-white/5 hover:bg-white/10 text-ivory/60 hover:text-white transition-colors z-10">
            <XMarkIcon class="h-5 w-5" />
          </button>

          <div class="flex-1 overflow-y-auto p-8 sm:p-10" style="scrollbar-width: none;">
            <h2 class="font-display text-3xl text-theme-text mb-2">{{ editingId ? 'Edit Masterpiece' : 'New Masterpiece' }}</h2>
            <p class="text-sm text-ivory/50 mb-10">Add details about this shop photo or haircut.</p>

            <form class="space-y-6" @submit.prevent="saveImage" id="galleryForm">
              <div class="space-y-3">
                <label class="text-xs font-bold uppercase tracking-[0.2em]" :class="colorTheme === 'gold' ? 'text-gold/70' : 'text-admin/70'">Image</label>
                <div class="relative w-full rounded-2xl border border-white/10 bg-black/30 px-5 py-4 transition-all cursor-pointer overflow-hidden group focus-within:bg-black/50 focus-within:ring-1" :class="colorTheme === 'gold' ? 'focus-within:border-gold/50 focus-within:ring-gold/50' : 'focus-within:border-admin/50 focus-within:ring-admin/50'">
                  <input type="file" accept="image/*" @change="onFileSelected" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" :required="!editingId && !uploadForm.image_url" />
                  <div class="flex items-center gap-3">
                    <PhotoIcon class="h-6 w-6 transition-colors" :class="colorTheme === 'gold' ? 'text-gold/50 group-hover:text-gold' : 'text-admin/50 group-hover:text-admin'" />
                    <span class="text-theme-text truncate flex-1">{{ selectedFileName || 'Choose an image file...' }}</span>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <label class="text-xs font-bold uppercase tracking-[0.2em]" :class="colorTheme === 'gold' ? 'text-gold/70' : 'text-admin/70'">Title</label>
                <input v-model="uploadForm.title" type="text" required class="w-full rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text placeholder-white/20 outline-none transition-all focus:bg-black/50 focus:ring-1" :class="colorTheme === 'gold' ? 'focus:border-gold/50 focus:ring-gold/50' : 'focus:border-admin/50 focus:ring-admin/50'" placeholder="e.g. Skin Fade w/ Design" />
              </div>

              <div class="space-y-3">
                <label class="text-xs font-bold uppercase tracking-[0.2em]" :class="colorTheme === 'gold' ? 'text-gold/70' : 'text-admin/70'">Category</label>
                <div class="relative">
                  <select v-model="uploadForm.category" class="w-full appearance-none rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text outline-none transition-all focus:bg-black/50 focus:ring-1" :class="colorTheme === 'gold' ? 'focus:border-gold/50 focus:ring-gold/50' : 'focus:border-admin/50 focus:ring-admin/50'">
                    <option value="Haircut">Haircut</option>
                    <option value="Beard">Beard</option>
                    <option value="Design">Design</option>
                    <option value="Color">Color</option>
                    <option value="Style">Style</option>
                    <option v-if="role === 'admin'" value="Shop">Shop</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5" :class="colorTheme === 'gold' ? 'text-gold/50' : 'text-admin/50'">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                  </div>
                </div>
              </div>
              
              <div v-if="role === 'admin'" class="space-y-3">
                <label class="text-xs font-bold uppercase tracking-[0.2em]" :class="colorTheme === 'gold' ? 'text-gold/70' : 'text-admin/70'">Barber (Optional)</label>
                <div class="relative">
                  <select v-model="uploadForm.barber_id" class="w-full appearance-none rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text outline-none transition-all focus:bg-black/50 focus:ring-1" :class="colorTheme === 'gold' ? 'focus:border-gold/50 focus:ring-gold/50' : 'focus:border-admin/50 focus:ring-admin/50'">
                    <option value="">None (General Shop Image)</option>
                    <option v-for="b in barbers" :key="b.id" :value="b.id">{{ b.user?.name || 'Barber ' + b.id }}</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5" :class="colorTheme === 'gold' ? 'text-gold/50' : 'text-admin/50'">
                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <label class="text-xs font-bold uppercase tracking-[0.2em]" :class="colorTheme === 'gold' ? 'text-gold/70' : 'text-admin/70'">Description</label>
                <textarea v-model="uploadForm.description" rows="4" class="w-full rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text placeholder-white/20 outline-none transition-all resize-none focus:bg-black/50 focus:ring-1" :class="colorTheme === 'gold' ? 'focus:border-gold/50 focus:ring-gold/50' : 'focus:border-admin/50 focus:ring-admin/50'" placeholder="Details about the tools, techniques, or products used..."></textarea>
              </div>
            </form>
          </div>

          <!-- Action Bar -->
          <div class="p-6 sm:p-8 bg-black/20 border-t border-white/5 flex gap-4 shrink-0">
            <button type="button" @click="$emit('close')" class="rounded-2xl border border-white/10 bg-white/5 px-8 py-4 text-sm font-bold text-ivory/80 hover:bg-white/10 hover:text-white transition-colors">
              Cancel
            </button>
            <button type="submit" form="galleryForm" :disabled="saving" class="flex-1 rounded-2xl py-4 text-sm font-bold text-obsidian transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" :class="colorTheme === 'gold' ? 'bg-gradient-to-r from-gold to-gold-dark hover:shadow-[0_0_30px_rgba(212,175,55,0.4)]' : 'bg-gradient-to-r from-admin to-admin-light hover:shadow-[0_0_30px_rgba(255,103,0,0.4)]'">
              <ArrowPathIcon v-if="saving" class="h-5 w-5 animate-spin" />
              {{ saving ? 'Saving...' : (editingId ? 'Save Masterpiece' : 'Publish to Gallery') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { PhotoIcon, XMarkIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
  show: Boolean,
  saving: Boolean,
  editingId: [String, Number],
  uploadForm: Object,
  previewImage: String,
  selectedFileName: String,
  barbers: Array,
  role: {
    type: String,
    default: 'admin'
  },
  colorTheme: {
    type: String,
    default: 'admin'
  }
});

const emit = defineEmits(['close', 'save', 'file-selected']);

const handleImageError = (e) => {
  e.target.style.display = 'none';
};

const onFileSelected = (event) => {
  emit('file-selected', event);
};

const saveImage = () => {
  emit('save');
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
