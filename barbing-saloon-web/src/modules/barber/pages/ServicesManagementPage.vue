<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-16 pt-4 px-4 sm:px-6 lg:px-8 space-y-6">
      <ServicesSkeleton v-if="loading" />

      <template v-else>
        <div class="flex justify-end">
          <router-link to="/barber/service-categories" class="rounded-xl border border-theme-border px-4 py-2 text-sm font-bold text-theme-text transition hover:bg-theme-surface">
            Request a category
          </router-link>
        </div>
        <ServicesHeader
          :loading="loading"
          :total-count="services.length"
          :admin-count="adminServicesCount"
          :mine-count="myServicesCount"
          :pending-count="pendingServicesCount"
        />

        <ServicesTabs
          :categories="categories"
          :active-category="activeCategory"
          :source-filter="sourceFilter"
          :total-count="services.length"
          :admin-count="adminServicesCount"
          :mine-count="myServicesCount"
          :pending-count="pendingServicesCount"
          @update:activeCategory="activeCategory = $event"
          @update:sourceFilter="sourceFilter = $event"
        />

        <ServicesGrid
          :filtered-services="filteredServices"
          @delete="deleteService"
        />
      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useBarberServices } from '../composables/useBarberServices';
import ServicesHeader from '../components/services/ServicesHeader.vue';
import ServicesTabs from '../components/services/ServicesTabs.vue';
import ServicesGrid from '../components/services/ServicesGrid.vue';
import ServicesSkeleton from '../components/services/ServicesSkeleton.vue';

const {
  loading,
  services,
  activeCategory,
  sourceFilter,
  categories,
  filteredServices,
  adminServicesCount,
  myServicesCount,
  pendingServicesCount,
  deleteService,
} = useBarberServices();
</script>
