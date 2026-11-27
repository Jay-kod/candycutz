<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
        <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/15 transition-colors duration-700"></div>
        <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
            <PhotoIcon class="h-3.5 w-3.5 text-gold" />
            <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Visual Portfolio</span>
          </div>
          <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-white tracking-tight mb-2">
            Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-400 to-amber-200">Gallery</span>
            <span v-if="!loading" class="ml-3 inline-flex items-center justify-center h-8 px-3 rounded-full bg-white/[0.04] border border-white/[0.08] text-sm text-slate-300 font-normal align-middle">
              {{ gallery.length }}
            </span>
          </h1>
          <p class="text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
            Showcase your best haircuts, precision trims, and styling craft. Upload photos that clients can browse and bookmark.
          </p>
        </div>

        <div class="relative z-10 flex items-center gap-3 shrink-0">
          <button
            @click="openUploadForm"
            class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-6 py-3.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0"
            data-test="upload-image-btn"
          >
            <PlusIcon class="h-4 w-4" />
            Upload Image
          </button>
        </div>
      </div>

      <GalleryGrid 
        :loading="loading" 
        :gallery="gallery" 
        colorTheme="gold" 
        :getImageUrl="getImageUrl" 
        @edit="openEditForm" 
        @delete="deleteImage" 
      />

      <GalleryUploadModal 
        :show="showUploadForm" 
        :saving="saving" 
        :editingId="editingId" 
        :uploadForm="uploadForm" 
        :previewImage="previewImage" 
        :selectedFileName="selectedFileName" 
        :barbers="barbers" 
        :gallery-categories="galleryCategories"
        role="barber" 
        colorTheme="gold" 
        @close="showUploadForm = false" 
        @file-selected="onFileSelected" 
        @save="onSaveImage" 
      />
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { PlusIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import GalleryGrid from '../components/GalleryGrid.vue';
import GalleryUploadModal from '../components/GalleryUploadModal.vue';
import { useGallery } from '../composables/useGallery';

const {
  loading,
  saving,
  gallery,
  barbers,
  galleryCategories,
  fetchGallery,
  saveImage,
  deleteImage,
  getImageUrl
} = useGallery('barber');

const showUploadForm = ref(false);
const editingId = ref(null);
const previewImage = ref('');
const selectedFileName = ref('');
const selectedFile = ref(null);

const uploadForm = reactive({
  image_url: '',
  title: '',
  category: 'haircut',
  description: '',
});

const onFileSelected = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    selectedFileName.value = file.name;
    previewImage.value = URL.createObjectURL(file);
  } else {
    selectedFile.value = null;
    selectedFileName.value = '';
    previewImage.value = uploadForm.image_url ? (uploadForm.image_url.startsWith('http') ? uploadForm.image_url : getImageUrl(uploadForm.image_url)) : '';
  }
};

const openUploadForm = () => {
  editingId.value = null;
  uploadForm.image_url = '';
  uploadForm.title = '';
  uploadForm.category = galleryCategories.value[0]?.slug || 'haircut';
  uploadForm.description = '';
  selectedFile.value = null;
  selectedFileName.value = '';
  previewImage.value = '';
  showUploadForm.value = true;
};

const openEditForm = (item) => {
  editingId.value = item.id;
  uploadForm.image_url = item.image_path || item.image_url || '';
  uploadForm.title = item.title;
  const rawCat = String(item.category || '').trim().toLowerCase();
  const matched = galleryCategories.value.find(
    c => c.slug.toLowerCase() === rawCat || c.name.toLowerCase() === rawCat
  );
  uploadForm.category = matched ? matched.slug : (item.category || galleryCategories.value[0]?.slug || 'haircut');
  uploadForm.description = item.description || '';
  selectedFile.value = null;
  selectedFileName.value = '';
  previewImage.value = uploadForm.image_url ? (uploadForm.image_url.startsWith('http') ? uploadForm.image_url : getImageUrl(uploadForm.image_url)) : '';
  showUploadForm.value = true;
};

const onSaveImage = async () => {
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

  const success = await saveImage(formData, editingId.value);
  if (success) {
    showUploadForm.value = false;
  }
};

onMounted(() => {
  fetchGallery();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
