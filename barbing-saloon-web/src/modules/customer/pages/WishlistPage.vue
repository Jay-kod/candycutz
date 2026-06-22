<template>
  <CustomerLayout>
    <section class="animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8 mb-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
            Your <span class="text-gold">Wishlist</span>
          </h1>
          <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
            Saved styles and services for quick booking.
          </p>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gold border-t-transparent"></div>
      </div>

      <div v-else-if="wishlist.length === 0" class="rounded-xl border border-gold/10 bg-surface py-20 text-center">
        <HeartIcon class="mx-auto h-12 w-12 text-theme-muted/50" />
        <h3 class="mt-4 font-display text-2xl text-theme-text">Your wishlist is empty</h3>
        <p class="mt-2 text-theme-muted">Browse our gallery or services to save your favorites.</p>
        <RouterLink to="/" class="mt-6 inline-block rounded-full bg-gold px-6 py-2.5 font-semibold text-obsidian transition-colors hover:bg-gold-light">
          Browse Styles
        </RouterLink>
      </div>

      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="item in wishlist" :key="item.id" class="group relative overflow-hidden rounded-xl border border-gold/10 bg-surface transition-all hover:border-gold/30">
          <div class="aspect-square w-full overflow-hidden bg-theme-bg">
            <img 
              v-if="item.item_type === 'gallery'" 
              :src="item.gallery_image" 
              alt="Style" 
              class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-theme-surface">
               <ScissorsIcon class="h-16 w-16 text-gold/20" />
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-center justify-between">
              <span class="text-xs uppercase tracking-widest text-gold-light font-medium">{{ item.item_type }}</span>
              <button @click="removeItem(item.id, item.item_type === 'gallery' ? item.gallery_title : item.service_name)" class="text-red-400/70 hover:text-red-400 transition-colors">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
            <h3 class="mt-2 font-display text-xl text-theme-text">
              {{ item.item_type === 'gallery' ? item.gallery_title : item.service_name }}
            </h3>
            <p v-if="item.item_type === 'service'" class="mt-1 font-bold text-theme-text">₦{{ item.price }}</p>
            
            <RouterLink 
              :to="`/customer/dashboard/bookings?service=${item.item_id}`" 
              class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-gold/10 py-2.5 text-sm font-semibold text-gold transition-colors hover:bg-gold hover:text-obsidian"
            >
              Book Now
            </RouterLink>
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
import { HeartIcon, ScissorsIcon, TrashIcon } from '@heroicons/vue/24/outline';
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
