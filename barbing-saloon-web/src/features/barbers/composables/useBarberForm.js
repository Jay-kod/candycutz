import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';

export function useBarberForm(barberId) {
  const router = useRouter();
  const toast = useToast();
  const isEditing = computed(() => !!barberId);
  const loadingData = ref(false);
  const submitting = ref(false);

  const form = ref({
    name: '',
    email: '',
    password: '',
    experience_years: '',
    specialties: '',
    bio: '',
    status: 'active',
    avatar: ''
  });

  const parsedSpecialties = computed(() => {
    if (!form.value.specialties) return [];
    return form.value.specialties.split(',').map(s => s.trim()).filter(Boolean);
  });

  async function loadBarberData() {
    if (!isEditing.value) {
      form.value.status = 'pending_approval';
      return;
    }
    
    loadingData.value = true;
    try {
      const res = await api.get('/v1/admin/barbers');
      const barbers = res.data.data || [];
      const barber = barbers.find(b => String(b.id) === String(barberId));
      
      if (barber) {
        form.value = {
          name: barber.name || '',
          email: barber.email || '',
          password: '',
          experience_years: barber.experience_years || 0,
          specialties: (barber.specialties || []).join(', '),
          bio: barber.bio || '',
          status: barber.status || 'active',
          avatar: barber.avatar || ''
        };
      } else {
        toast.error('Barber not found');
        router.push('/admin/barbers');
      }
    } catch (error) {
      toast.error('Failed to load barber details');
    } finally {
      loadingData.value = false;
    }
  }

  async function submitForm() {
    if (submitting.value || loadingData.value) return;
    
    submitting.value = true;
    try {
      if (isEditing.value) {
        await api.put(`/v1/admin/barbers/${barberId}`, form.value);
        toast.success('Barber profile updated successfully!');
      } else {
        await api.post('/v1/admin/barbers', form.value);
        toast.success('Barber added to team successfully!');
      }
      router.push('/admin/barbers');
    } catch (error) {
      if (error.response?.status === 409) {
        toast.error('A user with this email already exists');
      } else {
        toast.error(error.response?.data?.error || `Failed to ${isEditing.value ? 'update' : 'create'} barber`);
      }
    } finally {
      submitting.value = false;
    }
  }

  return {
    form,
    isEditing,
    loadingData,
    submitting,
    parsedSpecialties,
    loadBarberData,
    submitForm
  };
}
