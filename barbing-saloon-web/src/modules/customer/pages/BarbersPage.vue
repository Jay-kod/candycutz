<template>
  <CustomerLayout>
    <main class="min-h-screen pb-32 animate-fade-in space-y-8">
      <!-- Top Hero Header -->
      <section class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-black p-8 lg:p-12 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-gold/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gold/10 border border-gold/20 mb-3">
              <SparklesIcon class="h-3.5 w-3.5 text-gold" />
              <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Master Craftsmen</span>
            </div>
            <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
              Meet Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-500 to-amber-600 dark:via-amber-300 dark:to-amber-200">Master Barbers</span>
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
              Discover our licensed stylists, explore their craft and specialties, and view their portfolio lookbooks. Click any barber to learn more about their journey and book your seat.
            </p>
          </div>

          <!-- Quick Stats Pill Group -->
          <div class="flex items-center gap-3 shrink-0">
            <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-4 py-3 text-center shadow-sm">
              <span class="block text-lg sm:text-xl font-display font-bold text-gold">{{ barbers.length }}</span>
              <span class="text-[9px] uppercase tracking-wider text-slate-600 dark:text-slate-400 font-bold">Stylists</span>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-black/40 px-4 py-3 text-center shadow-sm">
              <span class="block text-lg sm:text-xl font-display font-bold text-emerald-600 dark:text-emerald-400">{{ availableCount }}</span>
              <span class="text-[9px] uppercase tracking-wider text-slate-600 dark:text-slate-400 font-bold">Available</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Search & Filter Controls -->
      <section class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
          <MagnifyingGlassIcon class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by stylist name, specialty (fade, beard, razor)..."
            class="w-full rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] py-3.5 pl-11 pr-4 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none transition-all focus:border-gold/50 focus:ring-4 focus:ring-gold/10"
            data-test="search-barber-input"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 dark:hover:text-white cursor-pointer"
          >
            <XMarkIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Filter Chips -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
          <button
            v-for="filter in filterOptions"
            :key="filter.id"
            @click="activeFilter = filter.id"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all cursor-pointer"
            :class="activeFilter === filter.id
              ? 'bg-gold text-obsidian font-bold shadow-md shadow-gold/20'
              : 'bg-white dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08] text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06] hover:text-slate-900 dark:hover:text-white'"
          >
            <span>{{ filter.label }}</span>
            <span
              class="px-1.5 py-0.5 rounded-full text-[10px] font-mono leading-none"
              :class="activeFilter === filter.id ? 'bg-black/20 text-obsidian font-bold' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-400'"
            >
              {{ filter.count }}
            </span>
          </button>

          <button
            type="button"
            @click="loadBarbers"
            :disabled="isLoading"
            class="p-2.5 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-all disabled:opacity-50 cursor-pointer"
            title="Refresh Stylists"
          >
            <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': isLoading }" />
          </button>
        </div>
      </section>

      <!-- Loading State: Zero-CLS Skeletons -->
      <section v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="i in 6"
          :key="i"
          class="rounded-3xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-[#12161C] p-6 space-y-4 animate-pulse"
        >
          <div class="flex items-center gap-4">
            <div class="h-20 w-20 rounded-2xl bg-slate-200 dark:bg-white/5 shrink-0"></div>
            <div class="space-y-2 flex-1">
              <div class="h-5 w-32 rounded bg-slate-200 dark:bg-white/5"></div>
              <div class="h-3 w-20 rounded bg-slate-200 dark:bg-white/5"></div>
              <div class="h-3 w-24 rounded bg-slate-200 dark:bg-white/5"></div>
            </div>
          </div>
          <div class="h-12 w-full rounded-xl bg-slate-200 dark:bg-white/5"></div>
          <div class="flex gap-2">
            <div class="h-7 w-16 rounded-lg bg-slate-200 dark:bg-white/5"></div>
            <div class="h-7 w-20 rounded-lg bg-slate-200 dark:bg-white/5"></div>
          </div>
          <div class="pt-2 flex gap-3">
            <div class="h-10 flex-1 rounded-xl bg-slate-200 dark:bg-white/5"></div>
            <div class="h-10 flex-1 rounded-xl bg-slate-200 dark:bg-white/5"></div>
          </div>
        </div>
      </section>

      <!-- Error State -->
      <section v-else-if="errorMessage" class="rounded-3xl border border-red-500/20 bg-red-500/5 p-12 text-center max-w-xl mx-auto space-y-4">
        <ExclamationTriangleIcon class="h-12 w-12 text-red-400 mx-auto" />
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Unable to Load Stylists</h3>
        <p class="text-xs text-slate-600 dark:text-slate-400">{{ errorMessage }}</p>
        <button
          @click="loadBarbers"
          class="inline-flex items-center gap-2 rounded-xl bg-gold px-6 py-2.5 text-xs font-bold text-obsidian hover:brightness-110 transition-all cursor-pointer"
        >
          <ArrowPathIcon class="h-4 w-4" />
          <span>Try Again</span>
        </button>
      </section>

      <!-- Empty State -->
      <section v-else-if="filteredBarbers.length === 0" class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] py-20 px-6 text-center max-w-xl mx-auto space-y-4">
        <div class="h-16 w-16 mx-auto rounded-2xl bg-gold/10 border border-gold/20 flex items-center justify-center text-gold">
          <UserGroupIcon class="h-8 w-8" />
        </div>
        <h3 class="text-xl font-display font-bold text-slate-900 dark:text-white">No Stylists Found</h3>
        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
          We couldn't find any barbers matching "<span class="text-gold">{{ searchQuery }}</span>". Try clearing your search or choosing another filter.
        </p>
        <button
          @click="resetFilters"
          class="inline-flex items-center gap-2 rounded-xl bg-slate-100 dark:bg-white/[0.06] border border-slate-200 dark:border-white/10 px-5 py-2.5 text-xs font-semibold text-slate-800 dark:text-white hover:bg-slate-200 dark:hover:bg-white/[0.1] transition-all cursor-pointer"
        >
          Reset Filters
        </button>
      </section>

      <!-- Barbers Grid -->
      <section v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <CustomerBarberCard
          v-for="barber in filteredBarbers"
          :key="barber.id"
          :barber="barber"
          @click="goToProfile(barber.id)"
        />
      </section>
    </main>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import CustomerBarberCard from '../components/barbers/CustomerBarberCard.vue';
import { client } from '@/shared/api/client';
import {
  SparklesIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ArrowPathIcon,
  UserGroupIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();

const barbers = ref([]);
const isLoading = ref(true);
const errorMessage = ref('');
const searchQuery = ref('');
const activeFilter = ref('all');

async function loadBarbers() {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const res = await client.get('/barbers');
    const data = res.data?.data || res.data || [];
    barbers.value = Array.isArray(data) ? data : [];
  } catch (err) {
    console.error('Failed to fetch barbers', err);
    errorMessage.value = 'Failed to load barbers. Please check your connection and try again.';
  } finally {
    isLoading.value = false;
  }
}

const availableCount = computed(() => {
  return barbers.value.filter((b) => b.is_available && (b.chair_status === 'free' || !b.chair_status)).length;
});

const topRatedCount = computed(() => {
  return barbers.value.filter((b) => Number(b.rating || 5) >= 4.8).length;
});

const filterOptions = computed(() => [
  { id: 'all', label: 'All Stylists', count: barbers.value.length },
  { id: 'available', label: 'Available Now', count: availableCount.value },
  { id: 'top_rated', label: 'Top Rated', count: topRatedCount.value },
]);

const filteredBarbers = computed(() => {
  let list = barbers.value;

  if (activeFilter.value === 'available') {
    list = list.filter((b) => b.is_available && (b.chair_status === 'free' || !b.chair_status));
  } else if (activeFilter.value === 'top_rated') {
    list = list.filter((b) => Number(b.rating || 5) >= 4.8);
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase();
    list = list.filter((b) => {
      const name = (b.name || '').toLowerCase();
      const bio = (b.bio || '').toLowerCase();
      const specs = Array.isArray(b.specialties) ? b.specialties.join(' ').toLowerCase() : '';
      return name.includes(q) || bio.includes(q) || specs.includes(q);
    });
  }

  return list;
});

function resetFilters() {
  searchQuery.value = '';
  activeFilter.value = 'all';
}

function goToProfile(id) {
  router.push(`/customer/dashboard/barber/${id}`);
}

onMounted(() => {
  window.scrollTo(0, 0);
  loadBarbers();
});
</script>
