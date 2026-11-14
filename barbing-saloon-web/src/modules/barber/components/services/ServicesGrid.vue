<template>
  <div>
    <!-- Empty State -->
    <div
      v-if="filteredServices.length === 0"
      class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-12 text-center"
    >
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] mb-4">
        <ScissorsIcon class="h-7 w-7 text-slate-500" />
      </div>
      <h3 class="text-base font-display font-bold text-slate-900 dark:text-white mb-1">No Services In This Filter</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-6">
        No services found matching the selected category or source filter.
      </p>
      <router-link
        to="/barber/services/new"
        class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all"
      >
        <PlusIcon class="h-4 w-4" />
        Add Custom Service
      </router-link>
    </div>

    <!-- Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="service in filteredServices"
        :key="service.id"
        class="group rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] hover:border-gold/40 transition-all duration-300 flex flex-col justify-between shadow-sm dark:shadow-xl hover:shadow-lg dark:hover:shadow-[0_16px_45px_rgba(0,0,0,0.6)] hover:-translate-y-1 relative overflow-hidden"
        data-test="service-card"
      >
        <!-- Top Accent Line -->
        <div 
          class="absolute top-0 left-0 right-0 h-1 z-30 transition-opacity"
          :class="[
            service.barber_id && service.approval_status === 'pending'
              ? 'bg-amber-400 opacity-100'
              : (service.barber_id && service.approval_status === 'rejected'
                  ? 'bg-rose-500 opacity-100'
                  : 'bg-gradient-to-r from-gold via-amber-400 to-amber-200 opacity-0 group-hover:opacity-100')
          ]"
        ></div>

        <!-- Media Cover / Image Section -->
        <div class="relative w-full h-48 sm:h-52 bg-slate-100 dark:bg-[#0B0F13] overflow-hidden shrink-0">
          <img 
            v-if="getServiceImage(service)"
            :src="getServiceImage(service)" 
            :alt="service.name" 
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
            loading="lazy"
            data-test="service-image"
            @error="handleImageError(service.id)" 
          />
          <!-- Luxury Fallback Placeholder if no image or image failed to load -->
          <div v-else class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-[#181F27] dark:via-[#12161C] dark:to-[#0B0F13] relative overflow-hidden" data-test="service-placeholder">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,184,0,0.08),transparent_70%)]"></div>
            <div class="h-14 w-14 rounded-2xl bg-white/80 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] flex items-center justify-center shadow-inner group-hover:border-gold/30 group-hover:scale-110 transition-all duration-500">
              <ScissorsIcon class="h-7 w-7 text-gold/60 group-hover:text-gold transition-colors" />
            </div>
            <span class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-mono mt-3">CandyCutz Signature</span>
          </div>

          <!-- Gradient Scrim Vignettes for readability -->
          <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/80 via-black/40 to-transparent pointer-events-none z-10"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-white dark:from-[#12161C] via-white/40 dark:via-[#12161C]/40 to-transparent pointer-events-none z-10"></div>

          <!-- Top-Left Badges floating on image -->
          <div class="absolute top-3 left-3 z-20 flex flex-wrap items-center gap-1.5">
            <span class="inline-flex items-center gap-1 rounded-lg bg-black/60 border border-white/10 backdrop-blur-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-gold shadow-md">
              <TagIcon class="h-3 w-3 text-gold" />
              {{ service.category_name || service.category?.name || 'General' }}
            </span>

            <span 
              v-if="service.is_home_service_eligible" 
              class="inline-flex items-center gap-1 rounded-lg bg-emerald-950/70 border border-emerald-500/30 backdrop-blur-md px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-300 shadow-md"
              title="Home service visit eligible"
            >
              <HomeIcon class="h-3 w-3" />
              House Call
            </span>
          </div>

          <!-- Top-Right Floating Price Badge -->
          <div class="absolute top-3 right-3 z-20">
            <div class="rounded-xl bg-black/70 border border-gold/30 backdrop-blur-md px-3 py-1 shadow-lg flex items-baseline gap-0.5">
              <span class="text-xs font-mono font-medium text-gold/70">₦</span>
              <span class="text-base font-bold font-mono text-gold">{{ Number(service.price || 0).toLocaleString() }}</span>
            </div>
          </div>
        </div>

        <!-- Card Body Content -->
        <div class="p-5 flex flex-col flex-1 justify-between">
          <div>
            <!-- Origin & Duration Row -->
            <div class="flex items-center justify-between gap-2 mb-3">
              <!-- Origin Badge -->
              <span 
                v-if="!service.barber_id"
                class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400"
                title="Added by Salon Administrator"
              >
                <BuildingStorefrontIcon class="h-3 w-3" />
                Salon Official
              </span>
              <span 
                v-else
                class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400"
                title="Custom service submitted by you"
              >
                <UserIcon class="h-3 w-3" />
                My Service
              </span>

              <!-- Duration badge -->
              <div class="flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                <ClockIcon class="h-3.5 w-3.5 text-gold/70" />
                <span>{{ service.duration_minutes || 30 }} mins</span>
              </div>
            </div>

            <!-- Service Title -->
            <h3 class="text-lg font-display font-bold text-slate-900 dark:text-white group-hover:text-gold transition-colors mb-1.5 leading-snug">
              {{ service.name }}
            </h3>

            <!-- Description -->
            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
              {{ service.description || 'Master precision haircut styling and grooming treatment.' }}
            </p>

            <!-- Status Indicator Banner -->
            <div 
              v-if="service.barber_id && service.approval_status === 'pending'"
              class="mb-4 px-3 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between text-xs text-amber-800 dark:text-amber-300"
            >
              <span class="flex items-center gap-1.5 font-medium">
                <ClockIcon class="h-4 w-4 text-amber-500 dark:text-amber-400 animate-pulse" />
                Pending Admin Approval
              </span>
              <span class="text-[10px] text-amber-800 dark:text-amber-400/80 bg-amber-500/20 px-2 py-0.5 rounded-full font-bold">Reviewing</span>
            </div>

            <div 
              v-else-if="service.barber_id && service.approval_status === 'rejected'"
              class="mb-4 px-3 py-2 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-between text-xs text-rose-700 dark:text-rose-300"
            >
              <span class="flex items-center gap-1.5 font-medium">
                <XCircleIcon class="h-4 w-4 text-rose-500 dark:text-rose-400" />
                Rejected by Admin
              </span>
              <span class="text-[10px] text-rose-700 dark:text-rose-400/80 bg-rose-500/20 px-2 py-0.5 rounded-full font-bold">Rejected</span>
            </div>

            <div 
              v-else-if="service.barber_id && service.approval_status === 'approved'"
              class="mb-4 px-3 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-300"
            >
              <span class="flex items-center gap-1.5 font-medium">
                <CheckCircleIcon class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                Approved by Admin
              </span>
              <span class="text-[10px] text-emerald-800 dark:text-emerald-400/80 bg-emerald-500/20 px-2 py-0.5 rounded-full font-bold">Active</span>
            </div>

            <div 
              v-else
              class="mb-4 px-3 py-2 rounded-xl bg-slate-100 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] flex items-center justify-between text-xs text-slate-700 dark:text-slate-300"
            >
              <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                <ShieldCheckIcon class="h-4 w-4 text-emerald-500 dark:text-emerald-400" />
                Admin Master Menu
              </span>
              <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded-full">Available</span>
            </div>
          </div>

          <!-- Footer Row -->
          <div class="pt-4 border-t border-slate-200 dark:border-white/[0.06] flex items-center justify-between">
            <div class="flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 font-mono">
              <span>Duration:</span>
              <span class="text-slate-900 dark:text-white font-medium">{{ service.duration_minutes || 30 }}m</span>
            </div>

            <!-- Actions -->
            <div v-if="service.barber_id" class="flex items-center gap-2">
              <router-link
                :to="`/barber/services/${service.id}/edit`"
                class="p-2 rounded-xl bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.06] transition-colors"
                title="Edit Custom Service"
                data-test="edit-service-btn"
              >
                <PencilSquareIcon class="h-4 w-4" />
              </router-link>

              <button
                @click="$emit('delete', service)"
                class="p-2 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-500/20 transition-colors"
                title="Delete Custom Service"
                data-test="delete-service-btn"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>

            <div v-else class="text-[11px] text-slate-600 dark:text-slate-400 font-medium flex items-center gap-1">
              <BuildingStorefrontIcon class="h-3.5 w-3.5 text-emerald-500 dark:text-emerald-400/70" />
              <span>Salon Managed</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import {
  ScissorsIcon,
  PlusIcon,
  TagIcon,
  ClockIcon,
  PencilSquareIcon,
  TrashIcon,
  BuildingStorefrontIcon,
  UserIcon,
  ShieldCheckIcon,
  CheckCircleIcon,
  XCircleIcon,
  HomeIcon,
} from '@heroicons/vue/24/outline';
import { getStorageUrl } from '@/core/utils/url';

defineProps({
  filteredServices: {
    type: Array,
    required: true,
  },
});

defineEmits(['delete']);

const imageErrors = ref({});

function handleImageError(id) {
  if (id) {
    imageErrors.value[id] = true;
  }
}

function getServiceImage(service) {
  if (!service) return null;

  if (imageErrors.value[service.id]) {
    return null;
  }

  const raw = service.image_url || service.image || service.images?.[0];
  if (raw) {
    return getStorageUrl(raw);
  }

  // Curated category defaults so every service displays an appropriate professional photo
  const cat = (service.category_name || service.category?.name || service.category || '').toLowerCase();
  if (cat.includes('beard')) {
    return '/images/gallery/beard-1.png';
  }
  return '/images/services/service-1.png';
}
</script>
