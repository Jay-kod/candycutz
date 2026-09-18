import { ref } from 'vue';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import api from '@/shared/api/client';

export function useGallery(role = 'admin') {
  const toast = useToast();
  const { confirm } = useConfirm();
  
  const loading = ref(true);
  const saving = ref(false);
  const gallery = ref([]);
  const barbers = ref([]);
  
  const getBaseUrl = () => {
    return role === 'admin' ? '/v1/admin' : '/v1/barber';
  };

  const fetchGallery = async () => {
    try {
      loading.value = true;
      const res = await api.get(`${getBaseUrl()}/gallery`);
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
      const res = await api.get('/v1/admin/barbers');
      barbers.value = res.data.data || [];
    } catch (err) {
      console.error('Failed to load barbers', err);
    }
  };

  const saveImage = async (formData, editingId) => {
    saving.value = true;
    try {
      if (editingId) {
        await api.post(`${getBaseUrl()}/gallery/${editingId}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Image updated successfully');
      } else {
        await api.post(`${getBaseUrl()}/gallery`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Image added to gallery');
      }
      await fetchGallery();
      return true;
    } catch (err) {
      const errorMsg = err.response?.data?.error || (editingId ? 'Failed to update image' : 'Failed to upload image');
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
      await api.delete(`${getBaseUrl()}/gallery/${id}`);
      gallery.value = gallery.value.filter(g => g.id !== id);
      toast.success('Image deleted');
      return true;
    } catch (err) {
      toast.error('Failed to delete image');
      return false;
    }
  };

  const getImageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    if (path.startsWith('/images/')) return path;
    return `${import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')}${path}`;
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
