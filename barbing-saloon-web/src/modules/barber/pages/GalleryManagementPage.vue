<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Portfolio</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-ivory">
              Your <span class="text-gold">Gallery</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Showcase your best work. Upload haircut photos that customers can browse and bookmark.
            </p>
          </div>
          <button
            @click="showUploadForm = true"
            class="flex items-center gap-2 shrink-0 rounded-xl bg-gradient-to-r from-gold to-gold-dark px-5 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]"
          >
            <PlusIcon class="h-5 w-5" />
            Upload Image
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gold border-t-transparent"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="gallery.length === 0" class="rounded-2xl border border-white/[0.06] bg-charcoal/80 py-20 text-center">
        <PhotoIcon class="mx-auto h-12 w-12 text-ivory/20" />
        <h3 class="mt-4 font-display text-xl text-ivory">No gallery images yet</h3>
        <p class="mt-2 text-sm text-ivory/50">Upload photos of your best cuts to attract customers.</p>
      </div>

      <!-- Gallery Grid -->
      <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div
          v-for="item in gallery"
          :key="item.id"
          class="group relative overflow-hidden rounded-2xl border border-white/[0.06] bg-charcoal/80 transition-all hover:border-gold/20 hover:shadow-[0_0_30px_rgba(212,175,55,0.08)]"
        >
          <div class="aspect-square overflow-hidden">
            <img
              :src="item.image_path || item.image_url"
              :alt="item.title"
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
          </div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
          <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-4 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
            <div class="flex items-end justify-between">
              <div>
                <p class="font-display text-lg text-white">{{ item.title }}</p>
                <p class="text-xs text-gold-light/80">{{ item.category }}</p>
              </div>
              <button
                @click="deleteImage(item.id)"
                class="rounded-lg bg-red-500/20 p-2 text-red-400 hover:bg-red-500/40 transition-colors backdrop-blur-sm"
                title="Delete"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload Modal -->
      <Teleport to="body">
        <div v-if="showUploadForm" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" @click.self="showUploadForm = false">
          <div class="w-full max-w-lg rounded-2xl border border-gold/20 bg-charcoal p-8 shadow-2xl animate-fade-in">
            <h2 class="font-display text-2xl text-ivory">Upload Gallery Image</h2>
            <form class="mt-6 space-y-5" @submit.prevent="uploadImage">
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Image URL</label>
                <input v-model="uploadForm.image_url" type="url" required class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory placeholder-ivory/30 outline-none transition-colors focus:border-gold/50" placeholder="https://example.com/image.jpg" />
                <p class="text-xs text-ivory/30">Paste a direct link to your image</p>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Title</label>
                <input v-model="uploadForm.title" type="text" required class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory placeholder-ivory/30 outline-none transition-colors focus:border-gold/50" placeholder="e.g. Clean Low Fade" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Category</label>
                  <select v-model="uploadForm.category" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory outline-none transition-colors focus:border-gold/50">
                    <option value="Haircut">Haircut</option>
                    <option value="Beard">Beard</option>
                    <option value="Design">Design</option>
                    <option value="Color">Color</option>
                    <option value="Style">Style</option>
                  </select>
                </div>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Description</label>
                <textarea v-model="uploadForm.description" rows="2" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory placeholder-ivory/30 outline-none transition-colors focus:border-gold/50 resize-none" placeholder="Optional description..."></textarea>
              </div>
              <div class="flex gap-3 pt-2">
                <button type="submit" :disabled="saving" class="flex-1 rounded-xl bg-gradient-to-r from-gold to-gold-dark py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-50">
                  {{ saving ? 'Uploading...' : 'Add to Gallery' }}
                </button>
                <button type="button" @click="showUploadForm = false" class="rounded-xl border border-white/[0.08] px-6 py-3 text-sm font-medium text-ivory/60 hover:bg-white/5 transition-colors">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from 'vue-toastification';
import { useConfirm } from '../../../core/composables/useConfirm';
import { PlusIcon, TrashIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const saving = ref(false);
const showUploadForm = ref(false);
const gallery = ref([]);

const uploadForm = reactive({
  image_url: '',
  title: '',
  category: 'Haircut',
  description: '',
});

async function fetchGallery() {
  try {
    const res = await barberApi.getGallery();
    gallery.value = res.data.data || [];
  } catch (err) {
    toast.error('Failed to load gallery');
  } finally {
    loading.value = false;
  }
}

async function uploadImage() {
  saving.value = true;
  try {
    await barberApi.addGalleryImage({ ...uploadForm });
    toast.success('Image added to gallery');
    showUploadForm.value = false;
    uploadForm.image_url = '';
    uploadForm.title = '';
    uploadForm.category = 'Haircut';
    uploadForm.description = '';
    await fetchGallery();
  } catch (err) {
    toast.error('Failed to upload image');
  } finally {
    saving.value = false;
  }
}

async function deleteImage(id) {
  const ok = await confirm({ title: 'Delete Gallery Image', message: 'Are you sure you want to delete this gallery image? This action cannot be undone.', confirmText: 'Delete' });
  if (!ok) return;
  try {
    await barberApi.deleteGalleryImage(id);
    gallery.value = gallery.value.filter(g => g.id !== id);
    toast.success('Image deleted');
  } catch (err) {
    toast.error('Failed to delete image');
  }
}

onMounted(fetchGallery);
</script>
