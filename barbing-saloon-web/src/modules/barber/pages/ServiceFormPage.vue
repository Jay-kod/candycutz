<template>
  <BarberLayout>
    <section class="animate-fade-in pb-10">

      <!-- Premium Header -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6 backdrop-blur-3xl mb-8">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-gold/10 blur-[100px]"></div>
        <div class="absolute -left-24 bottom-0 h-56 w-56 rounded-full bg-emerald-500/8 blur-[80px]"></div>

        <div class="relative z-10 flex items-center gap-6">
          <router-link to="/barber/services" class="group flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.15)] hover:border-gold/30">
            <ArrowLeftIcon class="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
          </router-link>
          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-gold/80 font-bold mb-1">{{ isEditing ? 'Edit Service' : 'New Service' }}</p>
            <h1 class="font-display text-3xl text-white drop-shadow-md">
              {{ isEditing ? 'Update Service Details' : 'Add a New Service' }}
            </h1>
          </div>
        </div>

        <div class="relative z-10 hidden md:block">
          <button @click="submitForm" :disabled="submitting || loadingData" class="flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-gold to-gold-dark py-3.5 px-8 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(212,175,55,0.4)] disabled:opacity-50 active:scale-[0.98]">
            <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
            <CheckIcon v-else class="h-5 w-5" />
            {{ submitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Service') }}
          </button>
        </div>
      </div>

      <div v-if="loadingData" class="flex justify-center py-32">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-gold/30 border-t-gold"></div>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 items-start">

        <!-- Left: Live Preview Card -->
        <div class="lg:sticky lg:top-8 space-y-6">
          <div class="flex items-center gap-2 px-2">
            <EyeIcon class="h-4 w-4 text-white/30" />
            <h3 class="text-[10px] uppercase tracking-[0.2em] font-bold text-white/30">Live Service Preview</h3>
          </div>

          <div class="group relative overflow-hidden rounded-[2.5rem] border border-white/[0.08] bg-gradient-to-br from-[#1a1a1a] to-[#111] shadow-[0_20px_60px_rgba(0,0,0,0.6)]">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay"></div>
            <!-- Glow accent -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-gold to-gold-dark z-20"></div>

            <!-- Image Gallery Preview -->
            <div v-if="hasAnyImage" class="relative w-full h-48 bg-black/40 overflow-hidden">
              <div class="absolute inset-0 flex transition-transform duration-500" :style="{ transform: `translateX(-${activePreviewIndex * 100}%)` }">
                <img v-for="(img, idx) in previewImages" :key="idx" :src="img" class="w-full h-full object-cover shrink-0" />
              </div>
              
              <div v-if="previewImages.length > 1" class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 z-10">
                <button 
                  v-for="(_, idx) in previewImages" 
                  :key="idx" 
                  @click="activePreviewIndex = idx"
                  class="h-1.5 rounded-full transition-all duration-300"
                  :class="activePreviewIndex === idx ? 'w-4 bg-gold' : 'w-1.5 bg-white/40 hover:bg-white/70'"
                ></button>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-transparent to-transparent opacity-80"></div>
            </div>

            <div class="p-8 relative z-10 flex flex-col" :class="{'pt-4': hasAnyImage}">
              <!-- Category badge -->
              <div class="mb-5">
                <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest border border-gold/20 bg-gold/10 text-gold shadow-lg">
                  <TagIcon class="h-3 w-3" />
                  {{ selectedCategoryName || 'General' }}
                </span>
              </div>

              <!-- Name -->
              <h2 class="font-display text-3xl text-white leading-tight mb-3 drop-shadow-md">
                {{ form.name || 'Service Name' }}
              </h2>

              <!-- Description -->
              <p class="text-sm text-white/50 leading-relaxed mb-6">
                {{ form.description || 'This service doesn\'t have a description yet.' }}
              </p>

              <!-- Availability -->
              <div class="mb-6">
                <span
                  class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider border"
                  :class="form.is_available
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                    : 'bg-red-500/10 text-red-400 border-red-500/20'"
                >
                  {{ form.is_available ? '● Available for Booking' : '● Currently Unavailable' }}
                </span>
              </div>

              <!-- Price & Duration -->
              <div class="pt-6 flex items-center justify-between border-t border-white/[0.05]">
                <div class="flex items-baseline gap-1 text-emerald-400 font-display drop-shadow-md">
                  <span class="text-sm opacity-60">₦</span>
                  <span class="text-3xl font-bold">{{ form.price ? Number(form.price).toLocaleString() : '0' }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs font-bold text-white/50 bg-white/[0.03] px-3 py-2 rounded-xl border border-white/[0.05]">
                  <ClockIcon class="h-4 w-4 text-gold/60" />
                  {{ form.duration_minutes || 0 }} mins
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Form Sections -->
        <div class="space-y-8">

          <!-- Section 1: Basic Info -->
          <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
            <div class="absolute -right-32 -top-32 h-64 w-64 rounded-full bg-gold/5 blur-[80px]"></div>

            <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
              <div class="p-2.5 rounded-xl bg-gold/10 border border-gold/20">
                <SparklesIcon class="h-5 w-5 text-gold" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Service Details</h2>
                <p class="text-xs text-white/40 mt-0.5">Name, description, and category</p>
              </div>
            </div>

            <div class="p-8 space-y-6 relative z-10">
              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Service Name</label>
                <div class="relative group">
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <SparklesIcon class="h-5 w-5 text-white/30 group-focus-within:text-gold/70 transition-colors" />
                  </div>
                  <input v-model="form.name" required type="text" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-gold/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-gold/10 hover:bg-white/[0.04]" placeholder="e.g. Classic Fade Haircut" />
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Description</label>
                <textarea v-model="form.description" rows="4" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] p-5 text-sm text-white outline-none transition-all focus:border-gold/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-gold/10 resize-none hover:bg-white/[0.04]" placeholder="Describe what this service includes..."></textarea>
              </div>

              <div class="space-y-2">
                <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Category</label>
                <div class="relative">
                  <select v-model="form.category_id" class="w-full rounded-2xl border border-white/[0.08] bg-[#1a1a1a] px-4 py-4 text-sm font-bold text-white outline-none transition-all focus:border-gold/50 focus:ring-4 focus:ring-gold/10 appearance-none cursor-pointer hover:border-white/20">
                    <option :value="null">No Category (General)</option>
                    <option v-for="cat in serviceCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Section 2: Media & Gallery -->
          <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
            <div class="absolute -right-32 top-0 h-64 w-64 rounded-full bg-purple-500/5 blur-[80px]"></div>

            <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
              <div class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20">
                <PhotoIcon class="h-5 w-5 text-purple-400" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Media Gallery</h2>
                <p class="text-xs text-white/40 mt-0.5">Upload up to 3 pictures to show customers what to expect</p>
              </div>
            </div>
            
            <div class="p-8 relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6">
              
              <!-- Image 1 -->
              <div class="space-y-3">
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold text-center">Primary Image</p>
                <div class="relative aspect-square rounded-2xl border-2 border-dashed border-white/10 overflow-hidden bg-black/20 hover:bg-white/5 hover:border-gold/30 transition-all flex flex-col items-center justify-center group cursor-pointer" @click="triggerUpload(1)">
                  <img v-if="images.url1" :src="images.url1" class="absolute inset-0 w-full h-full object-cover z-10" />
                  <div v-if="images.url1" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
                    <button @click.stop="removeImage(1)" class="p-2 bg-red-500/20 text-red-400 rounded-full hover:bg-red-500 hover:text-white transition-all">
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                  
                  <div v-if="!images.url1" class="flex flex-col items-center text-white/30 group-hover:text-gold/70 transition-colors">
                    <PhotoIcon class="h-8 w-8 mb-2" />
                    <span class="text-xs font-bold">Upload</span>
                  </div>
                  <input type="file" ref="file1" @change="e => handleFileChange(e, 1)" accept="image/jpeg,image/png,image/webp" class="hidden" />
                </div>
              </div>
              
              <!-- Image 2 -->
              <div class="space-y-3">
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold text-center">Image 2</p>
                <div class="relative aspect-square rounded-2xl border-2 border-dashed border-white/10 overflow-hidden bg-black/20 hover:bg-white/5 hover:border-gold/30 transition-all flex flex-col items-center justify-center group cursor-pointer" @click="triggerUpload(2)">
                  <img v-if="images.url2" :src="images.url2" class="absolute inset-0 w-full h-full object-cover z-10" />
                  <div v-if="images.url2" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
                    <button @click.stop="removeImage(2)" class="p-2 bg-red-500/20 text-red-400 rounded-full hover:bg-red-500 hover:text-white transition-all">
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                  
                  <div v-if="!images.url2" class="flex flex-col items-center text-white/30 group-hover:text-gold/70 transition-colors">
                    <PhotoIcon class="h-8 w-8 mb-2" />
                    <span class="text-xs font-bold">Upload</span>
                  </div>
                  <input type="file" ref="file2" @change="e => handleFileChange(e, 2)" accept="image/jpeg,image/png,image/webp" class="hidden" />
                </div>
              </div>
              
              <!-- Image 3 -->
              <div class="space-y-3">
                <p class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold text-center">Image 3</p>
                <div class="relative aspect-square rounded-2xl border-2 border-dashed border-white/10 overflow-hidden bg-black/20 hover:bg-white/5 hover:border-gold/30 transition-all flex flex-col items-center justify-center group cursor-pointer" @click="triggerUpload(3)">
                  <img v-if="images.url3" :src="images.url3" class="absolute inset-0 w-full h-full object-cover z-10" />
                  <div v-if="images.url3" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center justify-center">
                    <button @click.stop="removeImage(3)" class="p-2 bg-red-500/20 text-red-400 rounded-full hover:bg-red-500 hover:text-white transition-all">
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                  
                  <div v-if="!images.url3" class="flex flex-col items-center text-white/30 group-hover:text-gold/70 transition-colors">
                    <PhotoIcon class="h-8 w-8 mb-2" />
                    <span class="text-xs font-bold">Upload</span>
                  </div>
                  <input type="file" ref="file3" @change="e => handleFileChange(e, 3)" accept="image/jpeg,image/png,image/webp" class="hidden" />
                </div>
              </div>
              
            </div>
          </div>

          <!-- Section 3: Pricing & Duration -->
          <div class="rounded-[2.5rem] border border-white/[0.05] bg-[#151515] overflow-hidden shadow-2xl relative">
            <div class="absolute -left-32 bottom-0 h-64 w-64 rounded-full bg-emerald-500/5 blur-[80px]"></div>

            <div class="px-8 py-6 border-b border-white/[0.05] flex items-center gap-3 bg-black/20">
              <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                <CurrencyDollarIcon class="h-5 w-5 text-emerald-500" />
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Pricing & Duration</h2>
                <p class="text-xs text-white/40 mt-0.5">Set the cost and estimated time</p>
              </div>
            </div>

            <div class="p-8 relative z-10">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Price (₦)</label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <span class="text-white/30 text-lg font-bold group-focus-within:text-emerald-400/70 transition-colors">₦</span>
                    </div>
                    <input v-model.number="form.price" required type="number" min="0" step="0.01" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-gold/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-gold/10 hover:bg-white/[0.04]" placeholder="e.g. 5000" />
                  </div>
                </div>

                <div class="space-y-2">
                  <label class="text-[11px] uppercase tracking-[0.2em] text-white/40 ml-1 font-bold">Duration (minutes)</label>
                  <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <ClockIcon class="h-5 w-5 text-white/30 group-focus-within:text-gold/70 transition-colors" />
                    </div>
                    <input v-model.number="form.duration_minutes" required type="number" min="5" step="5" class="w-full rounded-2xl border border-white/[0.08] bg-white/[0.02] pl-12 pr-4 py-4 text-sm text-white outline-none transition-all focus:border-gold/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-gold/10 hover:bg-white/[0.04]" placeholder="e.g. 30" />
                  </div>
                </div>
              </div>

              <!-- Availability Toggle -->
              <div class="mt-8 p-5 rounded-2xl border border-white/[0.05] bg-black/30 flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-white">Availability</h4>
                  <p class="text-xs text-white/40 mt-0.5">When disabled, customers cannot book this service</p>
                </div>
                <button
                  type="button"
                  @click="form.is_available = !form.is_available"
                  class="relative h-8 w-14 rounded-full border transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-gold/20 shrink-0"
                  :class="form.is_available
                    ? 'bg-emerald-500/20 border-emerald-500/40'
                    : 'bg-white/5 border-white/10'"
                >
                  <span
                    class="absolute top-1 h-6 w-6 rounded-full transition-all duration-300 shadow-lg"
                    :class="form.is_available
                      ? 'left-7 bg-emerald-400'
                      : 'left-1 bg-white/40'"
                  ></span>
                </button>
              </div>
            </div>
          </div>

          <!-- Bottom Action Bar (Mobile) -->
          <div class="md:hidden flex gap-4 pt-4">
            <router-link to="/barber/services" class="flex-[1] flex items-center justify-center py-4 px-4 rounded-2xl border border-white/10 text-sm font-bold text-white/60 hover:text-white hover:bg-white/5 transition-all">
              Cancel
            </router-link>
            <button @click="submitForm" :disabled="submitting" class="flex-[2] flex justify-center items-center gap-2 rounded-2xl bg-gradient-to-r from-gold to-gold-dark py-4 px-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(212,175,55,0.4)] disabled:opacity-50 active:scale-[0.98]">
              <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
              <CheckIcon v-else class="h-5 w-5" />
              {{ submitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Service') }}
            </button>
          </div>

        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { publicApi } from '../../public/api/public.api';
import { useToast } from '../../../core/composables/useToast';
import {
  SparklesIcon,
  TagIcon,
  ClockIcon,
  CheckIcon,
  ArrowLeftIcon,
  EyeIcon,
  CurrencyDollarIcon,
  PhotoIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const isEditing = computed(() => route.path.includes('/edit'));
const serviceId = computed(() => route.params.id);

const loadingData = ref(false);
const submitting = ref(false);
const serviceCategories = ref([]);

const activePreviewIndex = ref(0);

const file1 = ref(null);
const file2 = ref(null);
const file3 = ref(null);

const images = ref({
  file1: null, url1: '', remove1: false,
  file2: null, url2: '', remove2: false,
  file3: null, url3: '', remove3: false,
});

const previewImages = computed(() => {
  const imgs = [];
  if (images.value.url1) imgs.push(images.value.url1);
  if (images.value.url2) imgs.push(images.value.url2);
  if (images.value.url3) imgs.push(images.value.url3);
  return imgs;
});

const hasAnyImage = computed(() => previewImages.value.length > 0);

const form = ref({
  name: '',
  description: '',
  price: '',
  duration_minutes: 30,
  category_id: null,
  is_available: true
});

const selectedCategoryName = computed(() => {
  if (!form.value.category_id) return 'General';
  const cat = serviceCategories.value.find(c => String(c.id) === String(form.value.category_id));
  return cat ? cat.name : 'General';
});

function triggerUpload(num) {
  if (num === 1) file1.value?.click();
  if (num === 2) file2.value?.click();
  if (num === 3) file3.value?.click();
}

function handleFileChange(event, num) {
  const file = event.target.files[0];
  if (!file) return;
  
  if (!file.type.match('image.*')) {
    toast.error('Please upload an image file (JPG, PNG)');
    return;
  }
  
  images.value[`file${num}`] = file;
  images.value[`url${num}`] = URL.createObjectURL(file);
  images.value[`remove${num}`] = false;
  activePreviewIndex.value = 0;
}

function removeImage(num) {
  images.value[`file${num}`] = null;
  images.value[`url${num}`] = '';
  images.value[`remove${num}`] = true;
  activePreviewIndex.value = 0;
}

async function loadData() {
  loadingData.value = true;
  try {
    try {
      const catRes = await publicApi.serviceCategories();
      serviceCategories.value = catRes.data.data || [];
    } catch (e) { /* ignore */ }

    if (isEditing.value) {
      const res = await barberApi.getServices();
      const allServices = res.data.data || [];
      const service = allServices.find(s => String(s.id) === String(serviceId.value));

      if (service) {
        form.value = {
          name: service.name || '',
          description: service.description || '',
          price: service.price || '',
          duration_minutes: service.duration_minutes || 30,
          category_id: service.category_id || null,
          is_available: service.is_available !== undefined ? Boolean(Number(service.is_available)) : true
        };
        
        if (service.image) images.value.url1 = service.image;
        if (service.image2) images.value.url2 = service.image2;
        if (service.image3) images.value.url3 = service.image3;
      } else {
        toast.error('Service not found');
        router.push('/barber/services');
      }
    }
  } catch (error) {
    toast.error('Failed to load data');
  } finally {
    loadingData.value = false;
  }
}

async function submitForm() {
  if (submitting.value || loadingData.value) return;

  if (!form.value.name || !form.value.price || form.value.price <= 0) {
    toast.error('Please provide a name and valid price');
    return;
  }

  submitting.value = true;
  try {
    const payload = new FormData();
    payload.append('name', form.value.name);
    payload.append('description', form.value.description);
    payload.append('price', form.value.price);
    payload.append('duration_minutes', form.value.duration_minutes || 30);
    payload.append('category_id', form.value.category_id || '');
    payload.append('is_available', form.value.is_available ? 1 : 0);
    
    if (images.value.file1) payload.append('image1', images.value.file1);
    if (images.value.file2) payload.append('image2', images.value.file2);
    if (images.value.file3) payload.append('image3', images.value.file3);
    
    if (images.value.remove1) payload.append('remove_image1', 'true');
    if (images.value.remove2) payload.append('remove_image2', 'true');
    if (images.value.remove3) payload.append('remove_image3', 'true');

    if (isEditing.value) {
      await barberApi.updateService(serviceId.value, payload);
      toast.success('Service updated successfully!');
    } else {
      await barberApi.createService(payload);
      toast.success('Service created successfully!');
    }
    router.push('/barber/services');
  } catch (error) {
    toast.error(error.response?.data?.error || `Failed to ${isEditing.value ? 'update' : 'create'} service`);
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
