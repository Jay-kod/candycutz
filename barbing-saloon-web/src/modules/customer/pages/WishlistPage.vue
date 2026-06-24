<template>
  <CustomerLayout>
    <section class="animate-fade-in relative max-w-6xl mx-auto space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-10 shadow-2xl flex flex-col md:flex-row justify-between md:items-center gap-6">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-gold/10 blur-[80px] pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-gold/5 blur-[60px] pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold mb-2 flex items-center gap-2">
            <HeartIcon class="w-4 h-4 text-gold" />
            Saved Favorites
          </p>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-lg">
            Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-white">Wishlist</span>
          </h1>
          <p class="mt-3 max-w-xl text-sm md:text-base text-white/70 leading-relaxed">
            A curated collection of your favorite styles and services. Ready for your next booking.
          </p>
        </div>
        
        <div class="relative z-10 flex items-center gap-4 bg-white/5 border border-white/10 backdrop-blur-md py-4 px-6 rounded-2xl shrink-0">
          <div class="text-center px-4 border-r border-white/10">
            <p class="text-xs text-white/50 uppercase tracking-wider mb-1">Items</p>
            <p class="font-display text-2xl text-gold">{{ wishlist.length }}</p>
          </div>
          <div class="text-center px-4">
            <p class="text-xs text-white/50 uppercase tracking-wider mb-1">Status</p>
            <p class="font-bold text-sm text-emerald-400">Ready</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-32 bg-theme-surface/30 rounded-3xl border border-theme-border/30">
        <div class="relative w-16 h-16">
          <div class="absolute inset-0 rounded-full border-4 border-gold/20"></div>
          <div class="absolute inset-0 rounded-full border-4 border-gold border-t-transparent animate-spin"></div>
          <HeartIcon class="absolute inset-0 m-auto w-6 h-6 text-gold animate-pulse" />
        </div>
        <p class="mt-4 text-theme-muted font-medium">Loading your favorites...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="wishlist.length === 0" class="relative overflow-hidden rounded-3xl border border-theme-border bg-theme-surface py-24 text-center shadow-xl">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-[0.03]"></div>
        
        <div class="relative z-10 flex flex-col items-center">
          <div class="h-28 w-28 bg-gradient-to-br from-theme-bg to-theme-surface border border-theme-border rounded-full flex items-center justify-center mb-8 shadow-[inset_0_4px_20px_rgba(0,0,0,0.5)] relative group">
            <div class="absolute inset-0 rounded-full bg-gold/5 scale-0 group-hover:scale-110 transition-transform duration-700 ease-out"></div>
            <HeartIcon class="h-12 w-12 text-theme-muted/30 group-hover:text-gold transition-colors duration-500" />
          </div>
          
          <h3 class="font-display text-3xl text-theme-text mb-3">Nothing saved yet</h3>
          <p class="max-w-md text-theme-muted leading-relaxed mb-8">
            Explore our professional services and style gallery. Click the heart icon on any item to save it here for quick access later.
          </p>
          
          <div class="flex items-center gap-4">
            <RouterLink to="/customer/dashboard/services" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gold text-obsidian px-8 py-3.5 font-bold hover:bg-gold-light transition-all active:scale-[0.98] shadow-[0_0_20px_rgba(212,175,55,0.2)]">
              <ScissorsIcon class="w-5 h-5" />
              Browse Services
            </RouterLink>
            <RouterLink to="/customer/dashboard/gallery" class="inline-flex items-center justify-center gap-2 rounded-xl bg-theme-bg border border-theme-border text-theme-text px-8 py-3.5 font-bold hover:text-gold hover:border-gold/30 transition-all active:scale-[0.98]">
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
          class="group relative flex flex-col overflow-hidden rounded-2xl border border-theme-border bg-theme-surface transition-all duration-500 hover:-translate-y-1 hover:border-gold/40 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]"
        >
          <!-- Media Header -->
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-obsidian">
            <!-- Overlay Gradient -->
            <div class="absolute inset-0 bg-gradient-to-t from-obsidian/90 via-obsidian/20 to-transparent z-10 pointer-events-none opacity-80"></div>
            
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
            <div v-else class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-charcoal to-obsidian group-hover:scale-105 transition-transform duration-700">
               <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay"></div>
               <ScissorsIcon class="h-20 w-20 text-gold/20 mb-2 drop-shadow-xl" />
            </div>

            <!-- Remove Button -->
            <button 
              @click="removeItem(item.id, item.item_type === 'gallery' ? item.gallery_title : item.service_name)" 
              class="absolute top-4 right-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-black/40 backdrop-blur-md border border-white/10 text-white/70 hover:bg-red-500/90 hover:text-white hover:border-red-400 transition-all duration-300 transform translate-x-2 -translate-y-2 opacity-0 group-hover:translate-x-0 group-hover:translate-y-0 group-hover:opacity-100"
              title="Remove from wishlist"
            >
              <TrashIcon class="h-5 w-5" />
            </button>
            
            <!-- Type Badge (Bottom Left overlay) -->
            <div class="absolute bottom-4 left-4 z-20">
               <span v-if="item.item_type === 'gallery'" class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-ivory/90 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg">
                 Gallery Style
               </span>
               <span v-else class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-gold/90 bg-gold/10 backdrop-blur-md border border-gold/20 rounded-lg">
                 Service
               </span>
            </div>
          </div>

          <!-- Content Body -->
          <div class="flex flex-col flex-1 p-6 relative z-20 bg-gradient-to-b from-transparent to-theme-surface mt-[-20px] pt-8">
            <h3 class="font-display text-xl text-theme-text group-hover:text-gold transition-colors duration-300 line-clamp-1">
              {{ item.item_type === 'gallery' ? item.gallery_title : item.service_name }}
            </h3>
            
            <div class="mt-2 flex items-end justify-between flex-1">
              <div v-if="item.item_type === 'service'">
                <p class="text-xs text-theme-muted uppercase tracking-wider mb-0.5">Price</p>
                <p class="font-bold text-lg text-emerald-400">₦{{ item.price }}</p>
              </div>
              <div v-else>
                <p class="text-xs text-theme-muted italic line-clamp-2 pr-4">Reference style for your next haircut.</p>
              </div>
            </div>
            
            <div class="mt-6 pt-5 border-t border-theme-border/50">
              <RouterLink 
                v-if="item.item_type === 'service'"
                :to="`/customer/dashboard/book/${item.item_id}`" 
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-light py-3.5 text-sm font-bold text-obsidian transition-all duration-300 hover:shadow-[0_0_20px_rgba(212,175,55,0.4)] hover:scale-[1.02]"
              >
                Book Now
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
              </RouterLink>
              <RouterLink 
                v-else
                to="/customer/dashboard/services" 
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-theme-bg border border-theme-border py-3.5 text-sm font-bold text-theme-text transition-all duration-300 hover:bg-gold hover:text-obsidian hover:border-gold hover:shadow-[0_0_20px_rgba(212,175,55,0.3)]"
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
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { HeartIcon, ScissorsIcon, TrashIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import { customerApi } from '../api/customer.api';
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
