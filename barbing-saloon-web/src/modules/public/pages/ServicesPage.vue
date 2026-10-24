<template>
  <PublicLayout>
    <PageHero
      page-key="services"
      eyebrow="Grooming Menu"
      fallback-title="Our Premium Services"
      fallback-subtitle="Explore our full range of grooming services designed to keep you looking sharp and feeling confident."
      title-setting-key="services_title"
      subtitle-setting-key="services_subtitle"
    />
    <section class="mx-auto max-w-7xl px-4 md:px-6 pt-12 pb-24 text-theme-text min-h-[50vh]">

      <!-- Categories Filter Tabs -->
      <div data-reveal class="flex items-center justify-center flex-wrap gap-2.5 sm:gap-3 mb-12">
        <button
          type="button"
          @click="selectedCategory = 'All'"
          class="px-5 py-2.5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 border"
          :class="selectedCategory === 'All'
            ? 'bg-gold text-obsidian border-gold shadow-lg shadow-gold/20 scale-105'
            : 'bg-theme-surface text-theme-muted border-theme-border hover:border-gold/40 hover:text-gold'"
        >
          All ({{ services.length }})
        </button>
        <button
          v-for="cat in categories"
          :key="cat"
          type="button"
          @click="selectedCategory = cat"
          class="px-5 py-2.5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 border"
          :class="selectedCategory === cat
            ? 'bg-gold text-obsidian border-gold shadow-lg shadow-gold/20 scale-105'
            : 'bg-theme-surface text-theme-muted border-theme-border hover:border-gold/40 hover:text-gold'"
        >
          {{ cat }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="n in 6"
          :key="n"
          class="aspect-auto xs:aspect-[0.68] min-h-[380px] xs:min-h-[440px] sm:min-h-[460px] w-full max-w-full rounded-3xl border border-theme-border bg-theme-surface p-5 space-y-4 animate-pulse"
        >
          <div class="h-8 w-2/3 rounded-lg bg-theme-border/50"></div>
          <div class="h-4 w-1/3 rounded bg-theme-border/30"></div>
          <div class="mt-auto space-y-3 pt-56">
            <div class="h-7 w-3/4 rounded-lg bg-theme-border/50"></div>
            <div class="h-4 w-full rounded bg-theme-border/30"></div>
            <div class="h-4 w-4/5 rounded bg-theme-border/30"></div>
            <div class="pt-4 flex justify-between items-center border-t border-theme-border/50">
              <div class="h-6 w-20 rounded bg-theme-border/50"></div>
              <div class="h-10 w-28 rounded-xl bg-theme-border/60"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Services Grid -->
      <div v-else-if="filteredServices.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <article
          v-for="service in filteredServices"
          :key="service.id"
          data-reveal
          class="group relative isolate aspect-auto xs:aspect-[0.68] min-h-[380px] xs:min-h-[440px] sm:min-h-[460px] w-full max-w-full overflow-hidden rounded-[1.75rem] border border-gold/25 bg-[#241407] shadow-xl transition-all duration-300 hover:-translate-y-1.5 hover:border-gold/60 hover:shadow-2xl hover:shadow-gold/10"
        >
          <img
            v-if="service.image || service.image_url"
            :src="getImageUrl(service.image || service.image_url)"
            :alt="service.name"
            class="absolute inset-0 -z-20 h-full w-full object-cover object-[center_60%] transition-transform duration-700 group-hover:scale-105"
          />
          <div v-else class="absolute inset-0 -z-20 flex items-center justify-center bg-gradient-to-br from-[#59300c] via-[#241407] to-[#0c0906]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-28 h-28 text-gold/20">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887m8.416-2.224l-1.536.887M9.384 14.863l-1.536.887m8.416 2.224l-1.536-.887M12 21.75a9.75 9.75 0 100-19.5 9.75 9.75 0 000 19.5z" />
            </svg>
          </div>

          <div class="absolute inset-0 -z-10 bg-gradient-to-b from-[#140b05]/70 via-transparent via-35% to-[#100a05]/80"></div>
          <div class="absolute inset-3 -z-10 rounded-[1.35rem] border border-gold/20"></div>

          <div class="flex h-full flex-col justify-between px-5 pt-5 pb-3 sm:px-6 sm:pt-6 sm:pb-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-gold/70">CandyCutz Service</p>
                <span class="mt-2 inline-flex rounded-full border border-gold/40 bg-black/45 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-gold backdrop-blur">
                  {{ service.category?.name || service.category || 'Barbering' }}
                </span>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span v-if="service.is_home_service_eligible" class="rounded-full border border-emerald-300/40 bg-emerald-950/60 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-200 backdrop-blur">
                  Home service
                </span>
                <span class="flex items-center gap-1.5 rounded-full border border-white/20 bg-black/50 px-3 py-1.5 text-[11px] font-semibold text-white backdrop-blur">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5 text-gold">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ service.duration_minutes || 30 }} min
                </span>
              </div>
            </div>

            <div class="rounded-2xl border border-gold/20 bg-[#160e08]/60 p-4 shadow-2xl backdrop-blur-sm sm:p-5">
              <h2 class="font-display text-2xl font-bold leading-tight text-white group-hover:text-gold transition-colors">
                {{ service.name }}
              </h2>
              <p class="mt-2 text-xs leading-relaxed text-white/70 line-clamp-2">
                {{ service.description || 'Premium grooming treatment tailored with precision and style.' }}
              </p>
              <div class="mt-4 flex items-end justify-between gap-3 border-t border-gold/20 pt-3">
                <div>
                  <span class="block text-[9px] font-bold uppercase tracking-[0.2em] text-gold/60">Price</span>
                  <span class="font-display text-2xl font-bold text-gold">{{ formatNairaAmount(service.price) }}</span>
                </div>
                <RouterLink
                  :to="`/customer/dashboard/book/${service.id}`"
                  class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-light px-4 py-2.5 min-h-[44px] text-xs font-bold text-obsidian shadow-lg transition-all duration-200 hover:scale-[1.03] hover:shadow-gold/30"
                >
                  Book Cut
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                  </svg>
                </RouterLink>
              </div>
            </div>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-20 rounded-3xl border border-theme-border bg-theme-surface">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-14 h-14 mx-auto text-theme-muted mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
        </svg>
        <h3 class="text-xl font-display text-theme-text font-bold">No services found</h3>
        <p class="text-sm text-theme-muted mt-2">There are currently no services listed under {{ selectedCategory }}.</p>
        <button
          type="button"
          @click="selectedCategory = 'All'"
          class="mt-6 inline-flex items-center gap-2 rounded-xl border border-gold/40 bg-gold/10 px-6 py-2.5 text-xs font-bold text-gold hover:bg-gold hover:text-obsidian transition-colors"
        >
          View All Services
        </button>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { formatNairaAmount } from '../../../core/utils';
import { getStorageUrl as getImageUrl } from '@/core/utils/url';

const { init: initScrollReveal } = useScrollReveal();


const services = ref([]);
const selectedCategory = ref('All');
const isLoading = ref(true);

const categories = computed(() => {
  const cats = services.value
    .map(s => s.category?.name || s.category_name || (typeof s.category === 'string' ? s.category : null))
    .filter(Boolean);
  return [...new Set(cats)].sort();
});

const filteredServices = computed(() => {
  if (selectedCategory.value === 'All') return services.value;
  return services.value.filter(s => {
    const cat = s.category?.name || s.category_name || (typeof s.category === 'string' ? s.category : '');
    return cat === selectedCategory.value;
  });
});

const fetchContent = async () => {
  try {
    const servicesRes = await publicApi.services().catch(() => ({ data: { data: [] } }));
    services.value = servicesRes.data?.data || [];
  } catch (error) {
    console.error('Failed to load services page data:', error);
  } finally {
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  }
};

onMounted(() => {
  fetchContent();
});
</script>
