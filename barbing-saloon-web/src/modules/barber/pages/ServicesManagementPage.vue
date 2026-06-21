<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Manage</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Your <span class="text-gold">Services</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Add, edit, or remove the services you offer. Customers see these on the public page.
            </p>
          </div>
          <button
            @click="openForm()"
            class="flex items-center gap-2 shrink-0 rounded-xl bg-gradient-to-r from-gold to-gold-dark px-5 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]"
          >
            <PlusIcon class="h-5 w-5" />
            Add Service
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gold border-t-transparent"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="services.length === 0" class="rounded-2xl border border-theme-border bg-theme-surface/80 py-20 text-center">
        <ScissorsIcon class="mx-auto h-12 w-12 text-ivory/20" />
        <h3 class="mt-4 font-display text-xl text-theme-text">No services yet</h3>
        <p class="mt-2 text-sm text-ivory/50">Add your first service to start getting bookings.</p>
      </div>

      <!-- Services Table/Grid -->
      <div v-else class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="border-b border-theme-border">
                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-ivory/40">Service</th>
                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-ivory/40">Price (₦)</th>
                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-ivory/40">Duration</th>
                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-ivory/40">Status</th>
                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-widest text-ivory/40 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="service in services"
                :key="service.id"
                class="border-b border-theme-border transition-colors hover:bg-theme-surface/50"
              >
                <td class="px-6 py-4">
                  <p class="font-semibold text-theme-text">{{ service.name }}</p>
                  <p class="mt-0.5 text-xs text-ivory/40 line-clamp-1">{{ service.description || 'No description' }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="font-bold text-gold text-lg">₦{{ Number(service.price).toLocaleString() }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-theme-muted">{{ service.duration_minutes }} min</td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                    :class="service.is_available == 1 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'"
                  >
                    <span class="h-1.5 w-1.5 rounded-full" :class="service.is_available == 1 ? 'bg-emerald-400' : 'bg-red-400'"></span>
                    {{ service.is_available == 1 ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="openForm(service)" class="rounded-lg p-2 text-ivory/40 hover:bg-gold/10 hover:text-gold transition-colors" title="Edit">
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button @click="deleteService(service.id)" class="rounded-lg p-2 text-ivory/40 hover:bg-red-500/10 hover:text-red-400 transition-colors" title="Delete">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Form -->
      <Teleport to="body">
        <div v-if="showForm" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" @click.self="showForm = false">
          <div class="w-full max-w-lg rounded-2xl border border-gold/20 bg-theme-surface p-8 shadow-2xl animate-fade-in">
            <h2 class="font-display text-2xl text-theme-text">{{ editingId ? 'Edit Service' : 'New Service' }}</h2>
            <form class="mt-6 space-y-5" @submit.prevent="saveService">
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Service Name</label>
                <input v-model="form.name" type="text" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50" placeholder="e.g. Classic Fade" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Price (₦)</label>
                  <input v-model="form.price" type="number" min="0" step="100" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50" placeholder="5000" />
                </div>
                <div class="space-y-2">
                  <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Duration (min)</label>
                  <input v-model="form.duration_minutes" type="number" min="5" step="5" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50" placeholder="30" />
                </div>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Description</label>
                <textarea v-model="form.description" rows="3" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50 resize-none" placeholder="Briefly describe this service..."></textarea>
              </div>
              <div class="flex items-center gap-3">
                <input v-model="form.is_available" type="checkbox" id="available" class="h-4 w-4 rounded accent-gold" />
                <label for="available" class="text-sm text-ivory/70">Available for booking</label>
              </div>
              <div class="flex gap-3 pt-2">
                <button type="submit" :disabled="saving" class="flex-1 rounded-xl bg-gradient-to-r from-gold to-gold-dark py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-50">
                  {{ saving ? 'Saving...' : (editingId ? 'Update Service' : 'Create Service') }}
                </button>
                <button type="button" @click="showForm = false" class="rounded-xl border border-theme-border px-6 py-3 text-sm font-medium text-theme-muted hover:bg-white/5 transition-colors">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from 'vue-toastification';
import { useConfirm } from '../../../core/composables/useConfirm';
import { PlusIcon, PencilIcon, TrashIcon, ScissorsIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref(null);
const services = ref([]);

const form = reactive({
  name: '',
  price: '',
  duration_minutes: 30,
  description: '',
  is_available: true,
});

function resetForm() {
  form.name = '';
  form.price = '';
  form.duration_minutes = 30;
  form.description = '';
  form.is_available = true;
  editingId.value = null;
}

function openForm(service = null) {
  resetForm();
  if (service) {
    editingId.value = service.id;
    form.name = service.name;
    form.price = service.price;
    form.duration_minutes = service.duration_minutes;
    form.description = service.description || '';
    form.is_available = service.is_available == 1;
  }
  showForm.value = true;
}

async function fetchServices() {
  try {
    const res = await barberApi.getServices();
    services.value = res.data.data || [];
  } catch (err) {
    toast.error('Failed to load services');
  } finally {
    loading.value = false;
  }
}

async function saveService() {
  saving.value = true;
  try {
    if (editingId.value) {
      await barberApi.updateService(editingId.value, { ...form });
      toast.success('Service updated');
    } else {
      await barberApi.createService({ ...form });
      toast.success('Service created');
    }
    showForm.value = false;
    await fetchServices();
  } catch (err) {
    toast.error('Failed to save service');
  } finally {
    saving.value = false;
  }
}

async function deleteService(id) {
  const ok = await confirm({ title: 'Delete Service', message: 'Are you sure you want to delete this service? Customers will no longer be able to book it.', confirmText: 'Delete' });
  if (!ok) return;
  try {
    await barberApi.deleteService(id);
    services.value = services.value.filter(s => s.id !== id);
    toast.success('Service deleted');
  } catch (err) {
    toast.error('Failed to delete service');
  }
}

onMounted(fetchServices);
</script>
