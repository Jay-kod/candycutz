<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Portfolio</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Your <span class="text-gold">Gallery</span> <span class="text-xl text-ivory/50">({{ gallery.length }})</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Showcase your best work. Upload haircut photos that customers can browse and bookmark.
            </p>
          </div>
          <button
            @click="openUploadForm()"
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
          class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 transition-all hover:border-gold/20 hover:shadow-[0_0_30px_rgba(212,175,55,0.08)]"
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
                <p class="text-xs text-gold-light/80">{{ item.category }}</p>
              </div>
              <div class="flex items-center gap-1.5">
                <button
                  @click="openEditForm(item)"
                  class="rounded-lg bg-black/40 p-2 text-ivory/70 hover:text-gold hover:bg-gold/10 transition-colors backdrop-blur-sm"
                  title="Edit"
                >
                  <PencilSquareIcon class="h-4 w-4" />
                </button>
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
      </div>

      <!-- Upload Modal -->
      <Teleport to="body">
        <div v-if="showUploadForm" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4 sm:p-6" @click.self="showUploadForm = false">
          <div class="w-full max-w-6xl h-[90vh] sm:h-[85vh] rounded-3xl border border-gold/20 bg-theme-surface shadow-2xl animate-fade-in flex flex-col md:flex-row overflow-hidden">
            
            <!-- Left Side: Image Preview -->
            <div class="md:w-1/2 h-64 md:h-full bg-obsidian border-b md:border-b-0 md:border-r border-gold/10 relative overflow-hidden flex items-center justify-center group">
              <div class="absolute inset-0 bg-gradient-to-br from-gold/5 to-transparent pointer-events-none"></div>
              
              <template v-if="previewImage">
                <img :src="previewImage" alt="Preview" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" @error="handleImageError" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent pointer-events-none"></div>
                <div class="absolute bottom-6 left-6 right-6">
                   <p class="font-display text-3xl text-white drop-shadow-lg">{{ uploadForm.title || 'Untitled' }}</p>
                   <p class="text-gold mt-1 uppercase tracking-widest text-xs font-bold">{{ uploadForm.category }}</p>
                </div>
              </template>
              <template v-else>
                <div class="text-center p-8">
                  <div class="w-24 h-24 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <PhotoIcon class="h-10 w-10 text-gold/40" />
                  </div>
                  <h3 class="font-display text-xl text-theme-text mb-2">Image Preview</h3>
                  <p class="text-sm text-ivory/40">Select an image to see how it will look in your gallery.</p>
                </div>
              </template>
            </div>

            <!-- Right Side: Form Details -->
            <div class="md:w-1/2 h-full flex flex-col bg-theme-surface relative">
              <!-- Close Button -->
              <button @click="showUploadForm = false" class="absolute top-6 right-6 p-2 rounded-xl bg-white/5 hover:bg-white/10 text-ivory/60 hover:text-white transition-colors z-10">
                <XMarkIcon class="h-5 w-5" />
              </button>

              <div class="flex-1 overflow-y-auto p-8 sm:p-10" style="scrollbar-width: none;">
                <h2 class="font-display text-3xl text-theme-text mb-2">{{ editingId ? 'Edit Masterpiece' : 'New Masterpiece' }}</h2>
                <p class="text-sm text-ivory/50 mb-10">Add details about this cut to attract more customers.</p>

                <form class="space-y-6" @submit.prevent="saveImage" id="galleryForm">
                  <div class="space-y-3">
                    <label class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">Image</label>
                    <div class="relative w-full rounded-2xl border border-white/10 bg-black/30 px-5 py-4 transition-all focus-within:border-gold/50 focus-within:bg-black/50 focus-within:ring-1 focus-within:ring-gold/50 cursor-pointer overflow-hidden group">
                      <input type="file" accept="image/*" @change="onFileSelected" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" :required="!editingId && !uploadForm.image_url" />
                      <div class="flex items-center gap-3">
                        <PhotoIcon class="h-6 w-6 text-gold/50 group-hover:text-gold transition-colors" />
                        <span class="text-theme-text truncate flex-1">{{ selectedFileName || 'Choose an image file...' }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <label class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">Title</label>
                    <input v-model="uploadForm.title" type="text" required class="w-full rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text placeholder-white/20 outline-none transition-all focus:border-gold/50 focus:bg-black/50 focus:ring-1 focus:ring-gold/50" placeholder="e.g. Skin Fade w/ Design" />
                  </div>

                  <div class="space-y-3">
                    <label class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">Category</label>
                    <div class="relative">
                      <select v-model="uploadForm.category" class="w-full appearance-none rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text outline-none transition-all focus:border-gold/50 focus:bg-black/50 focus:ring-1 focus:ring-gold/50">
                        <option value="Haircut">Haircut</option>
                        <option value="Beard">Beard</option>
                        <option value="Design">Design</option>
                        <option value="Color">Color</option>
                        <option value="Style">Style</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-gold/50">
                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <label class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">Description</label>
                    <textarea v-model="uploadForm.description" rows="4" class="w-full rounded-2xl border border-white/10 bg-black/30 px-5 py-4 text-theme-text placeholder-white/20 outline-none transition-all focus:border-gold/50 focus:bg-black/50 focus:ring-1 focus:ring-gold/50 resize-none" placeholder="Details about the tools, techniques, or products used..."></textarea>
                  </div>
                </form>
              </div>

              <!-- Action Bar -->
              <div class="p-6 sm:p-8 bg-black/20 border-t border-white/5 flex gap-4 shrink-0">
                <button type="button" @click="showUploadForm = false" class="rounded-2xl border border-white/10 bg-white/5 px-8 py-4 text-sm font-bold text-ivory/80 hover:bg-white/10 hover:text-white transition-colors">
                  Cancel
                </button>
                <button type="submit" form="galleryForm" :disabled="saving" class="flex-1 rounded-2xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                  <ArrowPathIcon v-if="saving" class="h-5 w-5 animate-spin" />
                  {{ saving ? 'Saving...' : (editingId ? 'Save Masterpiece' : 'Publish to Gallery') }}
                </button>
              </div>
            </div>
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
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { PlusIcon, TrashIcon, PhotoIcon, PencilSquareIcon, XMarkIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const saving = ref(false);
const showUploadForm = ref(false);
const editingId = ref(null);
const gallery = ref([]);
const previewImage = ref('');
const selectedFileName = ref('');
const selectedFile = ref(null);

const uploadForm = reactive({
  image_url: '',
  title: '',
  category: 'Haircut',
  description: '',
});

function handleImageError(e) {
  e.target.style.display = 'none';
}

function onFileSelected(event) {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    selectedFileName.value = file.name;
    previewImage.value = URL.createObjectURL(file);
  } else {
    selectedFile.value = null;
    selectedFileName.value = '';
    previewImage.value = uploadForm.image_url ? (uploadForm.image_url.startsWith('http') ? uploadForm.image_url : `http://localhost:8000${uploadForm.image_url}`) : '';
  }
}

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

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  if (path.startsWith('/images/')) return path;
  return `${import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')}${path}`;
}

function openUploadForm() {
  editingId.value = null;
  uploadForm.image_url = '';
  uploadForm.title = '';
  uploadForm.category = 'Haircut';
  uploadForm.description = '';
  selectedFile.value = null;
  selectedFileName.value = '';
  previewImage.value = '';
  showUploadForm.value = true;
}

function openEditForm(item) {
  editingId.value = item.id;
  uploadForm.image_url = item.image_path || item.image_url || '';
  uploadForm.title = item.title;
  uploadForm.category = item.category;
  uploadForm.description = item.description || '';
  selectedFile.value = null;
  selectedFileName.value = '';
  previewImage.value = uploadForm.image_url ? (uploadForm.image_url.startsWith('http') ? uploadForm.image_url : `http://localhost:8000${uploadForm.image_url}`) : '';
  showUploadForm.value = true;
}

async function saveImage() {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('title', uploadForm.title);
    formData.append('category', uploadForm.category);
    formData.append('description', uploadForm.description);
    if (uploadForm.image_url && !selectedFile.value) {
      formData.append('image_url', uploadForm.image_url);
    }
    if (selectedFile.value) {
      formData.append('image', selectedFile.value);
    }

    if (editingId.value) {
      await barberApi.updateGalleryImage(editingId.value, formData);
      toast.success('Image updated successfully');
    } else {
      await barberApi.addGalleryImage(formData);
      toast.success('Image added to gallery');
    }
    showUploadForm.value = false;
    await fetchGallery();
  } catch (err) {
    const errorMsg = err.response?.data?.error || (editingId.value ? 'Failed to update image' : 'Failed to upload image');
    toast.error(errorMsg);
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
