import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useServiceForm() {
  const route = useRoute();
  const router = useRouter();
  const toast = useToast();

  const isEditing = computed(() => route.path.includes('/edit'));
  const serviceId = computed(() => route.params.id);
  const portal = computed(() => route.path.startsWith('/barber') ? 'barber' : 'admin');

  const loadingData = ref(false);
  const submitting = ref(false);
  const serviceCategories = ref([]);

  const form = ref({
    name: '',
    description: '',
    price: '',
    duration_minutes: 30,
    category_id: null,
    is_available: true
  });

  const images = ref({
    file1: null, url1: '', remove1: false,
    file2: null, url2: '', remove2: false,
    file3: null, url3: '', remove3: false,
  });

  const activePreviewIndex = ref(0);

  const previewImages = computed(() => {
    const imgs = [];
    if (images.value.url1) imgs.push(images.value.url1);
    if (images.value.url2) imgs.push(images.value.url2);
    if (images.value.url3) imgs.push(images.value.url3);
    return imgs;
  });

  const hasAnyImage = computed(() => previewImages.value.length > 0);

  const selectedCategoryName = computed(() => {
    if (!form.value.category_id) return 'General';
    const cat = serviceCategories.value.find(c => String(c.id) === String(form.value.category_id));
    return cat ? cat.name : 'General';
  });

  function handleFileChange(event, num) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.match('image.*')) {
      toast.error('Please upload an image file (JPG, PNG)');
      return;
    }
    
    images.value[`file${num}`] = file;
    images.value[`url${num}`] = URL.createObjectURL(file);
    images.value[`remove${num}`] = false;
    activePreviewIndex.value = 0;
  }

  function removeImage(num) {
    images.value[`file${num}`] = null;
    images.value[`url${num}`] = '';
    images.value[`remove${num}`] = true;
    activePreviewIndex.value = 0;
  }

  async function loadData() {
    loadingData.value = true;
    try {
      try {
        const catRes = await client.get(`/v1/${portal.value}/service-categories`);
        serviceCategories.value = catRes.data.data || [];
      } catch (e) {
        // If category fails, it's fine. Not all portals might have categories endpoint, though both admin and barber likely do, or maybe barber doesn't.
        // Wait, old barberApi didn't have serviceCategories. But maybe the generic `/v1/barber/service-categories` exists. If not, we just ignore.
      }

      if (isEditing.value) {
        const res = await client.get(`/v1/${portal.value}/services`);
        const allServices = res.data.data || [];
        const service = allServices.find(s => String(s.id) === String(serviceId.value));

        if (service) {
          form.value = {
            name: service.name || '',
            description: service.description || '',
            price: service.price || '',
            duration_minutes: service.duration_minutes || 30,
            category_id: service.category_id || null,
            is_available: service.is_available !== undefined ? Boolean(Number(service.is_available)) : true
          };
          
          if (service.image) images.value.url1 = service.image;
          if (service.image2) images.value.url2 = service.image2;
          if (service.image3) images.value.url3 = service.image3;
        } else {
          toast.error('Service not found');
          router.push(`/${portal.value}/services`);
        }
      }
    } catch (error) {
      toast.error('Failed to load data');
    } finally {
      loadingData.value = false;
    }
  }

  async function submitForm() {
    if (submitting.value || loadingData.value) return;

    if (!form.value.name || !form.value.price || form.value.price <= 0) {
      toast.error('Please provide a name and valid price');
      return;
    }

    submitting.value = true;
    try {
      const payload = new FormData();
      payload.append('name', form.value.name);
      payload.append('description', form.value.description || '');
      payload.append('price', form.value.price);
      payload.append('duration_minutes', form.value.duration_minutes || 30);
      
      if (form.value.category_id) {
        payload.append('category_id', form.value.category_id);
      }
      
      payload.append('is_available', form.value.is_available ? 1 : 0);
      
      if (images.value.file1) payload.append('image1', images.value.file1);
      if (images.value.file2) payload.append('image2', images.value.file2);
      if (images.value.file3) payload.append('image3', images.value.file3);
      
      if (images.value.remove1) payload.append('remove_image1', 'true');
      if (images.value.remove2) payload.append('remove_image2', 'true');
      if (images.value.remove3) payload.append('remove_image3', 'true');

      if (isEditing.value) {
        // use POST with _method=PUT to support file uploads in Laravel
        // wait, the old API used `client.post(\`/admin/services/\${id}\`, data)` for admin 
        // and `client.put(\`/barber/services/\${id}\`, data)` for barber.
        // Actually, for file uploads, PUT doesn't work well with FormData in Laravel unless you do POST with _method=PUT.
        // We'll use POST and append _method=PUT.
        payload.append('_method', 'PUT');
        await client.post(`/v1/${portal.value}/services/${serviceId.value}`, payload, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Service updated successfully!');
      } else {
        await client.post(`/v1/${portal.value}/services`, payload, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        toast.success('Service created successfully!');
      }
      router.push(`/${portal.value}/services`);
    } catch (error) {
      toast.error(error.response?.data?.error || `Failed to ${isEditing.value ? 'update' : 'create'} service`);
    } finally {
      submitting.value = false;
    }
  }

  return {
    form,
    images,
    previewImages,
    hasAnyImage,
    activePreviewIndex,
    serviceCategories,
    selectedCategoryName,
    loadingData,
    submitting,
    isEditing,
    portal,
    handleFileChange,
    removeImage,
    loadData,
    submitForm
  };
}
