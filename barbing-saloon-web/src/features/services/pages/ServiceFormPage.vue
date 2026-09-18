<template>
  <component :is="layoutComponent">
    <section class="animate-fade-in pb-10">
      <!-- Premium Header -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6 backdrop-blur-3xl mb-8">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-emerald-500/8 blur-[80px]"></div>

        <div class="relative z-10 flex items-center gap-6">
          <router-link :to="`/${portal}/services`" class="group flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition-all hover:shadow-[0_0_20px_rgba(255,103,0,0.15)] hover:border-admin/30">
            <ArrowLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
          </router-link>
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold mb-1">{{ isEditing ? 'Edit Service' : 'New Service' }}</p>
            <h1 class="font-display text-3xl text-white drop-shadow-md">
              {{ isEditing ? 'Update Service Details' : 'Add a New Service' }}
            </h1>
          </div>
        </div>

        <div class="relative z-10 hidden md:block">
          <button @click="submitForm" :disabled="submitting || loadingData" class="flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 py-3.5 px-8 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.4)] disabled:opacity-50 active:scale-[0.98]">
            <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
            <CheckIcon v-else class="h-5 w-5" />
            {{ submitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Service') }}
          </button>
        </div>
      </div>

      <div v-if="loadingData" class="flex justify-center py-32">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-admin/30 border-t-admin"></div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 items-start">
        
        <!-- Left: Live Preview Card -->
        <ServiceLivePreview
          :form="form"
          :hasAnyImage="hasAnyImage"
          :previewImages="previewImages"
          :activePreviewIndex="activePreviewIndex"
          :selectedCategoryName="selectedCategoryName"
          @update:activePreviewIndex="activePreviewIndex = $event"
        />

        <!-- Right: Form Sections -->
        <div class="space-y-8">
          <ServiceDetailsForm
            :form="form"
            :serviceCategories="serviceCategories"
            :images="images"
            @fileChange="handleFileChange"
            @removeImage="removeImage"
          />

          <!-- Bottom Action Bar (Mobile) -->
          <div class="md:hidden flex gap-4 pt-4">
            <router-link :to="`/${portal}/services`" class="flex-[1] flex items-center justify-center py-4 px-4 rounded-2xl border border-white/10 text-sm font-bold text-white/60 hover:text-white hover:bg-white/5 transition-all">
              Cancel
            </router-link>
            <button @click="submitForm" :disabled="submitting" class="flex-[2] flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 py-4 px-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.4)] disabled:opacity-50 active:scale-[0.98]">
              <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
              <CheckIcon v-else class="h-5 w-5" />
              {{ submitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Service') }}
            </button>
          </div>
        </div>
      </div>
    </section>
  </component>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout from '@/portals/Admin/layouts/Adminlayout.vue';
import BarberLayout from '@/portals/Barber/layouts/BarberLayout.vue';
import { ArrowLeftIcon, CheckIcon } from '@heroicons/vue/24/outline';
import ServiceLivePreview from '../components/ServiceLivePreview.vue';
import ServiceDetailsForm from '../components/ServiceDetailsForm.vue';
import { useServiceForm } from '../composables/useServiceForm';

const {
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
} = useServiceForm();

const layoutComponent = computed(() => {
  return portal.value === 'admin' ? AdminLayout : BarberLayout;
});

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
