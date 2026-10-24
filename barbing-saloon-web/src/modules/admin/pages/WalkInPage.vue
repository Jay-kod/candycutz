<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preload -->
      <WalkInSkeleton v-if="isLoading" />

      <!-- Interactive Content -->
      <div v-else class="space-y-8 animate-fade-in">
        <!-- Luxury Header -->
        <WalkInHeader />

        <!-- 2-Column Responsive Intake Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_390px] gap-8 items-start">
          <!-- Left Column: Form Intake Flow -->
          <div class="space-y-6">
            <!-- 1. Client Identity & Intake Details -->
            <WalkInClientSection v-model="form" />

            <!-- 2. Visual Service Cards Picker -->
            <WalkInServicePicker
              v-model="form.service_id"
              :services="services"
            />

            <!-- 3. Stylist Chair Allocation -->
            <WalkInBarberPicker
              v-model="form.barber_id"
              :barbers="barbers"
            />
          </div>

          <!-- Right Column: Live Luxury Intake Slip & Today's Ledger -->
          <div class="lg:sticky lg:top-6 space-y-6">
            <!-- Digital Intake Slip -->
            <WalkInTicketPreview
              :form="form"
              :service="selectedService"
              :barber="selectedBarber"
              :submitting="isSubmitting"
              :is-valid="isFormValid"
              @submit="handleSubmit"
              @reset="handleReset"
            />

            <!-- Today's Walk-In Ledger -->
            <WalkInRecentLedger :walk-ins="recentWalkIns" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import WalkInHeader from '@/features/walkin/components/WalkInHeader.vue';
import WalkInSkeleton from '@/features/walkin/components/WalkInSkeleton.vue';
import WalkInClientSection from '@/features/walkin/components/WalkInClientSection.vue';
import WalkInServicePicker from '@/features/walkin/components/WalkInServicePicker.vue';
import WalkInBarberPicker from '@/features/walkin/components/WalkInBarberPicker.vue';
import WalkInTicketPreview from '@/features/walkin/components/WalkInTicketPreview.vue';
import WalkInRecentLedger from '@/features/walkin/components/WalkInRecentLedger.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '../../../core/composables/useToast';

const toast = useToast();

const isLoading = ref(true);
const isSubmitting = ref(false);
const services = ref([]);
const barbers = ref([]);
const recentWalkIns = ref([]);

const getInitialForm = () => {
  const now = new Date();
  return {
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    service_id: '',
    barber_id: '',
    appointment_date: now.toISOString().split('T')[0],
    appointment_time: now.toTimeString().slice(0, 5),
    payment_method: 'cash',
    notes: ''
  };
};

const form = ref(getInitialForm());

const selectedService = computed(() => {
  if (!form.value.service_id) return null;
  return services.value.find(s => s.id === form.value.service_id) || null;
});

const selectedBarber = computed(() => {
  if (!form.value.barber_id) return null;
  return barbers.value.find(b => b.id === form.value.barber_id) || null;
});

const isFormValid = computed(() => {
  return Boolean(
    form.value.customer_name?.trim() &&
    form.value.customer_phone?.trim() &&
    form.value.service_id
  );
});

async function loadData() {
  isLoading.value = true;
  try {
    const [sRes, bRes, aRes] = await Promise.all([
      adminApi.services(),
      adminApi.barbers(),
      adminApi.appointments()
    ]);

    services.value = sRes.data?.data || [];
    barbers.value = (bRes.data?.data || []).map(b => ({
      id: b.id,
      name: b.name || b.user?.name || 'Master Barber',
      rating: b.rating || 5.0
    }));

    const appts = aRes.data?.data || [];
    const list = Array.isArray(appts) ? appts : (appts.items || []);
    recentWalkIns.value = list
      .filter(a => a.booking_type === 'walk_in' || a.source === 'walk_in' || a.source === 'desk')
      .sort((a, b) => (b.id || 0) - (a.id || 0))
      .slice(0, 5);
  } catch (err) {
    toast.error('Failed to load salon roster and services');
  } finally {
    isLoading.value = false;
  }
}

function handleReset() {
  form.value = getInitialForm();
}

async function handleSubmit() {
  if (!isFormValid.value) {
    toast.error('Please specify client name, phone number, and service');
    return;
  }

  // If no barber explicitly chosen, auto-assign first available
  const payload = {
    ...form.value,
    client_name: form.value.customer_name,
    client_phone: form.value.customer_phone,
    client_email: form.value.customer_email,
    barber_id: form.value.barber_id || (barbers.value[0]?.id ?? 1)
  };

  isSubmitting.value = true;
  try {
    await adminApi.createWalkIn(payload);
    toast.success('Walk-in registered & checked in successfully!');
    handleReset();

    // Refresh today's ledger
    const aRes = await adminApi.appointments();
    const appts = aRes.data?.data || [];
    const list = Array.isArray(appts) ? appts : (appts.items || []);
    recentWalkIns.value = list
      .filter(a => a.booking_type === 'walk_in' || a.source === 'walk_in' || a.source === 'desk')
      .sort((a, b) => (b.id || 0) - (a.id || 0))
      .slice(0, 5);
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.error || 'Failed to check in walk-in client';
    toast.error(msg);
  } finally {
    isSubmitting.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
