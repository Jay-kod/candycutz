<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Portfolio</p>
            <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
              Global <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Gallery</span> 
              <span class="flex items-center justify-center h-8 px-3 rounded-full bg-admin/20 border border-admin/30 text-lg text-admin-light">{{ gallery.length }}</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/60 leading-relaxed">
              Showcase the saloon's best work. Upload shop photos, general styles, and manage the complete gallery portfolio.
            </p>
          </div>
          <button
            @click="openUploadForm"
            class="flex items-center gap-2 shrink-0 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(255,103,0,0.25)] transition-all hover:shadow-[0_0_30px_rgba(255,103,0,0.4)] hover:scale-[1.02]"
          >
            <PlusIcon class="h-5 w-5" />
            Upload Image
          </button>
        </div>
      </div>

      <GalleryGrid 
        :loading="loading" 
        :gallery="gallery" 
        colorTheme="admin" 
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
        role="admin" 
        colorTheme="admin" 
        @close="showUploadForm = false" 
        @file-selected="onFileSelected" 
        @save="onSaveImage" 
      />
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/Admin/layouts/Adminlayout.vue';
import GalleryGrid from '../components/GalleryGrid.vue';
import GalleryUploadModal from '../components/GalleryUploadModal.vue';
import { useGallery } from '../composables/useGallery';

const {
  loading,
  saving,
  gallery,
  barbers,
  fetchGallery,
  fetchBarbers,
  saveImage,
  deleteImage,
  getImageUrl
} = useGallery('admin');

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
  barber_id: '',
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
  uploadForm.barber_id = '';
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
  uploadForm.barber_id = item.barber_id || '';
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
  if (uploadForm.barber_id) {
    formData.append('barber_id', uploadForm.barber_id);
  }
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
  fetchBarbers();
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
