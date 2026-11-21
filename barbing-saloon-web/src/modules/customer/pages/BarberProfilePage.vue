<template>
  <CustomerLayout>
    <main class="min-h-screen pb-32 animate-fade-in space-y-6">
      
      <!-- Top Breadcrumb & Back Link -->
      <nav class="flex items-center justify-between">
        <RouterLink
          to="/customer/dashboard/barbers"
          class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-gold transition-colors"
        >
          <ChevronLeftIcon class="h-4 w-4" />
          <span>Back to All Barbers</span>
        </RouterLink>

        <span class="text-[11px] font-mono text-slate-500">Stylist Profile</span>
      </nav>

      <!-- Zero-CLS Skeleton Loader -->
      <div v-if="isLoading" class="mx-auto max-w-6xl space-y-8">
        <div class="h-72 rounded-3xl bg-white/5 border border-white/5 animate-pulse"></div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <div class="lg:col-span-8 space-y-6">
            <div class="h-40 w-full rounded-3xl bg-white/5 animate-pulse"></div>
            <div class="h-40 w-full rounded-3xl bg-white/5 animate-pulse"></div>
          </div>
          <div class="lg:col-span-4 h-72 rounded-3xl bg-white/5 animate-pulse"></div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="mx-auto max-w-xl text-center py-24 rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-8 space-y-4">
        <div class="h-16 w-16 mx-auto rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-500 dark:text-red-400">
          <ExclamationTriangleIcon class="w-8 h-8" />
        </div>
        <h1 class="font-display text-2xl text-slate-900 dark:text-white font-bold">Stylist Not Found</h1>
        <p class="text-xs text-slate-600 dark:text-slate-400">The requested barber profile does not exist or is currently inactive.</p>
        <RouterLink 
          to="/customer/dashboard/barbers" 
          class="inline-flex items-center gap-2 rounded-xl bg-gold px-6 py-2.5 text-xs font-bold text-obsidian shadow-lg hover:brightness-110 transition-all"
        >
          View All Barbers
        </RouterLink>
      </div>

      <!-- Barber Profile Content -->
      <div v-else class="space-y-8">
        
        <!-- Hero Banner Header -->
        <section class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-black p-8 lg:p-12 shadow-2xl">
          <div v-if="barber.cover_image || barber.cover_image_url || barber.avatar || barber.avatar_url" class="absolute inset-0">
            <img :src="getFullImageUrl(barber.cover_image || barber.cover_image_url || barber.avatar || barber.avatar_url)" :alt="barber.name" class="h-full w-full object-cover opacity-20" />
            <div class="absolute inset-0 bg-gradient-to-t from-white via-white/80 dark:from-[#0E1217] dark:via-[#0E1217]/80 to-transparent"></div>
          </div>

          <!-- Cover Image Full View Action -->
          <button
            v-if="barber.cover_image || barber.cover_image_url"
            type="button"
            @click="openSingleImageViewer('cover')"
            class="absolute top-6 right-6 z-20 flex items-center gap-2 rounded-xl border border-white/10 bg-black/60 px-3.5 py-2 text-xs font-semibold text-white shadow-lg backdrop-blur-md transition-all hover:bg-black/80 hover:border-gold/40 hover:text-gold"
            data-test="btn-view-cover"
            title="View Cover Picture in full resolution"
          >
            <PhotoIcon class="h-4 w-4 text-gold" />
            <span>View Cover</span>
          </button>
          
          <div class="relative z-10 flex flex-col md:flex-row items-start md:items-end gap-6">
            <!-- Large Portrait Avatar (Clickable to view full size) -->
            <div
              class="relative h-28 w-28 md:h-36 md:w-36 rounded-3xl overflow-hidden border-2 border-gold/40 shadow-2xl bg-[#161B22] shrink-0 group transition-all"
              :class="(barber.avatar || barber.avatar_url) ? 'cursor-pointer hover:border-gold hover:shadow-gold/20' : ''"
              :tabindex="(barber.avatar || barber.avatar_url) ? 0 : -1"
              :role="(barber.avatar || barber.avatar_url) ? 'button' : undefined"
              :title="(barber.avatar || barber.avatar_url) ? 'Click to view profile picture' : barber.name"
              data-test="btn-view-avatar"
              @click="openSingleImageViewer('avatar')"
              @keydown.enter="openSingleImageViewer('avatar')"
            >
              <img
                v-if="barber.avatar || barber.avatar_url"
                :src="getFullImageUrl(barber.avatar || barber.avatar_url)"
                :alt="barber.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
              />
              <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-gold/20 to-amber-900/40 text-gold text-3xl font-display font-bold">
                {{ getInitials(barber.name) }}
              </div>
              <div
                v-if="barber.avatar || barber.avatar_url"
                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity"
              >
                <EyeIcon class="h-7 w-7 text-white drop-shadow" />
              </div>
            </div>

            <!-- Stylist Header Info -->
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex h-2.5 w-2.5 rounded-full" :class="barber.is_available ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                <p class="text-[11px] uppercase tracking-[0.25em] font-bold text-gold">Master Barber & Stylist</p>
              </div>
              
              <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 dark:text-white leading-tight">
                {{ barber.name }}
              </h1>
              
              <div class="mt-4 flex flex-wrap items-center gap-3">
                <!-- Stat: Experience -->
                <div class="flex items-center gap-2.5 bg-white/80 dark:bg-black/50 backdrop-blur-md px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm">
                  <span class="font-display text-lg text-gold font-bold">{{ barber.experience_years || barber.years_experience || '5+' }}</span>
                  <span class="text-[9px] uppercase tracking-wider text-slate-600 dark:text-slate-300 font-bold">Years Exp</span>
                </div>
                
                <!-- Stat: Rating -->
                <div class="flex items-center gap-2.5 bg-white/80 dark:bg-black/50 backdrop-blur-md px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm">
                  <span class="font-display text-lg text-amber-500 dark:text-amber-400 font-bold">{{ Number(barber.rating || 5.0).toFixed(1) }}</span>
                  <div class="flex text-amber-500 dark:text-amber-400">
                    <StarIcon v-for="i in 5" :key="i" class="w-3.5 h-3.5 fill-current" />
                  </div>
                  <span class="text-[9px] uppercase tracking-wider text-slate-500 dark:text-slate-400 font-bold">({{ barber.total_reviews || 12 }})</span>
                </div>
                
                <!-- Stat: Status -->
                <div class="flex items-center gap-2 bg-white/80 dark:bg-black/50 backdrop-blur-md px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10 shadow-sm">
                  <span class="h-2 w-2 rounded-full" :class="barber.is_available ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-amber-500 dark:bg-amber-400'"></span>
                  <span class="text-xs uppercase tracking-wider font-bold" :class="barber.is_available ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                    {{ barber.is_available ? 'Available For Booking' : 'Chair Busy' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Detailed Content Grid -->
        <section class="grid gap-8 lg:grid-cols-12 lg:items-start">
          
          <!-- Left Column: Bio, Signature Specializations, Working Hours (8 cols) -->
          <div class="lg:col-span-8 space-y-6">
            
            <!-- Bio Card -->
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl">
              <h2 class="font-display text-xl text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                <ScissorsIcon class="w-5 h-5 text-gold" />
                <span>About</span>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-amber-500 dark:to-amber-200">{{ barber.name }}</span>
              </h2>
              <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                {{ barber.bio || 'Specializing in precision scissor cuts, skin fades, beard shaping, hot towel grooming, and customized luxury salon experiences.' }}
              </p>
            </div>

            <!-- Signature Specializations -->
            <div v-if="barber.specialties && barber.specialties.length" class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl">
              <h2 class="font-display text-lg text-slate-900 dark:text-white mb-3">Signature Specialties & Craft</h2>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="spec in barber.specialties"
                  :key="spec"
                  class="rounded-xl border border-gold/30 bg-gold/10 px-3.5 py-1.5 text-xs font-semibold text-gold tracking-wide"
                >
                  {{ spec }}
                </span>
              </div>
            </div>

            <!-- Working Schedule (if loaded) -->
            <div v-if="barber.working_hours && barber.working_hours.length" class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-4">
              <div class="flex items-center gap-2">
                <ClockIcon class="h-5 w-5 text-gold" />
                <h2 class="font-display text-lg text-slate-900 dark:text-white font-bold">Regular Chair Hours</h2>
              </div>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div
                  v-for="wh in barber.working_hours"
                  :key="wh.day_of_week"
                  class="rounded-xl border border-slate-200 dark:border-white/[0.06] bg-slate-50 dark:bg-black/40 p-2.5 text-center"
                >
                  <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">{{ formatDay(wh.day_of_week) }}</span>
                  <span v-if="!wh.is_closed && wh.is_working !== false" class="text-xs font-mono text-gold font-bold block mt-0.5">
                    {{ (wh.open_time || wh.start_time)?.slice(0, 5) }} - {{ (wh.close_time || wh.end_time)?.slice(0, 5) }}
                  </span>
                  <span v-else class="text-xs font-medium text-slate-400 dark:text-slate-500 block mt-0.5">Off Day</span>
                </div>
              </div>
            </div>

            <!-- Client Reviews / Testimonials (if present) -->
            <div v-if="barber.testimonials && barber.testimonials.length" class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-4">
              <h2 class="font-display text-lg text-slate-900 dark:text-white font-bold flex items-center gap-2">
                <StarIcon class="h-5 w-5 text-amber-500 dark:text-amber-400" />
                <span>Client Feedback & Reviews</span>
              </h2>
              <div class="space-y-3">
                <div
                  v-for="rev in barber.testimonials"
                  :key="rev.id"
                  class="rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-slate-50 dark:bg-black/30 p-4 space-y-1.5"
                >
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-900 dark:text-white">{{ rev.customer?.name || 'Verified Client' }}</span>
                    <div class="flex text-amber-500 dark:text-amber-400">
                      <StarIcon v-for="s in (rev.rating || 5)" :key="s" class="w-3.5 h-3.5 fill-current" />
                    </div>
                  </div>
                  <p class="text-xs text-slate-600 dark:text-slate-300 italic">"{{ rev.content || rev.comment }}"</p>
                </div>
              </div>
            </div>

          </div>

          <!-- Right Column: Book With Barber Card (4 cols, sticky) -->
          <div class="lg:col-span-4 lg:sticky lg:top-28">
            <div class="rounded-3xl border border-gold/30 bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-2xl relative overflow-hidden space-y-6">
              <div class="absolute top-0 right-0 w-32 h-32 bg-gold/10 rounded-full blur-3xl pointer-events-none"></div>
              
              <div>
                <span class="text-[10px] uppercase font-bold text-gold tracking-widest block mb-1">Chair Reservation</span>
                <h3 class="font-display text-2xl text-slate-900 dark:text-white font-bold">Book {{ barber.name.split(' ')[0] }}</h3>
                <p class="text-slate-600 dark:text-slate-400 text-xs mt-1">Select your service and reserve an appointment directly with this master stylist.</p>
              </div>
              
              <RouterLink 
                :to="{ path: '/customer/dashboard/services', query: { barber_id: barber.id } }" 
                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 py-3.5 text-xs font-bold text-obsidian shadow-lg shadow-gold/20 hover:brightness-110 active:scale-[0.98] transition-all"
              >
                <span>Select Services & Book</span>
                <ChevronRightIcon class="w-4 h-4" />
              </RouterLink>
              
              <ul class="pt-4 border-t border-slate-100 dark:border-white/[0.08] space-y-3 text-xs text-slate-600 dark:text-slate-300">
                <li class="flex items-center gap-2.5">
                  <CheckCircleIcon class="w-4 h-4 text-emerald-500 dark:text-emerald-400 shrink-0" />
                  <span>Complimentary warm beverage included</span>
                </li>
                <li class="flex items-center gap-2.5">
                  <CheckCircleIcon class="w-4 h-4 text-emerald-500 dark:text-emerald-400 shrink-0" />
                  <span>Invigorating hot towel finish</span>
                </li>
                <li class="flex items-center gap-2.5">
                  <CheckCircleIcon class="w-4 h-4 text-emerald-500 dark:text-emerald-400 shrink-0" />
                  <span>Signature beard & scalp conditioning</span>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Barber Gallery Masonry -->
        <section v-if="barber.gallery && barber.gallery.length" class="space-y-6">
          <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/[0.06]">
            <div>
              <p class="text-xs uppercase tracking-[0.2em] text-gold font-bold">Lookbook Portfolio</p>
              <h2 class="font-display text-2xl text-slate-900 dark:text-white font-bold">Recent Cuts & Grooming by {{ barber.name }}</h2>
            </div>
          </div>
          
          <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
            <article 
              v-for="(item, index) in barber.gallery" 
              :key="item.id" 
              @click="openLightbox(index)" 
              class="group overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] relative block mb-6 break-inside-avoid cursor-pointer shadow-lg hover:border-gold/40 transition-all duration-300"
            >
              <div class="relative w-full overflow-hidden bg-white/[0.02]" :style="{ aspectRatio: index % 2 === 0 ? '4/5' : '1/1' }">
                <img v-if="item.image_path || item.image_url" :src="getImageUrl(item.image_path || item.image_url)" :alt="item.title" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-80 group-hover:opacity-95 transition-opacity"></div>
              </div>
              <div class="absolute inset-0 z-10 flex flex-col justify-end p-5 text-white">
                <p class="text-[10px] uppercase tracking-wider text-gold font-bold">{{ item.category || 'Cut' }}</p>
                <h3 class="mt-1 font-display text-lg font-bold">{{ item.title }}</h3>
              </div>
            </article>
          </div>
        </section>

      </div>
    </main>

    <!-- Lightbox Modal (Supports Single Image [Avatar/Cover] & Gallery Portfolio) -->
    <div 
      v-if="isLightboxOpen" 
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md p-4" 
      @keydown.esc="closeLightbox" 
      tabindex="0" 
      ref="lightboxRef"
      data-test="lightbox-modal"
    >
      <button 
        @click="closeLightbox" 
        class="absolute top-6 right-6 z-10 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition-colors"
        data-test="lightbox-close"
        title="Close Preview"
      >
        <XMarkIcon class="w-6 h-6" />
      </button>

      <!-- Single Image Mode (Avatar or Cover Picture) -->
      <template v-if="lightboxMode === 'single'">
        <div class="relative max-h-[85vh] max-w-4xl w-full flex flex-col items-center justify-center p-2">
          <img 
            :src="singleImage.url" 
            :alt="singleImage.title" 
            class="max-h-[72vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl border border-white/10"
            data-test="lightbox-single-img"
          />
          <div class="mt-4 text-center text-white">
            <p class="text-xs uppercase tracking-wider text-gold font-bold">{{ singleImage.subtitle }}</p>
            <h2 class="mt-1 font-display text-xl font-bold">{{ singleImage.title }}</h2>
          </div>
        </div>
      </template>

      <!-- Gallery Mode -->
      <template v-else-if="barber && barber.gallery && barber.gallery.length">
        <button 
          @click.stop="prevImage" 
          class="absolute left-6 z-10 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 transition-colors disabled:opacity-30" 
          :disabled="currentIdx === 0"
        >
          <ChevronLeftIcon class="w-6 h-6" />
        </button>

        <div class="relative max-h-[85vh] max-w-3xl w-full flex flex-col items-center justify-center">
          <img 
            v-if="barber.gallery[currentIdx]?.image_path || barber.gallery[currentIdx]?.image_url" 
            :src="getImageUrl(barber.gallery[currentIdx]?.image_path || barber.gallery[currentIdx]?.image_url)" 
            :alt="barber.gallery[currentIdx]?.title" 
            class="max-h-[70vh] w-auto rounded-2xl object-contain shadow-2xl border border-white/10" 
          />
          <div class="mt-4 text-center text-white">
            <p class="text-xs uppercase tracking-wider text-gold font-bold">{{ barber.gallery[currentIdx]?.category }}</p>
            <h2 class="mt-1 font-display text-xl font-bold">{{ barber.gallery[currentIdx]?.title }}</h2>
            <p v-if="barber.gallery[currentIdx]?.description" class="mt-1 text-xs text-white/60 max-w-xl mx-auto">{{ barber.gallery[currentIdx]?.description }}</p>
          </div>
        </div>

        <button 
          @click.stop="nextImage" 
          class="absolute right-6 z-10 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 transition-colors disabled:opacity-30" 
          :disabled="currentIdx === (barber.gallery.length - 1)"
        >
          <ChevronRightIcon class="w-6 h-6" />
        </button>
      </template>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref, nextTick } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { 
  ScissorsIcon, 
  StarIcon, 
  CheckCircleIcon, 
  ChevronRightIcon, 
  ChevronLeftIcon,
  XMarkIcon, 
  ClockIcon,
  ExclamationTriangleIcon,
  PhotoIcon,
  EyeIcon
} from '@heroicons/vue/24/outline';

function getFullImageUrl(path) {
  return getStorageUrl(path);
}

function getImageUrl(path) {
  return getStorageUrl(path);
}

function getInitials(name) {
  if (!name) return 'CC';
  return name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
}

function formatDay(day) {
  const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  return days[day] || `Day ${day}`;
}

const route = useRoute();
const barber = ref(null);
const isLoading = ref(true);
const error = ref(false);

const isLightboxOpen = ref(false);
const lightboxMode = ref('gallery'); // 'gallery' | 'single'
const singleImage = ref({ url: '', title: '', subtitle: '' });
const currentIdx = ref(0);
const lightboxRef = ref(null);

onMounted(async () => {
  window.scrollTo(0, 0);
  try {
    const id = route.params.id;
    if (!/^\d+$/.test(String(id))) {
      error.value = true;
      isLoading.value = false;
      return;
    }
    const response = await client.get(`/barbers/${id}`);
    barber.value = response.data?.data || response.data;
  } catch (err) {
    console.error('Failed to load barber profile:', err);
    error.value = true;
  } finally {
    isLoading.value = false;
  }
});

function openSingleImageViewer(type) {
  if (type === 'avatar') {
    const avatarPath = barber.value?.avatar || barber.value?.avatar_url;
    if (!avatarPath) return;
    singleImage.value = {
      url: getFullImageUrl(avatarPath),
      title: `${barber.value?.name || 'Barber'} — Profile Picture`,
      subtitle: 'Official Barber Headshot',
    };
  } else if (type === 'cover') {
    const coverPath = barber.value?.cover_image || barber.value?.cover_image_url;
    if (!coverPath) return;
    singleImage.value = {
      url: getFullImageUrl(coverPath),
      title: `${barber.value?.name || 'Barber'} — Cover Picture`,
      subtitle: 'Barber Suite & Showcase Cover Banner',
    };
  }
  lightboxMode.value = 'single';
  isLightboxOpen.value = true;
  document.body.style.overflow = 'hidden';
  nextTick(() => {
    lightboxRef.value?.focus();
  });
}

function openLightbox(index) {
  lightboxMode.value = 'gallery';
  currentIdx.value = index;
  isLightboxOpen.value = true;
  document.body.style.overflow = 'hidden';
  nextTick(() => {
    lightboxRef.value?.focus();
  });
}

function closeLightbox() {
  isLightboxOpen.value = false;
  document.body.style.overflow = '';
}

function nextImage() {
  if (currentIdx.value < barber.value.gallery.length - 1) {
    currentIdx.value++;
  }
}

function prevImage() {
  if (currentIdx.value > 0) {
    currentIdx.value--;
  }
}
</script>
