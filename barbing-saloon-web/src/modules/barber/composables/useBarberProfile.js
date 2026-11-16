import { ref, reactive, onMounted } from 'vue';
import api from '@/shared/api/client';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import { useToast } from '@/core/composables/useToast';
import { getStorageUrl } from '@/core/utils/url';

export function useBarberProfile() {
  const authStore = useAuthStore();
  const toast = useToast();

  const loading = ref(true);
  const saving = ref(false);
  const avatarFile = ref(null);
  const coverFile = ref(null);

  const form = reactive({
    name: '',
    email: '',
    phone: '',
    bio: '',
    instagram_url: '',
    profile_image: null,
    cover_image: null,
    specialties: [],
  });

  const allStyles = [
    'Classic Fade', 'High Fade', 'Taper Fade', 'Textured Crop',
    'Men Executive Cut', 'Hair Styling', 'Hair Color',
    'Full Beard Trim', 'Beard Detail', 'Beard Conditioning',
    'Hot Towel Shave', 'Scalp Treatment', 'Shampoo & Conditioning',
    'Kids Haircut', 'Kids Super Fade', 'Senior Cut',
    'Women Pixie Cut', 'Wedding Special', 'Home VIP Service',
    'Accessible Care Cut',
  ];

  function toggleSpecialty(style) {
    const idx = form.specialties.indexOf(style);
    if (idx > -1) {
      form.specialties.splice(idx, 1);
    } else {
      form.specialties.push(style);
    }
  }

  function addSpecialty(style) {
    if (!form.specialties.includes(style)) {
      form.specialties.push(style);
    }
  }

  function onAvatarSelected(file) {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      toast.error('Profile picture must be less than 5MB');
      return;
    }
    avatarFile.value = file;
    form.profile_image = URL.createObjectURL(file);
  }

  function onCoverSelected(file) {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
      toast.error('Cover picture must be less than 5MB');
      return;
    }
    coverFile.value = file;
    form.cover_image = URL.createObjectURL(file);
  }

  async function loadProfile() {
    loading.value = true;
    try {
      const res = await api.get('/barber/account');
      const data = res.data?.data || {};
      const user = data.user || authStore.user || {};
      const barber = data.barber || {};

      form.name = user.name || barber.name || '';
      form.email = user.email || barber.email || '';
      form.phone = user.phone || barber.phone || '';
      form.bio = barber.bio || user.bio || '';
      form.instagram_url = barber.instagram_url || '';

      const specs = barber.specialties;
      if (Array.isArray(specs) && specs.length > 0) {
        form.specialties = [...specs];
      } else {
        form.specialties = [
          'Classic Fade', 'High Fade', 'Taper Fade',
          'Men Executive Cut', 'Full Beard Trim', 'Beard Detail',
        ];
      }

      if (user.avatar_url || barber.avatar_url) {
        form.profile_image = user.avatar_url || barber.avatar_url;
      } else if (user.avatar || barber.avatar) {
        const raw = user.avatar || barber.avatar;
        form.profile_image = raw.startsWith('http') ? raw : getStorageUrl(raw);
      }

      if (user.cover_image_url || barber.cover_image_url) {
        form.cover_image = user.cover_image_url || barber.cover_image_url;
      } else if (user.cover_image || barber.cover_image) {
        const rawCover = user.cover_image || barber.cover_image;
        form.cover_image = rawCover.startsWith('http') ? rawCover : getStorageUrl(rawCover);
      }
    } catch {
      form.name = authStore.user?.name || 'Master Barber';
      form.email = authStore.user?.email || '';
      form.specialties = ['Classic Fade', 'Taper Fade', 'Full Beard Trim'];
    } finally {
      loading.value = false;
    }
  }

  async function saveProfile() {
    saving.value = true;
    try {
      const payload = new FormData();
      payload.append('name', form.name);
      payload.append('email', form.email);
      payload.append('phone', form.phone || '');
      payload.append('bio', form.bio || '');
      payload.append('instagram_url', form.instagram_url || '');

      form.specialties.forEach((spec, idx) => {
        payload.append(`specialties[${idx}]`, spec);
      });

      if (avatarFile.value) {
        payload.append('avatar', avatarFile.value);
      }

      if (coverFile.value) {
        payload.append('cover_image', coverFile.value);
      }

      const res = await api.post('/barber/account', payload, {
        params: { _method: 'PATCH' },
      });

      const updatedBarber = res.data?.data?.barber || {};
      const updatedUser = res.data?.data?.user || {};

      const updatedAvatar = updatedUser.avatar_url || updatedBarber.avatar_url || updatedUser.avatar || updatedBarber.avatar;
      if (updatedAvatar) {
        form.profile_image = updatedAvatar.startsWith('http') ? updatedAvatar : getStorageUrl(updatedAvatar);
      }

      if (updatedUser.cover_image_url || updatedBarber.cover_image_url || updatedUser.cover_image || updatedBarber.cover_image) {
        const updatedCover = updatedUser.cover_image_url || updatedBarber.cover_image_url || updatedUser.cover_image || updatedBarber.cover_image;
        form.cover_image = updatedCover.startsWith('http') ? updatedCover : getStorageUrl(updatedCover);
      }

      if (typeof authStore.fetchUser === 'function') {
        await authStore.fetchUser();
      }

      toast.success('Profile details saved successfully!');
    } catch (e) {
      const msg = e.response?.data?.message || 'Failed to save profile';
      toast.error(msg);
    } finally {
      saving.value = false;
    }
  }

  onMounted(() => {
    loadProfile();
  });

  return {
    loading,
    saving,
    form,
    allStyles,
    toggleSpecialty,
    addSpecialty,
    onAvatarSelected,
    onCoverSelected,
    loadProfile,
    saveProfile,
  };
}
