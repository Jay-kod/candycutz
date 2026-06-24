<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <InformationCircleIcon class="w-4 h-4" /> About Page Content
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">About Us</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Edit the content displayed on your public About Us page.</p>
        </div>
        <button 
          @click="saveContent" 
          :disabled="saving" 
          class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="h-5 w-5" />
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>

    <!-- Content Editor -->
    <div class="space-y-6">
      <!-- Shop Section -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Shop Information</h2>
          <p class="mt-1 text-xs text-ivory/40">The main introductory section of the About page.</p>
        </div>
        <div class="p-8 space-y-6">
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Title</label>
              <input v-model="content.about_shop_title" type="text" placeholder="About Us" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Heading</label>
              <input v-model="content.about_shop_subtitle" type="text" placeholder="A Sharper Standard" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description Paragraph 1</label>
            <textarea v-model="content.about_shop_text_1" rows="4" placeholder="Welcome to CandyCutz..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description Paragraph 2</label>
            <textarea v-model="content.about_shop_text_2" rows="4" placeholder="Whether you're here for a quick shape-up..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
          </div>
          
          <div class="mt-6">
            <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
              <PhotoIcon class="h-4 w-4 text-admin/80" />
              Shop Image
            </label>
            
            <div class="relative w-full max-w-md aspect-[4/3] rounded-2xl border-2 border-dashed border-white/10 bg-black/20 hover:border-admin/40 hover:bg-admin/5 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer"
                 @click="$refs.shopFileInput.click()">
              
              <img v-if="!shopImageLoadError" 
                   :src="previewShopUrl || (currentShopImage ? getStorageUrl(currentShopImage) : 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80')" 
                   @error="shopImageLoadError = true"
                   @load="shopImageLoadError = false"
                   class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-40 transition-all duration-500" />
                   
              <div class="relative z-10 flex flex-col items-center justify-center p-6 text-center" :class="(!shopImageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''">
                <div class="w-16 h-16 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center mb-4 text-admin group-hover:scale-110 transition-transform">
                  <CloudArrowUpIcon class="w-8 h-8" />
                </div>
                <p class="text-theme-text font-display text-lg mb-1">{{ (!shopImageLoadError) ? 'Change Image' : 'Upload Image' }}</p>
                <p class="text-[10px] text-ivory/30 uppercase tracking-widest mt-2 border border-white/5 bg-black/40 px-3 py-1 rounded-full">Max 2MB • JPG/PNG</p>
              </div>
              
              <input 
                ref="shopFileInput"
                type="file" 
                class="hidden"
                @change="handleShopImageUpload" 
                accept="image/jpeg,image/png,image/webp" 
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Barbers Section -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Meet the Team Teaser</h2>
          <p class="mt-1 text-xs text-ivory/40">The section introducing your barbers.</p>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Title</label>
            <input v-model="content.about_team_title" type="text" placeholder="Meet the team" 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Description</label>
            <textarea v-model="content.about_team_subtitle" rows="3" placeholder="Our barbers are masters of their craft..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '../../api/admin.api';
import { InformationCircleIcon, CheckIcon, PhotoIcon, CloudArrowUpIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const saving = ref(false);

const content = ref({
  about_shop_title: 'About Us',
  about_shop_subtitle: 'A Sharper Standard',
  about_shop_text_1: "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.",
  about_shop_text_2: "Whether you're here for a quick shape-up or a complete restyle, our expert barbers are dedicated to making you look and feel your best. Experience the art of grooming at its finest.",
  about_team_title: 'Meet the team',
  about_team_subtitle: 'Our barbers are masters of their craft, bringing years of experience and a passion for perfection to every cut.'
});

const currentShopImage = ref(null);
const shopImageFile = ref(null);
const shopFileInput = ref(null);
const shopImageLoadError = ref(false);

const previewShopUrl = computed(() => {
  if (shopImageFile.value) {
    return URL.createObjectURL(shopImageFile.value);
  }
  return null;
});

const getStorageUrl = (path) => {
  if (!path) return '';
  return `${import.meta.env.VITE_API_BASE_URL.replace('/api', '')}/storage/${path}`;
};

const handleShopImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      toast.error('Image must be less than 2MB');
      event.target.value = '';
      return;
    }
    shopImageFile.value = file;
  }
};

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data.data;
    if (data) {
      Object.keys(content.value).forEach(key => {
        if (data[key]) content.value[key] = data[key];
      });
      currentShopImage.value = data.about_shop_image || null;
    }
  } catch (error) {
    console.error('Failed to load about content:', error);
  }
};

const saveContent = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    Object.keys(content.value).forEach(key => {
      formData.append(`settings[${key}]`, content.value[key]);
    });
    
    if (shopImageFile.value) {
      formData.append('about_shop_image', shopImageFile.value);
    }
    
    const response = await adminApi.updateSettings(formData);
    toast.success('About page content saved!');
    
    const updatedData = response.data.data;
    if (updatedData) {
      currentShopImage.value = updatedData.about_shop_image || currentShopImage.value;
      shopImageFile.value = null;
      if (shopFileInput.value) shopFileInput.value.value = '';
    }
  } catch (error) {
    console.error('Error saving about content:', error);
    toast.error('Failed to save changes.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchContent();
});
</script>
