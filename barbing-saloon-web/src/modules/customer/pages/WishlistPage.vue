<template>
  <CustomerLayout>
    <section class="animate-fade-in relative max-w-6xl mx-auto space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-10 shadow-xl flex flex-col md:flex-row justify-between md:items-center gap-6">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[var(--color-accent)]/5 blur-[80px] pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-[60px] pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold mb-2 flex items-center gap-2">
            <HeartIcon class="w-4 h-4 text-[var(--color-accent)]" />
            Saved Favorites
          </p>
          <h1 class="font-display text-4xl lg:text-5xl text-[var(--color-text-primary)] drop-shadow-sm">
            Your <span class="text-[var(--color-accent)]">Wishlist</span>
          </h1>
          <p class="mt-3 max-w-xl text-sm md:text-base text-[var(--color-text-secondary)] leading-relaxed">
            A curated collection of your favorite styles and services. Ready for your next booking.
          </p>
        </div>
        
        <div class="relative z-10 flex items-center gap-4 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] backdrop-blur-md py-4 px-6 rounded-2xl shrink-0">
          <div class="text-center px-4 border-r border-[var(--color-border)]">
            <p class="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-1">Items</p>
            <p class="font-display text-2xl text-[var(--color-accent)]">{{ wishlist.length }}</p>
          </div>
          <div class="text-center px-4">
            <p class="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-1">Status</p>
            <p class="font-bold text-sm text-[var(--color-success)]">Ready</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-32 bg-[var(--color-bg-secondary)]/50 rounded-3xl border border-[var(--color-border)]">
        <div class="relative w-16 h-16">
          <div class="absolute inset-0 rounded-full border-4 border-[var(--color-accent)]/20"></div>
          <div class="absolute inset-0 rounded-full border-4 border-[var(--color-accent)] border-t-transparent animate-spin"></div>
          <HeartIcon class="absolute inset-0 m-auto w-6 h-6 text-[var(--color-accent)] animate-pulse" />
        </div>
        <p class="mt-4 text-[var(--color-text-muted)] font-medium">Loading your favorites...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="wishlist.length === 0" class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] py-24 text-center shadow-xl">
        <div class="relative z-10 flex flex-col items-center">
          <div class="h-28 w-28 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-full flex items-center justify-center mb-8 relative group">
            <div class="absolute inset-0 rounded-full bg-[var(--color-accent)]/5 scale-0 group-hover:scale-110 transition-transform duration-700 ease-out"></div>
            <HeartIcon class="h-12 w-12 text-[var(--color-text-muted)] group-hover:text-[var(--color-accent)] transition-colors duration-500" />
          </div>
          
          <h3 class="font-display text-3xl text-[var(--color-text-primary)] mb-3">Nothing saved yet</h3>
          <p class="max-w-md text-[var(--color-text-secondary)] leading-relaxed mb-8">
            Explore our professional services and style gallery. Click the heart icon on any item to save it here for quick access later.
          </p>
          
          <div class="flex items-center gap-4">
            <RouterLink to="/customer/dashboard/services" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] px-8 py-3.5 font-bold hover:bg-[var(--color-accent-hover)] transition-all active:scale-[0.98] shadow-md">
              <ScissorsIcon class="w-5 h-5" />
              Browse Services
            </RouterLink>
            <RouterLink to="/customer/dashboard/gallery" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-[var(--color-text-primary)] px-8 py-3.5 font-bold hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/40 transition-all active:scale-[0.98]">
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
          class="group relative flex flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] transition-all duration-500 hover:-translate-y-1 hover:border-[var(--color-accent)]/40 hover:shadow-xl"
        >
          <!-- Media Header -->
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-[var(--color-bg-tertiary)]">
            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-[var(--color-bg-primary)]/80 via-[var(--color-bg-primary)]/20 to-transparent z-10 pointer-events-none opacity-80"></div>
            
            <img 
              v-if="item.item_type === 'gallery' && item.gallery_image" 
              :src="item.gallery_image" 
              alt="Style" 
              class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
            />
            <img 
              v-else-if="item.item_type === 'service' && item.service_image" 
              :src="item.service_image" 
              alt="Service" 
              class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
            />
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-[var(--color-bg-tertiary)] group-hover:scale-105 transition-transform duration-700">
               <ScissorsIcon class="h-20 w-20 text-[var(--color-accent)]/20 mb-2 drop-shadow-xl" />
            </div>

            <!-- Remove Button -->
            <button 
              @click="removeItem(item.id, item.item_type === 'gallery' ? item.gallery_title : item.service_name)" 
              class="absolute top-4 right-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-bg-secondary)]/80 backdrop-blur-md border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:bg-[var(--color-danger)] hover:text-white hover:border-[var(--color-danger)] transition-all duration-300 transform translate-x-2 -translate-y-2 opacity-0 group-hover:translate-x-0 group-hover:translate-y-0 group-hover:opacity-100 shadow-md"
              title="Remove from wishlist"
            >
              <TrashIcon class="h-5 w-5" />
            </button>
            
            <!-- Type Badge (Bottom Left overlay) -->
            <div class="absolute bottom-4 left-4 z-20">
               <span v-if="item.item_type === 'gallery'" class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[var(--color-text-primary)] bg-[var(--color-bg-primary)]/80 backdrop-blur-md border border-[var(--color-border)] rounded-lg shadow-sm">
                 Gallery Style
               </span>
               <span v-else class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[var(--color-accent)] bg-[var(--color-accent)]/10 backdrop-blur-md border border-[var(--color-accent)]/20 rounded-lg shadow-sm">
                 Service
               </span>
            </div>
          </div>

          <!-- Content Body -->
          <div class="flex flex-col flex-1 p-6 relative z-20 bg-[var(--color-bg-secondary)] mt-[-20px] pt-8">
            <h3 class="font-display text-xl text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors duration-300 line-clamp-1">
              {{ item.item_type === 'gallery' ? item.gallery_title : item.service_name }}
            </h3>
            
            <div class="mt-2 flex items-end justify-between flex-1">
              <div v-if="item.item_type === 'service'">
                <p class="text-xs text-[var(--color-text-muted)] uppercase tracking-wider mb-0.5">Price</p>
                <p class="font-bold text-lg text-[var(--color-success)]">₦{{ item.price }}</p>
              </div>
              <div v-else>
                <p class="text-xs text-[var(--color-text-secondary)] italic line-clamp-2 pr-4">Reference style for your next haircut.</p>
              </div>
            </div>
            
            <div class="mt-6 pt-5 border-t border-[var(--color-border)]">
              <RouterLink 
                v-if="item.item_type === 'service'"
                :to="`/customer/dashboard/book/${item.item_id}`" 
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] py-3.5 text-sm font-bold text-[var(--color-accent-foreground)] transition-all duration-300 hover:bg-[var(--color-accent-hover)] hover:scale-[1.02] shadow-sm"
              >
                Book Now
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
              </RouterLink>
              <RouterLink 
                v-else
                to="/customer/dashboard/services" 
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] py-3.5 text-sm font-bold text-[var(--color-text-primary)] transition-all duration-300 hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] hover:border-[var(--color-accent)] shadow-sm"
              >
                <CalendarIcon class="w-5 h-5" />
                Browse & Book
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
import { HeartIcon, ScissorsIcon, TrashIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import { customerApi } from '@/shared/api/old_customerApi';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const wishlist = ref([]);

const fetchWishlist = async () => {
  try {
    const res = await customerApi.getWishlist();
    wishlist.value = res.data.data || [];
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
    await customerApi.removeFromWishlist(id);
    wishlist.value = wishlist.value.filter(i => i.id !== id);
    toast.info('Removed from wishlist');
  } catch (err) {
    toast.error('Failed to remove item');
  }
};

onMounted(fetchWishlist);
</script>
