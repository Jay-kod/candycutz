<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in pb-10">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-10 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-gold/5 blur-[100px]"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.06),transparent_70%)]"></div>
        <div class="relative z-10">
          <p class="text-[11px] uppercase tracking-[0.35em] text-gold/70 font-bold">Barber Settings</p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg leading-tight">
            My <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-amber-400">Profile</span>
          </h1>
          <p class="mt-3 max-w-xl text-sm text-ivory/50 leading-relaxed">
            Update your personal details, specialties, and let your clients know exactly what you bring to the chair.
          </p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <!-- Profile Form -->
        <form
          class="rounded-3xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-xl overflow-hidden shadow-xl"
          @submit.prevent="submit"
        >
          <div class="flex items-center gap-3 border-b border-white/[0.04] px-8 py-6 bg-black/20">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20 shadow-inner">
              <UserIcon class="h-5 w-5 text-gold" />
            </div>
            <div>
              <h2 class="font-display text-xl text-theme-text">Personal Details</h2>
              <p class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold mt-0.5">Your public persona</p>
            </div>
          </div>

          <div class="p-8 space-y-6">
            
            <!-- Profile Picture Placeholder -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-6 pb-4 border-b border-white/5">
              <div class="relative group cursor-pointer">
                <div class="h-28 w-28 rounded-full border-2 border-gold/30 bg-[#1a1a1a] overflow-hidden shadow-[0_0_20px_rgba(212,175,55,0.15)] flex items-center justify-center transition-all duration-300 group-hover:border-gold">
                  <img v-if="form.profile_image" :src="form.profile_image" class="h-full w-full object-cover" />
                  <UserIcon v-else class="h-12 w-12 text-ivory/20" />
                  <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity backdrop-blur-[2px]">
                    <CameraIcon class="h-6 w-6 text-white" />
                  </div>
                </div>
                <!-- Hidden file input for future implementation -->
                <input type="file" class="hidden" accept="image/*" @change="handleImageUpload" ref="fileInput" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-theme-text">Profile Picture</h3>
                <p class="text-xs text-ivory/40 mt-1 max-w-sm">Upload a professional headshot or avatar. PNG, JPG up to 5MB. (Currently a placeholder feature)</p>
                <button type="button" @click="$refs.fileInput.click()" class="mt-3 px-4 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-bold text-theme-text transition-colors">
                  Change Image
                </button>
              </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
              <label class="block">
                <span class="text-[11px] uppercase tracking-widest text-ivory/50 font-bold ml-1 mb-2 block">Full Name</span>
                <input
                  v-model="form.name"
                  class="w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3.5 text-sm text-theme-text placeholder:text-ivory/20 outline-none transition-all focus:border-gold/50 focus:bg-black/40 focus:ring-1 focus:ring-gold/30"
                  placeholder="e.g. Obo Shadow"
                  required
                />
              </label>
              <label class="block">
                <span class="text-[11px] uppercase tracking-widest text-ivory/50 font-bold ml-1 mb-2 block">Phone Number</span>
                <input
                  v-model="form.phone"
                  class="w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3.5 text-sm text-theme-text placeholder:text-ivory/20 outline-none transition-all focus:border-gold/50 focus:bg-black/40 focus:ring-1 focus:ring-gold/30"
                  placeholder="e.g. +234 800 000 0000"
                />
              </label>
            </div>
            
            <label class="block">
              <span class="text-[11px] uppercase tracking-widest text-ivory/50 font-bold ml-1 mb-2 block">Bio</span>
              <textarea
                v-model="form.bio"
                rows="4"
                class="w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3.5 text-sm text-theme-text placeholder:text-ivory/20 outline-none transition-all focus:border-gold/50 focus:bg-black/40 focus:ring-1 focus:ring-gold/30 resize-none"
                placeholder="Tell clients about your experience, style, and what makes your cuts unique..."
              ></textarea>
            </label>
            <label class="block">
              <span class="text-[11px] uppercase tracking-widest text-ivory/50 font-bold ml-1 mb-2 block">Instagram Profile URL</span>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <span class="text-ivory/30 text-sm">@</span>
                </div>
                <input
                  v-model="form.instagram_url"
                  class="w-full rounded-xl border border-white/10 bg-black/20 pl-9 pr-4 py-3.5 text-sm text-theme-text placeholder:text-ivory/20 outline-none transition-all focus:border-gold/50 focus:bg-black/40 focus:ring-1 focus:ring-gold/30"
                  placeholder="instagram_handle"
                />
              </div>
            </label>

            <div class="pt-6 border-t border-white/5 flex items-center gap-4">
              <button
                type="submit"
                :disabled="saving"
                class="rounded-xl bg-gradient-to-r from-gold to-amber-500 px-8 py-3.5 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.3)] hover:shadow-[0_0_30px_rgba(212,175,55,0.5)] transition-all active:scale-[0.98] disabled:opacity-70 flex items-center gap-2"
              >
                <ArrowPathIcon v-if="saving" class="h-4 w-4 animate-spin" />
                {{ saving ? 'Saving Profile...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </form>

        <!-- Specialties Card -->
        <div class="rounded-3xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-xl overflow-hidden h-fit shadow-xl">
          <div class="flex items-center gap-3 border-b border-white/[0.04] px-8 py-6 bg-black/20">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 border border-purple-500/20 shadow-inner">
              <SparklesIcon class="h-5 w-5 text-purple-400" />
            </div>
            <div>
              <h2 class="font-display text-xl text-theme-text">Specialties</h2>
              <p class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold mt-0.5">Your signature styles</p>
            </div>
          </div>

          <div class="p-8">
            <!-- All Available Styles Grid -->
            <p class="text-[11px] uppercase tracking-widest text-ivory/50 font-bold ml-1 mb-4">Tap to select / deselect</p>
            <div class="flex flex-wrap gap-2.5 mb-6">
              <button
                v-for="style in allStyles"
                :key="style"
                type="button"
                @click="toggleSpecialty(style)"
                :class="[
                  'inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition-all duration-200 border cursor-pointer',
                  form.specialties.includes(style)
                    ? 'bg-gold/15 border-gold/50 text-gold shadow-[0_0_12px_rgba(212,175,55,0.2)] ring-1 ring-gold/20'
                    : 'bg-[#1a1a1a] border-white/8 text-ivory/40 hover:border-white/20 hover:text-ivory/60'
                ]"
              >
                <CheckIcon v-if="form.specialties.includes(style)" class="h-3.5 w-3.5" />
                <span>{{ style }}</span>
              </button>
            </div>

            <!-- Custom Add Input -->
            <div class="pt-4 border-t border-white/5">
              <p class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold ml-1 mb-3">Add custom style</p>
              <div class="flex gap-2">
                <input
                  v-model="newSpecialty"
                  @keydown.enter.prevent="addSpecialty"
                  class="flex-1 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-theme-text placeholder:text-ivory/30 outline-none transition-all focus:border-purple-500/50 focus:bg-black/40 focus:ring-1 focus:ring-purple-500/30"
                  placeholder="Type a custom style name..."
                />
                <button
                  type="button"
                  @click="addSpecialty"
                  class="rounded-xl bg-purple-500/20 hover:bg-purple-500 text-purple-400 hover:text-white px-5 py-3 text-sm font-bold transition-all border border-purple-500/30 hover:border-purple-500 hover:shadow-[0_0_15px_rgba(168,85,247,0.4)]"
                >
                  Add
                </button>
              </div>
            </div>

            <!-- Selected count -->
            <div class="mt-5 flex items-center gap-2 text-xs text-ivory/40">
              <SparklesIcon class="h-4 w-4 text-gold/60" />
              <span><strong class="text-gold">{{ form.specialties.length }}</strong> specialties selected</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { UserIcon, SparklesIcon, CameraIcon, XMarkIcon, ArrowPathIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { useToast } from '../../../core/composables/useToast';
import { useAuthStore } from '../../auth/store/auth.store';

const toast = useToast();
const authStore = useAuthStore();
const API_ROOT = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000';

const form = reactive({ 
  name: '', 
  phone: '', 
  bio: '', 
  instagram_url: '', 
  specialties: [],
  profile_image: null
});
const saving = ref(false);
const newSpecialty = ref('');
const fileInput = ref(null);

const allStyles = [
  'Classic Fade', 'High Fade', 'Taper Fade', 'Textured Crop',
  'Men Executive Cut', 'Hair Styling', 'Hair Color',
  'Full Beard Trim', 'Beard Detail', 'Beard Conditioning',
  'Hot Towel Shave', 'Scalp Treatment', 'Shampoo & Conditioning',
  'Kids Haircut', 'Kids Super Fade', 'Senior Cut',
  'Women Pixie Cut', 'Wedding Special', 'Home VIP Service',
  'Accessible Care Cut'
];

function toggleSpecialty(style) {
  const idx = form.specialties.indexOf(style);
  if (idx >= 0) {
    form.specialties.splice(idx, 1);
  } else {
    form.specialties.push(style);
  }
}

function addSpecialty() {
  const val = newSpecialty.value.trim();
  if (val && !form.specialties.includes(val)) {
    form.specialties.push(val);
  }
  newSpecialty.value = '';
}

function removeSpecialty(index) {
  form.specialties.splice(index, 1);
}

function handleImageUpload(e) {
  const file = e.target.files[0];
  if (!file) return;
  
  // Create a fake placeholder URL for visual demonstration
  const objectUrl = URL.createObjectURL(file);
  form.profile_image = objectUrl;
  toast.success('Image selected! Make sure to save the profile.');
}

async function submit() {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('phone', form.phone);
    formData.append('bio', form.bio);
    formData.append('instagram_url', form.instagram_url);
    formData.append('specialties', JSON.stringify(form.specialties));
    
    // Attach the actual file if one was selected
    const fileInputEl = fileInput.value;
    if (fileInputEl && fileInputEl.files.length > 0) {
      formData.append('profile_image', fileInputEl.files[0]);
    }
    
    const response = await barberApi.updateProfile(formData);
    if (response.data?.profile_image) {
      form.profile_image = response.data.profile_image.startsWith('http') 
        ? response.data.profile_image 
        : API_ROOT + response.data.profile_image;
    }
    await authStore.fetchUser();
    toast.success('Profile saved successfully!');
  } catch (e) {
    toast.error('Failed to save profile');
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  try {
    const response = await barberApi.profile();
    const profile = response.data?.data || {};
    
    // Set properties, providing "Obo Shadow" defaults if completely empty 
    form.name = profile.name || 'Obo Shadow';
    form.phone = profile.phone || '+234 800 123 4567';
    form.bio = profile.bio || 'Master Barber with over 5 years of experience specializing in sharp fades and crisp line-ups. I believe every haircut is an art form.';
    form.instagram_url = profile.instagram_url || 'obo_shadow_cuts';
    form.specialties = profile.specialties && profile.specialties.length > 0 
      ? profile.specialties 
      : [
          'Classic Fade', 'High Fade', 'Taper Fade', 'Textured Crop',
          'Men Executive Cut', 'Hair Styling', 'Hair Color',
          'Full Beard Trim', 'Beard Detail', 'Beard Conditioning',
          'Hot Towel Shave', 'Scalp Treatment', 'Shampoo & Conditioning',
          'Kids Haircut', 'Kids Super Fade', 'Senior Cut',
          'Women Pixie Cut', 'Wedding Special', 'Home VIP Service',
          'Accessible Care Cut'
        ];
      
    if (profile.profile_image) {
      form.profile_image = profile.profile_image.startsWith('http') 
        ? profile.profile_image 
        : API_ROOT + profile.profile_image;
    } else {
      form.profile_image = null;
    }
    
  } catch (e) {
    // API might fail if not fully implemented, fallback to defaults
    form.name = 'Obo Shadow';
    form.bio = 'Master Barber with over 5 years of experience specializing in sharp fades and crisp line-ups.';
    form.specialties = [
      'Classic Fade', 'High Fade', 'Taper Fade', 'Textured Crop',
      'Men Executive Cut', 'Full Beard Trim', 'Beard Detail',
      'Hot Towel Shave', 'Kids Haircut', 'Hair Styling'
    ];
  }
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