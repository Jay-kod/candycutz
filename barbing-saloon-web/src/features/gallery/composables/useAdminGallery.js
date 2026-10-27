import { ref, reactive, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';

export function useAdminGallery() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const loading = ref(true);
  const saving = ref(false);
  const isRefreshing = ref(false);
  const gallery = ref([]);
  const barbers = ref([]);

  // Filters & Search
  const search = ref('');
  const activeCategory = ref('all');
  const barberFilter = ref('all'); // 'all', 'shop', or barber_id
  const sortOrder = ref('newest'); // 'newest', 'oldest', 'name-asc', 'category'

  // Modal & Edit state
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
    barber_id: ''
  });

  const getImageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return getStorageUrl(path);
  };

  const categories = computed(() => {
    const counts = {};
    gallery.value.forEach(item => {
      const cat = item.category || 'General';
      counts[cat] = (counts[cat] || 0) + 1;
    });

    const list = Object.keys(counts).sort().map(name => ({
      name,
      count: counts[name]
    }));

    return [
      { name: 'All Works', key: 'all', count: gallery.value.length },
      ...list.map(c => ({ name: c.name, key: c.name, count: c.count }))
    ];
  });

  const stats = computed(() => {
    const list = gallery.value;
    const totalCount = list.length;
    
    // Distinct styling categories
    const distinctCats = new Set(list.map(i => i.category || 'General')).size;

    // Stylists with works
    const distinctBarbers = new Set(list.filter(i => i.barber_id).map(i => i.barber_id)).size;

    // Shop ambiance photos (category === 'Shop' or no barber)
    const shopPhotosCount = list.filter(i => i.category === 'Shop' || !i.barber_id).length;

    // Haircuts & beard styles
    const hairAndBeardCount = list.filter(i => i.category === 'Haircut' || i.category === 'Beard').length;

    return {
      totalCount,
      distinctCats,
      distinctBarbers,
      shopPhotosCount,
      hairAndBeardCount
    };
  });

  const filteredGallery = computed(() => {
    let list = [...gallery.value];

    // 1. Category Filter
    if (activeCategory.value !== 'all') {
      list = list.filter(item => (item.category || 'General') === activeCategory.value);
    }

    // 2. Barber / Contributor Filter
    if (barberFilter.value === 'shop') {
      list = list.filter(item => !item.barber_id || item.category === 'Shop');
    } else if (barberFilter.value !== 'all') {
      list = list.filter(item => String(item.barber_id) === String(barberFilter.value));
    }

    // 3. Search Term
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(item => {
        const titleMatch = item.title?.toLowerCase().includes(q);
        const descMatch = item.description?.toLowerCase().includes(q);
        const catMatch = item.category?.toLowerCase().includes(q);
        const barberMatch = item.barber_name?.toLowerCase().includes(q) || item.barber?.name?.toLowerCase().includes(q);
        return titleMatch || descMatch || catMatch || barberMatch;
      });
    }

    // 4. Sorting
    if (sortOrder.value === 'newest') {
      list.sort((a, b) => (b.id || 0) - (a.id || 0));
    } else if (sortOrder.value === 'oldest') {
      list.sort((a, b) => (a.id || 0) - (b.id || 0));
    } else if (sortOrder.value === 'name-asc') {
      list.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
    } else if (sortOrder.value === 'category') {
      list.sort((a, b) => (a.category || '').localeCompare(b.category || ''));
    }

    return list;
  });

  const fetchGallery = async (silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const res = await api.get('/gallery');
      gallery.value = res.data?.data || res.data || [];
    } catch (err) {
      toast.error('Failed to load gallery portfolio');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  const fetchBarbers = async () => {
    try {
      const res = await api.get('/barbers');
      barbers.value = res.data?.data || res.data || [];
    } catch (err) {
      console.error('Failed to load barbers roster', err);
    }
  };

  const onFileSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
      selectedFile.value = file;
      selectedFileName.value = file.name;
      previewImage.value = URL.createObjectURL(file);
    } else {
      selectedFile.value = null;
      selectedFileName.value = '';
      previewImage.value = uploadForm.image_url ? getImageUrl(uploadForm.image_url) : '';
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
    uploadForm.category = item.category || 'Haircut';
    uploadForm.description = item.description || '';
    uploadForm.barber_id = item.barber_id || '';
    selectedFile.value = null;
    selectedFileName.value = '';
    previewImage.value = uploadForm.image_url ? getImageUrl(uploadForm.image_url) : '';
    showUploadForm.value = true;
  };

  const onSaveImage = async () => {
    saving.value = true;
    const formData = new FormData();
    formData.append('title', uploadForm.title);
    formData.append('category', uploadForm.category);
    formData.append('description', uploadForm.description || '');
    if (uploadForm.barber_id) {
      formData.append('barber_id', uploadForm.barber_id);
    }
    if (uploadForm.image_url && !selectedFile.value) {
      formData.append('image_url', uploadForm.image_url);
    }
    if (selectedFile.value) {
      formData.append('image', selectedFile.value);
    }

    try {
      if (editingId.value) {
        formData.append('_method', 'PUT');
        await api.post(`/gallery/${editingId.value}`, formData);
        toast.success('Masterpiece updated successfully');
      } else {
        await api.post('/gallery', formData);
        toast.success('Masterpiece added to global gallery');
      }
      showUploadForm.value = false;
      await fetchGallery(true);
      return true;
    } catch (err) {
      const errorMsg = err.response?.data?.error || err.response?.data?.message || (editingId.value ? 'Failed to update image' : 'Failed to upload image');
      toast.error(errorMsg);
      return false;
    } finally {
      saving.value = false;
    }
  };

  const deleteImage = async (id) => {
    const target = gallery.value.find(item => item.id === id);
    const titleText = target?.title ? `"${target.title}"` : 'this masterpiece';

    const ok = await confirm(
      'Delete Gallery Image',
      `Are you sure you want to delete ${titleText}? This action cannot be undone.`
    );
    if (!ok) return false;

    try {
      await api.delete(`/gallery/${id}`);
      gallery.value = gallery.value.filter(g => g.id !== id);
      toast.success('Image removed from gallery');
      return true;
    } catch (err) {
      toast.error('Failed to delete image');
      return false;
    }
  };

  const resetFilters = () => {
    search.value = '';
    activeCategory.value = 'all';
    barberFilter.value = 'all';
    sortOrder.value = 'newest';
  };

  return {
    loading,
    saving,
    isRefreshing,
    gallery,
    barbers,
    search,
    activeCategory,
    barberFilter,
    sortOrder,
    showUploadForm,
    editingId,
    previewImage,
    selectedFileName,
    uploadForm,
    categories,
    stats,
    filteredGallery,
    getImageUrl,
    fetchGallery,
    fetchBarbers,
    onFileSelected,
    openUploadForm,
    openEditForm,
    onSaveImage,
    deleteImage,
    resetFilters
  };
}
