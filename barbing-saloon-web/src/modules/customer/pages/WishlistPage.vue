<template>
  <CustomerLayout>
    <section class="animate-fade-in relative max-w-6xl mx-auto space-y-8 pb-12">
      <!-- Luxury Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row justify-between md:items-center gap-6">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[var(--color-accent)]/5 blur-[80px] pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-[60px] pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold mb-2 flex items-center gap-2">
            <HeartIcon class="w-4 h-4 text-[var(--color-accent)]" />
            Curated Styles
          </p>
          <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-slate-900 dark:text-white drop-shadow-sm">
            Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-500 dark:to-amber-200">Wishlist</span>
          </h1>
          <p class="mt-2 max-w-xl text-xs sm:text-sm text-[var(--color-text-secondary)] leading-relaxed">
            Your saved cuts, signature beard stylings, and services ready for quick booking.
          </p>
        </div>
        
        <div class="relative z-10 flex items-center gap-4 bg-white/80 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] backdrop-blur-md py-3.5 px-6 rounded-2xl shrink-0 shadow-sm">
          <div class="text-center px-4 border-r border-slate-200 dark:border-white/10">
            <p class="text-[10px] text-[var(--color-text-muted)] uppercase tracking-wider font-bold mb-0.5">Saved</p>
            <p class="font-display text-2xl text-[var(--color-accent)]">{{ wishlist.length }}</p>
          </div>
          <div class="text-center px-4">
            <p class="text-[10px] text-[var(--color-text-muted)] uppercase tracking-wider font-bold mb-0.5">Status</p>
            <p class="font-bold text-xs text-emerald-500 dark:text-emerald-400">Ready</p>
          </div>
        </div>
      </div>

      <!-- Zero-CLS Skeleton Grid -->
      <div v-if="loading" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 3" :key="i" class="h-80 rounded-3xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="wishlist.length === 0" class="relative overflow-hidden rounded-3xl border border-dashed border-slate-300 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02] py-20 text-center shadow-xl">
        <div class="relative z-10 flex flex-col items-center">
          <div class="h-20 w-20 bg-amber-50 dark:bg-white/[0.04] border border-gold/30 dark:border-white/10 rounded-3xl flex items-center justify-center mb-6">
            <HeartIcon class="h-9 w-9 text-[var(--color-accent)]/80" />
          </div>
          
          <h3 class="font-display text-2xl text-slate-900 dark:text-white mb-2">No Saved Styles Yet</h3>
          <p class="max-w-md text-xs text-[var(--color-text-secondary)] leading-relaxed mb-6">
            Explore our curated service catalog or lookbook gallery. Click the heart icon to save reference cuts for your upcoming appointments.
          </p>
          
          <div class="flex items-center gap-3">
            <RouterLink 
              to="/customer/dashboard/services" 
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] text-white px-6 py-2.5 text-xs font-bold hover:brightness-110 transition-all shadow-md shadow-[var(--color-accent)]/20"
            >
              <ScissorsIcon class="w-4 h-4" />
              Browse Services
            </RouterLink>
            <RouterLink 
              to="/customer/dashboard/gallery" 
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-white px-6 py-2.5 text-xs font-bold hover:bg-slate-200 dark:hover:bg-white/[0.08] transition-all"
            >
              View Gallery
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Wishlist Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div 
          v-for="item in wishlist" 
          :key="item.id" 
          class="group relative flex flex-col overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] transition-all duration-300 hover:-translate-y-1 hover:border-gold/40 hover:shadow-xl"
        >
          <!-- Media Header -->
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100 dark:bg-white/[0.02]">
            <div class="absolute inset-0 bg-gradient-to-t from-white dark:from-[#12161C] via-transparent to-transparent z-10 pointer-events-none opacity-90"></div>
            
            <img 
              v-if="item.item_type === 'gallery' && item.gallery_image" 
              :src="item.gallery_image" 
              alt="Style" 
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <img 
              v-else-if="item.item_type === 'service' && item.service_image" 
              :src="item.service_image" 
              alt="Service" 
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100 dark:bg-white/[0.02]">
               <ScissorsIcon class="h-16 w-16 text-slate-300 dark:text-white/10" />
            </div>

            <!-- Remove Button -->
            <button 
              @click="removeItem(item.id, item.item_type === 'gallery' ? item.gallery_title : item.service_name)" 
              class="absolute top-3 right-3 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 backdrop-blur-md border border-white/10 text-white/60 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-md"
              title="Remove from wishlist"
            >
              <TrashIcon class="h-4 w-4" />
            </button>
            
            <!-- Type Badge -->
            <div class="absolute bottom-3 left-4 z-20">
               <span v-if="item.item_type === 'gallery'" class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white bg-black/60 backdrop-blur-md border border-white/10 rounded-lg">
                 Gallery Look
               </span>
               <span v-else class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[var(--color-accent)] bg-[var(--color-accent)]/15 backdrop-blur-md border border-[var(--color-accent)]/30 rounded-lg">
                 Service
               </span>
            </div>
          </div>

          <!-- Content Body -->
          <div class="flex flex-col flex-1 p-5 relative z-20 bg-white dark:bg-[#12161C] -mt-2">
            <h3 class="font-display text-base text-slate-900 dark:text-white group-hover:text-[var(--color-accent)] transition-colors truncate">
              {{ item.item_type === 'gallery' ? item.gallery_title : item.service_name }}
            </h3>
            
            <div class="mt-2 flex items-center justify-between flex-1">
              <div v-if="item.item_type === 'service'">
                <p class="text-[10px] text-[var(--color-text-muted)] uppercase tracking-wider font-bold">Standard Rate</p>
                <p class="font-bold text-base text-emerald-600 dark:text-emerald-400">₦{{ Number(item.price || 0).toLocaleString() }}</p>
              </div>
              <div v-else>
                <p class="text-xs text-[var(--color-text-secondary)] italic">Visual inspiration cut</p>
              </div>
            </div>
            
            <div class="mt-5 pt-4 border-t border-slate-100 dark:border-white/[0.06]">
              <RouterLink 
                v-if="item.item_type === 'service'"
                :to="`/customer/dashboard/book/${item.item_id}`" 
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] py-2.5 text-xs font-bold text-white shadow-md shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.98] transition-all"
              >
                <span>Book This Cut</span>
                <ChevronRightIcon class="h-3.5 w-3.5" />
              </RouterLink>
              <RouterLink 
                v-else
                to="/customer/dashboard/services" 
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-800 dark:text-white py-2.5 text-xs font-bold hover:bg-slate-200 dark:hover:bg-white/[0.08] hover:border-gold/30 transition-all"
              >
                <CalendarIcon class="w-4 h-4" />
                Schedule Cut
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { HeartIcon, ScissorsIcon, TrashIcon, CalendarIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const wishlist = ref([]);

const fetchWishlist = async () => {
  loading.value = true;
  try {
    const res = await client.get('/customer/wishlist');
    wishlist.value = res.data?.data || res.data || [];
  } catch (err) {
    toast.error('Failed to load wishlist');
  } finally {
    loading.value = false;
  }
};

const removeItem = async (id, name) => {
  const ok = await confirm({ 
    title: 'Remove from Wishlist', 
    message: `Are you sure you want to remove ${name || 'this item'} from your wishlist?`, 
    confirmText: 'Remove' 
  });
  
  if (!ok) return;

  try {
    await client.delete(`/customer/wishlist/${id}`);
    wishlist.value = wishlist.value.filter(i => i.id !== id);
    toast.success('Removed from wishlist');
  } catch (err) {
    toast.error('Failed to remove item');
  }
};

onMounted(fetchWishlist);
</script>