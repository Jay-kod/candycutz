<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-gold/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-emerald-500/8 blur-[80px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.05),transparent_60%)]"></div>

        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-gold/20 text-gold border border-gold/30">
              <SparklesIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-gold/80 font-bold">Catalog</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Service <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-gold-light">Menu</span>
            <span v-if="!loading" class="ml-3 inline-flex items-center justify-center h-9 px-3.5 rounded-full bg-white/5 border border-white/10 text-lg text-white/60 font-normal align-middle">{{ services.length }}</span>
          </h1>
          <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
            Manage your saloon's service offerings, pricing, and durations. These services are visible to your customers during booking.
          </p>
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4 shrink-0 w-full md:w-auto">
          <router-link to="/barber/services/new" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-gold to-gold-dark px-6 py-3.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(212,175,55,0.3)] shrink-0 group">
            <PlusIcon class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" />
            Add Service
          </router-link>
        </div>
      </div>

      <!-- Category Filter Tabs -->
      <div class="flex items-center gap-3 overflow-x-auto pb-1 scrollbar-hide">
        <button
          @click="activeCategory = 'all'"
          class="shrink-0 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-wider border transition-all"
          :class="activeCategory === 'all'
            ? 'bg-gold/20 text-gold border-gold/30 shadow-[0_0_15px_rgba(212,175,55,0.1)]'
            : 'bg-white/[0.02] text-white/40 border-white/[0.05] hover:bg-white/[0.05] hover:text-white/60'"
        >
          All Services
          <span class="ml-1.5 text-[10px] opacity-60">{{ services.length }}</span>
        </button>
        <button
          v-for="cat in categories"
          :key="cat"
          @click="activeCategory = cat"
          class="shrink-0 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-wider border transition-all"
          :class="activeCategory === cat
            ? 'bg-gold/20 text-gold border-gold/30 shadow-[0_0_15px_rgba(212,175,55,0.1)]'
            : 'bg-white/[0.02] text-white/40 border-white/[0.05] hover:bg-white/[0.05] hover:text-white/60'"
        >
          {{ cat }}
          <span class="ml-1.5 text-[10px] opacity-60">{{ services.filter(s => (s.category_name || 'General') === cat).length }}</span>
        </button>
      </div>

      <!-- Services Grid -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">

        <!-- Loading skeleton -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
          <div v-for="i in 6" :key="i" class="rounded-2xl border border-white/[0.02] bg-white/[0.01] p-6 animate-pulse">
            <div class="h-4 w-20 bg-white/[0.03] rounded mb-4"></div>
            <div class="h-6 w-3/4 bg-white/[0.03] rounded mb-3"></div>
            <div class="h-3 w-full bg-white/[0.02] rounded mb-2"></div>
            <div class="h-3 w-5/6 bg-white/[0.02] rounded mb-6"></div>
            <div class="flex justify-between items-center pt-4 border-t border-white/[0.03]">
              <div class="h-6 w-16 bg-white/[0.03] rounded"></div>
              <div class="h-6 w-20 bg-white/[0.02] rounded"></div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="filteredServices.length === 0" class="flex flex-col items-center justify-center py-24 px-6 text-center">
          <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] mb-6">
            <div class="absolute inset-0 rounded-full border border-dashed border-white/10 animate-[spin_10s_linear_infinite]"></div>
            <SparklesIcon class="h-8 w-8 text-white/20" />
          </div>
          <h3 class="text-xl font-bold text-white mb-2">{{ activeCategory !== 'all' ? 'No Services in This Category' : 'No Services Found' }}</h3>
          <p class="text-sm text-white/40 max-w-sm mx-auto mb-6">
            {{ activeCategory !== 'all' ? 'Try selecting a different category or add a new service.' : 'Add your first service to start accepting bookings from customers.' }}
          </p>
          <router-link to="/barber/services/new" class="text-gold hover:text-gold-light text-sm font-semibold transition-colors flex items-center gap-2">
            <PlusIcon class="h-4 w-4" /> Add your first service
          </router-link>
        </div>

        <!-- Service Cards -->
        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <article
              v-for="service in filteredServices"
              :key="service.id"
              class="group relative overflow-hidden rounded-3xl border border-white/[0.05] bg-gradient-to-br from-white/[0.02] to-transparent transition-all duration-300 hover:border-gold/20 hover:from-white/[0.04] hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(0,0,0,0.3)] flex flex-col"
            >
              <!-- Glow accent -->
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-gold to-gold-light opacity-0 group-hover:opacity-100 transition-opacity z-20"></div>

              <!-- Cover Image (if exists) -->
              <div v-if="service.image" class="relative w-full h-40 bg-black overflow-hidden shrink-0">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay z-10"></div>
                <img :src="service.image" :alt="service.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                <div class="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-[#1a1a1a]/50 to-transparent z-10"></div>
                
                <!-- Category badge moved to image overlay -->
                <div class="absolute top-4 left-4 z-20">
                  <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest border border-gold/20 bg-gold/10 text-gold backdrop-blur-md">
                    <TagIcon class="h-3 w-3" />
                    {{ service.category_name || 'General' }}
                  </span>
                </div>
              </div>

              <div class="relative z-20 flex flex-col flex-1 p-6" :class="{'pt-2': service.image}">
                <!-- Header row (no image state) -->
                <div v-if="!service.image" class="flex items-start justify-between gap-4 mb-4">
                  <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest border border-gold/20 bg-gold/10 text-gold">
                    <TagIcon class="h-3 w-3" />
                    {{ service.category_name || 'General' }}
                  </span>

                  <div class="flex items-center gap-1 bg-white/5 rounded-xl p-1 border border-white/5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <router-link :to="`/barber/services/${service.id}/edit`" class="p-1.5 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors" title="Edit Service">
                      <PencilSquareIcon class="h-4 w-4" />
                    </router-link>
                    <button @click="deleteService(service)" class="p-1.5 rounded-lg text-white/40 hover:text-red-400 hover:bg-red-500/20 transition-colors" title="Delete Service">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </div>
                
                <!-- Floating action buttons (with image state) -->
                <div v-if="service.image" class="absolute top-4 right-4 z-30 flex items-center gap-1 bg-black/40 backdrop-blur-md rounded-xl p-1 border border-white/10 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                  <router-link :to="`/barber/services/${service.id}/edit`" class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/20 transition-colors" title="Edit Service">
                    <PencilSquareIcon class="h-4 w-4" />
                  </router-link>
                  <button @click="deleteService(service)" class="p-1.5 rounded-lg text-white/70 hover:text-red-400 hover:bg-red-500/30 transition-colors" title="Delete Service">
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>

                <!-- Name & Description -->
                <h2 class="font-display text-2xl text-white mb-2 group-hover:text-gold-light transition-colors leading-tight drop-shadow-sm">{{ service.name }}</h2>
                <p v-if="service.description" class="text-sm text-white/50 line-clamp-2 mb-auto leading-relaxed">{{ service.description }}</p>
                <p v-else class="text-sm text-white/20 italic mb-auto">No description provided</p>

                <!-- Availability -->
                <div class="mt-4 mb-4">
                  <span
                    class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border"
                    :class="service.is_available
                      ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                      : 'bg-red-500/10 text-red-400 border-red-500/20'"
                  >
                    {{ service.is_available ? 'Available' : 'Unavailable' }}
                  </span>
                </div>

                <!-- Price & Duration -->
                <div class="pt-4 flex items-center justify-between border-t border-white/[0.05]">
                  <div class="flex items-baseline gap-1 text-emerald-400 font-display">
                    <span class="text-xs opacity-60">₦</span>
                    <span class="text-2xl font-bold">{{ Number(service.price).toLocaleString() }}</span>
                  </div>
                  <div class="flex items-center gap-1.5 text-xs font-bold text-white/50 bg-white/[0.03] px-3 py-1.5 rounded-lg border border-white/[0.05]">
                    <ClockIcon class="h-4 w-4 text-gold/60" />
                    {{ service.duration_minutes }} mins
                  </div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { 
  PlusIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  SparklesIcon, 
  TagIcon, 
  ClockIcon 
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const services = ref([]);
const activeCategory = ref('all');

const categories = computed(() => {
  const cats = new Set(services.value.map(s => s.category_name || 'General'));
  return [...cats].sort();
});

const filteredServices = computed(() => {
  if (activeCategory.value === 'all') return services.value;
  return services.value.filter(s => (s.category_name || 'General') === activeCategory.value);
});

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

async function deleteService(service) {
  const ok = await confirm('Delete Service', `Are you sure you want to delete "${service.name}"? This action cannot be undone.`);
  if (!ok) return;
  try {
    await barberApi.deleteService(service.id);
    services.value = services.value.filter(s => s.id !== service.id);
    toast.success('Service deleted');
  } catch (err) {
    toast.error('Failed to delete service');
  }
}

onMounted(fetchServices);
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
