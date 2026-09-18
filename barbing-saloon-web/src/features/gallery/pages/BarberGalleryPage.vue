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
            @click="openUploadForm"
            class="flex items-center gap-2 shrink-0 rounded-xl bg-gradient-to-r from-gold to-gold-dark px-5 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]"
          >
            <PlusIcon class="h-5 w-5" />
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
import { PlusIcon } from '@heroicons/vue/24/outline';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import GalleryGrid from '../components/GalleryGrid.vue';
import GalleryUploadModal from '../components/GalleryUploadModal.vue';
import { useGallery } from '../composables/useGallery';

const {
  loading,
  saving,
  gallery,
  barbers,
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
  category: 'Haircut',
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
  uploadForm.category = 'Haircut';
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
  uploadForm.category = item.category;
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
