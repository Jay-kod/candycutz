<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <Cog8ToothIcon class="w-4 h-4" /> Home Page Content
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Hero Section</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Configure the main text and background image for your public landing page.</p>
        </div>
        
        <div class="flex items-center gap-3">
           <button 
              @click="saveSettings" 
              :disabled="saving" 
              class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
            >
              <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
              <CheckIcon v-else class="h-5 w-5" />
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
      </div>
    </div>

    <!-- Content Area -->
    <div class="space-y-6 animate-slide-up">

            
            <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
              <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
                <div class="flex items-center gap-4">
                  <div class="h-12 w-12 rounded-2xl bg-admin/10 border border-admin/20 flex items-center justify-center shadow-[0_0_15px_rgba(255,103,0,0.1)]">
                    <WindowIcon class="h-6 w-6 text-admin" />
                  </div>
                  <div>
                    <h2 class="font-display text-2xl text-theme-text">Hero Section</h2>
                    <p class="text-sm text-ivory/50 mt-1">The first thing customers see when they visit your website.</p>
                  </div>
                </div>
              </div>
              
              <div class="p-8 space-y-8">
                <div class="grid lg:grid-cols-2 gap-8">
                  <!-- Text Content -->
                  <div class="space-y-6">
                    <div class="group">
                      <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
                        <DocumentTextIcon class="h-4 w-4 text-admin/80 group-hover:text-admin transition-colors" />
                        Hero Title
                      </label>
                      <div class="relative">
                        <input 
                          v-model="settings.hero_title" 
                          type="text" 
                          class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-4 text-lg font-display text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all hover:border-white/20" 
                          placeholder="e.g. Premium Grooming Experience"
                        />
                        <div class="absolute inset-0 rounded-xl pointer-events-none border border-admin/0 group-hover:border-admin/20 transition-colors"></div>
                      </div>
                    </div>

                    <div class="group">
                      <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
                        <ChatBubbleBottomCenterTextIcon class="h-4 w-4 text-admin/80 group-hover:text-admin transition-colors" />
                        Hero Subtitle
                      </label>
                      <div class="relative">
                        <textarea 
                          v-model="settings.hero_subtitle" 
                          rows="4" 
                          class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-4 text-sm text-ivory/80 placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 resize-none transition-all hover:border-white/20 leading-relaxed" 
                          placeholder="Write a compelling subtitle to engage visitors..."
                        ></textarea>
                        <div class="absolute inset-0 rounded-xl pointer-events-none border border-admin/0 group-hover:border-admin/20 transition-colors"></div>
                      </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                      <div class="group">
                        <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
                          Primary Button Text
                        </label>
                        <input 
                          v-model="settings.hero_btn1_text" 
                          type="text" 
                          class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-sm font-display text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all hover:border-white/20" 
                          placeholder="e.g. Login & Book Now"
                        />
                      </div>
                      <div class="group">
                        <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
                          Secondary Button Text
                        </label>
                        <input 
                          v-model="settings.hero_btn2_text" 
                          type="text" 
                          class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-sm font-display text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all hover:border-white/20" 
                          placeholder="e.g. About Us"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Image Upload -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
                      <PhotoIcon class="h-4 w-4 text-admin/80" />
                      Hero Background Image
                    </label>
                    
                    <div class="relative w-full aspect-video rounded-2xl border-2 border-dashed border-white/10 bg-black/20 hover:border-admin/40 hover:bg-admin/5 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer"
                         @click="$refs.fileInput.click()">
                      
                      <!-- Current/Preview Image -->
                      <img v-if="(previewUrl || currentHeroImage) && !imageLoadError" 
                           :src="previewUrl || getStorageUrl(currentHeroImage)" 
                           @error="imageLoadError = true"
                           @load="imageLoadError = false"
                           class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-40 transition-all duration-500" />
                           
                      <!-- Upload Overlay -->
                      <div class="relative z-10 flex flex-col items-center justify-center p-6 text-center" :class="((previewUrl || currentHeroImage) && !imageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''">
                        <div class="w-16 h-16 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center mb-4 text-admin group-hover:scale-110 transition-transform">
                          <CloudArrowUpIcon class="w-8 h-8" />
                        </div>
                        <p class="text-theme-text font-display text-lg mb-1">{{ ((previewUrl || currentHeroImage) && !imageLoadError) ? 'Change Image' : 'Upload Image' }}</p>
                        <p class="text-xs text-ivory/50">Drag & drop or click to browse</p>
                        <p class="text-[10px] text-ivory/30 uppercase tracking-widest mt-4 border border-white/5 bg-black/40 px-3 py-1 rounded-full">1920×1080 • Max 2MB • JPG/PNG</p>
                      </div>
                      
                      <input 
                        ref="fileInput"
                        type="file" 
                        class="hidden"
                        @change="handleImageUpload" 
                        accept="image/jpeg,image/png,image/webp" 
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Statistics Section -->
              <div class="border-t border-white/5 px-8 py-8 space-y-6">
                <div>
                  <h3 class="font-display text-xl text-theme-text">Statistics Banner</h3>
                  <p class="text-sm text-ivory/50">The numbers displayed immediately below the hero image.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div>
                    <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Years Experience</label>
                    <input v-model="settings.stats_years" type="text" placeholder="10+" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                  </div>
                  <div>
                    <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Premium Services</label>
                    <input v-model="settings.stats_services" type="text" placeholder="20+" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                  </div>
                  <div>
                    <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Happy Clients</label>
                    <input v-model="settings.stats_clients" type="text" placeholder="5k+" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                  </div>
                </div>
              </div>

              <!-- About Teaser -->
              <div class="border-t border-white/5 px-8 py-8 space-y-6">
                <div>
                  <h3 class="font-display text-xl text-theme-text">About Us Teaser</h3>
                  <p class="text-sm text-ivory/50">The introductory philosophy section on the home page.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                  <div class="space-y-6">
                    <div>
                      <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Title</label>
                      <input v-model="settings.about_teaser_title" type="text" placeholder="Our Philosophy" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                    </div>
                    <div>
                      <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Heading</label>
                      <input v-model="settings.about_teaser_subtitle" type="text" placeholder="A Sharper Standard" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description Text</label>
                    <textarea v-model="settings.about_teaser_text" rows="5" placeholder="Welcome to CandyCutz..." class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all resize-none"></textarea>
                  </div>
                </div>
                
                <div class="mt-6">
                  <label class="flex items-center gap-2 text-sm font-bold text-ivory/80 mb-3 uppercase tracking-wider">
                    <PhotoIcon class="h-4 w-4 text-admin/80" />
                    Side Image
                  </label>
                  
                  <div class="relative w-full max-w-md aspect-[4/3] rounded-2xl border-2 border-dashed border-white/10 bg-black/20 hover:border-admin/40 hover:bg-admin/5 transition-all duration-300 group overflow-hidden flex flex-col items-center justify-center cursor-pointer"
                       @click="$refs.aboutFileInput.click()">
                    
                    <!-- Current/Preview Image -->
                    <img v-if="!aboutImageLoadError" 
                         :src="previewAboutUrl || (currentAboutImage ? getStorageUrl(currentAboutImage) : 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80')" 
                         @error="aboutImageLoadError = true"
                         @load="aboutImageLoadError = false"
                         class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-40 transition-all duration-500" />
                         
                    <!-- Upload Overlay -->
                    <div class="relative z-10 flex flex-col items-center justify-center p-6 text-center" :class="(!aboutImageLoadError) ? 'opacity-0 group-hover:opacity-100 transition-opacity duration-300' : ''">
                      <div class="w-16 h-16 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center mb-4 text-admin group-hover:scale-110 transition-transform">
                        <CloudArrowUpIcon class="w-8 h-8" />
                      </div>
                      <p class="text-theme-text font-display text-lg mb-1">{{ ((previewAboutUrl || currentAboutImage) && !aboutImageLoadError) ? 'Change Image' : 'Upload Image' }}</p>
                      <p class="text-[10px] text-ivory/30 uppercase tracking-widest mt-2 border border-white/5 bg-black/40 px-3 py-1 rounded-full">Max 2MB • JPG/PNG</p>
                    </div>
                    
                    <input 
                      ref="aboutFileInput"
                      type="file" 
                      class="hidden"
                      @change="handleAboutImageUpload" 
                      accept="image/jpeg,image/png,image/webp" 
                    />
                  </div>
                </div>
              </div>

              <!-- Portal Teaser -->
              <div class="border-t border-white/5 px-8 py-8 space-y-6">
                <div>
                  <h3 class="font-display text-xl text-theme-text">Customer Portal Teaser</h3>
                  <p class="text-sm text-ivory/50">The call to action section encouraging users to log in.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-6">
                  <div class="space-y-6">
                    <div>
                      <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Title</label>
                      <input v-model="settings.portal_teaser_title" type="text" placeholder="The CandyCutz Portal" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                    </div>
                    <div>
                      <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Heading</label>
                      <input v-model="settings.portal_teaser_subtitle" type="text" placeholder="Unlock the Full Experience" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description Text</label>
                    <textarea v-model="settings.portal_teaser_text" rows="5" placeholder="Create an account to browse..." class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all resize-none"></textarea>
                  </div>
                </div>
              </div>
              
              <!-- Bottom Action Bar -->
              <div class="border-t border-white/5 bg-black/20 px-8 py-5 flex justify-end">
                <button 
                  @click="saveSettings" 
                  :disabled="saving" 
                  class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-8 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
                >
                  <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
                  <CheckIcon v-else class="h-5 w-5" />
                  {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '../../api/admin.api';
import { 
  PhotoIcon, 
  CheckIcon, 
  Cog8ToothIcon,
  CloudArrowUpIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const saving = ref(false);

const settings = ref({
  hero_title: 'Premium grooming with a sharper standard.',
  hero_subtitle: 'Experience the CandyCutz difference. Log in to explore our full menu of premium services, meet our expert barbers, and book your next appointment seamlessly.',
  hero_btn1_text: 'Login & Book Now',
  hero_btn2_text: 'About Us',
  stats_years: '10+',
  stats_barbers: '5+',
  stats_services: '20+',
  stats_clients: '5k+',
  about_teaser_title: 'Our Philosophy',
  about_teaser_subtitle: 'A Sharper Standard',
  about_teaser_text: "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.",
  portal_teaser_title: 'The CandyCutz Portal',
  portal_teaser_subtitle: 'Unlock the Full Experience',
  portal_teaser_text: 'Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.'
});
const currentHeroImage = ref(null);
const heroImageFile = ref(null);
const fileInput = ref(null);
const imageLoadError = ref(false);

const currentAboutImage = ref(null);
const aboutImageFile = ref(null);
const aboutFileInput = ref(null);
const aboutImageLoadError = ref(false);

const previewUrl = computed(() => {
  if (heroImageFile.value) {
    return URL.createObjectURL(heroImageFile.value);
  }
  return null;
});

const previewAboutUrl = computed(() => {
  if (aboutImageFile.value) {
    return URL.createObjectURL(aboutImageFile.value);
  }
  return null;
});

const getStorageUrl = (path) => {
  if (!path) return '';
  return `${import.meta.env.VITE_API_BASE_URL.replace('/api', '')}/storage/${path}`;
};

const fetchSettings = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data.data;
    if (data) {
      Object.keys(settings.value).forEach(key => {
        if (data[key]) settings.value[key] = data[key];
      });
      currentHeroImage.value = data.hero_image || null;
      currentAboutImage.value = data.about_teaser_image || null;
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      toast.error('Image must be less than 2MB');
      event.target.value = '';
      return;
    }
    heroImageFile.value = file;
  }
};

const handleAboutImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      toast.error('Image must be less than 2MB');
      event.target.value = '';
      return;
    }
    aboutImageFile.value = file;
  }
};

const saveSettings = async () => {
  if (!settings.value.hero_title || !settings.value.hero_subtitle) {
    toast.error('Please fill in all text fields.');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    Object.keys(settings.value).forEach(key => {
      formData.append(`settings[${key}]`, settings.value[key]);
    });
    
    if (heroImageFile.value) {
      formData.append('hero_image', heroImageFile.value);
    }
    
    if (aboutImageFile.value) {
      formData.append('about_teaser_image', aboutImageFile.value);
    }

    const response = await adminApi.updateSettings(formData);
    toast.success('Settings updated successfully');
    
    // Update local state with new image path
    const updatedData = response.data.data;
    if (updatedData) {
      currentHeroImage.value = updatedData.hero_image || currentHeroImage.value;
      heroImageFile.value = null; // reset file input conceptually
      if (fileInput.value) fileInput.value.value = '';
      
      currentAboutImage.value = updatedData.about_teaser_image || currentAboutImage.value;
      aboutImageFile.value = null;
      if (aboutFileInput.value) aboutFileInput.value.value = '';
    }
  } catch (error) {
    console.error('Error saving settings:', error);
    toast.error('Failed to save settings');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
