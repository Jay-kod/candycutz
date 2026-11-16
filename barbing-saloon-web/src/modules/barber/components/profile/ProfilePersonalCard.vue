<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-sm dark:shadow-xl">
    <div class="flex items-center gap-3 border-b border-slate-200 dark:border-white/[0.08] px-8 py-5 bg-slate-50 dark:bg-[#0B0F13]/40">
      <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20 shadow-inner">
        <UserIcon class="h-5 w-5 text-gold" />
      </div>
      <div>
        <h2 class="font-display text-lg text-slate-900 dark:text-white font-bold">Personal & Professional Info</h2>
        <p class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold">Your public presentation</p>
      </div>
    </div>

    <div class="p-8 space-y-6">
      <!-- Profile Picture (Avatar) -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-6 pb-6 border-b border-slate-200 dark:border-white/[0.06]">
        <div class="relative group cursor-pointer shrink-0" @click="$refs.fileInput.click()">
          <div class="h-24 w-24 rounded-2xl border-2 border-gold/30 bg-slate-100 dark:bg-[#0B0F13] overflow-hidden flex items-center justify-center group-hover:border-gold transition-colors">
            <img v-if="form.profile_image" :src="form.profile_image" @error="form.profile_image = null" class="h-full w-full object-cover" alt="Barber Portrait" />
            <UserIcon v-else class="h-10 w-10 text-slate-500" />
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
              <CameraIcon class="h-6 w-6 text-white" />
            </div>
          </div>
          <input
            ref="fileInput"
            type="file"
            class="hidden"
            accept="image/*"
            data-test="input-avatar-file"
            @change="handleFileChange"
          />
        </div>

        <div class="flex-1">
          <h3 class="text-sm font-bold text-slate-900 dark:text-white">Barber Headshot</h3>
          <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-sm">
            Upload a sharp portrait. PNG, JPG or WebP up to 5MB.
          </p>
          <div class="mt-3 flex items-center gap-2">
            <button
              type="button"
              @click="$refs.fileInput.click()"
              class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] dark:hover:bg-white/[0.08] border border-slate-200 dark:border-white/[0.08] text-xs font-semibold text-slate-700 dark:text-white transition-colors"
              data-test="btn-change-avatar"
            >
              Change Photo
            </button>
            <button
              v-if="form.profile_image"
              type="button"
              @click="openPreview(form.profile_image, 'Barber Headshot')"
              class="px-3.5 py-1.5 rounded-xl bg-gold/10 hover:bg-gold/20 border border-gold/20 text-xs font-semibold text-amber-800 dark:text-gold transition-colors flex items-center gap-1.5 font-bold"
              data-test="btn-view-avatar-preview"
            >
              <EyeIcon class="h-3.5 w-3.5" />
              <span>View Photo</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Cover Picture -->
      <div class="pb-6 border-b border-slate-200 dark:border-white/[0.06] space-y-3">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Cover Picture</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
              Banner image displayed atop your public barber profile. PNG, JPG or WebP up to 5MB.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="$refs.coverInput.click()"
              class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] dark:hover:bg-white/[0.08] border border-slate-200 dark:border-white/[0.08] text-xs font-semibold text-slate-700 dark:text-white transition-colors"
              data-test="btn-change-cover"
            >
              {{ form.cover_image ? 'Change Cover' : 'Upload Cover' }}
            </button>
            <button
              v-if="form.cover_image"
              type="button"
              @click="openPreview(form.cover_image, 'Barber Profile Cover Banner')"
              class="px-3.5 py-1.5 rounded-xl bg-gold/10 hover:bg-gold/20 border border-gold/20 text-xs font-semibold text-amber-800 dark:text-gold transition-colors flex items-center gap-1.5 font-bold"
              data-test="btn-view-cover-preview"
            >
              <EyeIcon class="h-3.5 w-3.5" />
              <span>View Cover</span>
            </button>
          </div>
        </div>

        <!-- Banner Preview Container -->
        <div
          class="relative w-full h-36 sm:h-44 rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#0B0F13] overflow-hidden group cursor-pointer flex items-center justify-center transition-colors hover:border-gold/40"
          @click="$refs.coverInput.click()"
          data-test="cover-preview-card"
        >
          <img
            v-if="form.cover_image"
            :src="form.cover_image"
            @error="form.cover_image = null"
            alt="Profile Cover Banner"
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            data-test="cover-image-preview"
          />
          <div v-else class="flex flex-col items-center justify-center gap-2 text-slate-500">
            <div class="h-10 w-10 rounded-xl bg-white/80 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] flex items-center justify-center">
              <PhotoIcon class="h-5 w-5 text-slate-500 dark:text-slate-400" />
            </div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Click to select a showcase cover banner</p>
          </div>

          <!-- Hover Overlay -->
          <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-2 transition-opacity">
            <CameraIcon class="h-5 w-5 text-white" />
            <span class="text-xs font-semibold text-white">Change Cover Banner</span>
          </div>
        </div>

        <input
          ref="coverInput"
          type="file"
          class="hidden"
          accept="image/*"
          data-test="input-cover-file"
          @change="handleCoverChange"
        />
      </div>

      <!-- Identity Grid -->
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold ml-1 mb-2">
            Full Name <span class="text-gold">*</span>
          </label>
          <input
            v-model="form.name"
            class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 transition-colors"
            placeholder="e.g. Obo Shadow"
            required
            data-test="input-name"
          />
        </div>

        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold ml-1 mb-2">
            Email Address <span class="text-gold">*</span>
          </label>
          <input
            v-model="form.email"
            type="email"
            class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 transition-colors"
            placeholder="barber@candycutz.com"
            required
            data-test="input-email"
          />
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold ml-1 mb-2">
            Phone Number
          </label>
          <input
            v-model="form.phone"
            class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 transition-colors"
            placeholder="+234 800 123 4567"
          />
        </div>

        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold ml-1 mb-2">
            Instagram Handle
          </label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-500">@</span>
            <input
              v-model="form.instagram_url"
              class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] pl-8 pr-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 transition-colors"
              placeholder="obo_cuts"
            />
          </div>
        </div>
      </div>

      <div>
        <label class="block text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold ml-1 mb-2">
          Barber Bio & Philosophy
        </label>
        <textarea
          v-model="form.bio"
          rows="4"
          class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 transition-colors leading-relaxed resize-none"
          placeholder="Brief intro for your clientele..."
        ></textarea>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div
      v-if="previewModal.open"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4"
      @click.self="closePreview"
      @keydown.esc="closePreview"
      tabindex="0"
      data-test="dashboard-image-preview-modal"
    >
      <div class="relative max-w-3xl w-full flex flex-col items-center">
        <button
          type="button"
          @click="closePreview"
          class="absolute -top-12 right-0 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition-colors"
          data-test="btn-close-preview"
          title="Close Preview"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>

        <img
          :src="previewModal.url"
          :alt="previewModal.title"
          class="max-h-[75vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl border border-white/10"
        />

        <div class="mt-3 text-center">
          <p class="text-sm font-bold text-white">{{ previewModal.title }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { UserIcon, CameraIcon, PhotoIcon, EyeIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['avatar-selected', 'cover-selected']);

const fileInput = ref(null);
const coverInput = ref(null);

const previewModal = ref({
  open: false,
  url: '',
  title: '',
});

function handleFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    emit('avatar-selected', file);
  }
}

function handleCoverChange(event) {
  const file = event.target.files[0];
  if (file) {
    emit('cover-selected', file);
  }
}

function openPreview(url, title) {
  if (!url) return;
  previewModal.value = {
    open: true,
    url,
    title,
  };
}

function closePreview() {
  previewModal.value.open = false;
}
</script>
