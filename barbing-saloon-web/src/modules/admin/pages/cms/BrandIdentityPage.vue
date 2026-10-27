<template>
  <component :is="layout">
    <div class="mx-auto max-w-6xl space-y-6 pb-12">
      <!-- Error Banner -->
      <div
        v-if="error"
        class="flex items-center justify-between rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-xs text-rose-400"
      >
        <span>{{ error }}</span>
        <button
          type="button"
          @click="load"
          class="rounded-lg bg-rose-500/20 px-3 py-1 font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors"
        >
          Retry
        </button>
      </div>

      <!-- Preloader Skeleton -->
      <BrandIdentitySkeleton v-if="loading" />

      <!-- Main Configurator -->
      <template v-else>
        <!-- Luxury Brand Header -->
        <BrandIdentityHeader
          :full-name="fullName"
          :has-changes="hasChanges"
          :loading="loading"
          :saving="saving"
          @save="save"
          @refresh="load"
          @reset="reset"
        />

        <!-- Two Column Studio Grid -->
        <div class="grid gap-6 lg:grid-cols-[1fr_0.85fr]">
          <!-- Left: Parameters Form -->
          <BrandIdentityForm :form="form" />

          <!-- Right: Live Visual Preview -->
          <BrandIdentityPreview :form="form" />
        </div>

        <!-- Sticky Bottom Save Action on Mobile -->
        <div v-if="hasChanges" class="flex justify-end pt-2 sm:hidden">
          <button
            type="button"
            @click="save"
            :disabled="saving"
            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 py-3 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all"
          >
            <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
            <span>{{ saving ? 'Saving Changes...' : 'Save Branding Changes' }}</span>
          </button>
        </div>
      </template>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import BrandIdentityHeader from '../../components/brand-identity/BrandIdentityHeader.vue';
import BrandIdentityForm from '../../components/brand-identity/BrandIdentityForm.vue';
import BrandIdentityPreview from '../../components/brand-identity/BrandIdentityPreview.vue';
import BrandIdentitySkeleton from '../../components/brand-identity/BrandIdentitySkeleton.vue';
import { useAdminBrandIdentity } from '../../composables/useAdminBrandIdentity';

const {
  form,
  loading,
  saving,
  error,
  hasChanges,
  fullName,
  isAdmin,
  load,
  save,
  reset
} = useAdminBrandIdentity();

const layout = computed(() => (isAdmin.value ? AdminLayout : SuperAdminLayout));
</script>
