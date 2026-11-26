import { ref, onUnmounted } from 'vue';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { usePublicContentStore } from '@/core/stores/publicContent.store';

export function useGallery(role = 'admin') {
  const toast = useToast();
  const { confirm } = useConfirm();
  const publicStore = usePublicContentStore();
  
  const loading = ref(true);
  const saving = ref(false);
  const gallery = ref([]);
  const barbers = ref([]);
  let optimizeRefreshTimer = null;

  onUnmounted(() => {
    if (optimizeRefreshTimer) {
      window.clearTimeout(optimizeRefreshTimer);
      optimizeRefreshTimer = null;
    }
  });
  
  const fetchGallery = async () => {
    try {
      loading.value = true;
      const res = await api.get('/gallery');
      gallery.value = res.data.data || [];
    } catch (err) {
      toast.error('Failed to load gallery');
    } finally {
      loading.value = false;
    }
  };

  const fetchBarbers = async () => {
    if (role !== 'admin') return;
    try {
      const res = await api.get('/barbers');
      barbers.value = res.data.data || [];
    } catch (err) {
      console.error('Failed to load barbers', err);
    }
  };

  const saveImage = async (formData, editingId) => {
    saving.value = true;
    try {
      let savedItem = null;
      if (editingId) {
        if (formData instanceof FormData) {
          formData.append('_method', 'PUT');
        }
        const res = await api.post(`/gallery/${editingId}`, formData);
        savedItem = res.data?.data;
        toast.success(
          savedItem?.is_optimizing
            ? 'Done! Uploaded — optimizing to WebP in background.'
            : 'Image updated & WebP ready!'
        );
      } else {
        const res = await api.post('/gallery', formData);
        savedItem = res.data?.data;
        toast.success(
          savedItem?.is_optimizing
            ? 'Done! Added to gallery — optimizing to WebP in background.'
            : 'Image added & WebP ready!'
        );
      }
      publicStore.invalidate('gallery');
      await fetchGallery();

      if (savedItem?.is_optimizing && typeof window !== 'undefined') {
        if (optimizeRefreshTimer) {
          window.clearTimeout(optimizeRefreshTimer);
        }
        optimizeRefreshTimer = window.setTimeout(async () => {
          optimizeRefreshTimer = null;
          await fetchGallery();
          publicStore.invalidate('gallery');
        }, 2500);
      }
      return true;
    } catch (err) {
      const errorMsg = err.response?.data?.error || err.response?.data?.message || (editingId ? 'Failed to update image' : 'Failed to upload image');
      toast.error(errorMsg);
      return false;
    } finally {
      saving.value = false;
    }
  };

  const deleteImage = async (id) => {
    const ok = await confirm({ 
      title: 'Delete Gallery Image', 
      message: 'Are you sure you want to delete this gallery image? This action cannot be undone.', 
      confirmText: 'Delete' 
    });
    
    if (!ok) return false;
    
    try {
      await api.delete(`/gallery/${id}`);
      gallery.value = gallery.value.filter(g => g.id !== id);
      toast.success('Image deleted');
      return true;
    } catch (err) {
      toast.error('Failed to delete image');
      return false;
    }
  };

  const getImageUrl = (path) => {
    return getStorageUrl(path);
  };

  return {
    loading,
    saving,
    gallery,
    barbers,
    fetchGallery,
    fetchBarbers,
    saveImage,
    deleteImage,
    getImageUrl
  };
}
