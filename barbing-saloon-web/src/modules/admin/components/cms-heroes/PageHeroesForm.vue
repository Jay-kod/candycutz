<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-6">
    <div class="border-b border-white/[0.06] pb-4">
      <h3 class="text-base font-bold text-white">Hero Parameters &amp; Media</h3>
      <p class="mt-0.5 text-xs text-white/50">
        Adjust the headline, narrative subtitle, cover photography, and overlay opacity for the {{ pageLabel }} page.
      </p>
    </div>

    <!-- Hero Title -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          Hero Title <span class="text-[#FF6700]">*</span>
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (form.title || '').length }}/180</span>
      </div>
      <input
        v-model="form.title"
        type="text"
        maxlength="180"
        required
        placeholder="e.g. Precision Cuts. Made for You."
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
      />
    </div>

    <!-- Hero Subtitle -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          Hero Subtitle <span class="text-[#FF6700]">*</span>
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (form.subtitle || '').length }}/1000</span>
      </div>
      <textarea
        v-model="form.subtitle"
        rows="3"
        maxlength="1000"
        placeholder="e.g. Expert barbers. Fresh fades. A better kind of barbershop experience."
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-3.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 resize-none leading-relaxed"
      ></textarea>
    </div>

    <!-- Hero Cover Image Upload & Thumbnail -->
    <div class="space-y-3 pt-2 border-t border-white/[0.06]">
      <label class="text-xs font-bold uppercase tracking-wider text-white/60 block">
        Hero Cover Photography
      </label>

      <!-- If Image Present (Uploaded or Existing) -->
      <div v-if="previewImage" class="flex items-center gap-4 p-3.5 rounded-xl border border-white/[0.08] bg-[#0B0F13]">
        <div class="relative h-16 w-24 rounded-lg overflow-hidden border border-white/10 shrink-0 bg-black">
          <img :src="previewImage" alt="Cover preview" class="h-full w-full object-cover" />
          <div class="absolute inset-0 bg-black/20"></div>
        </div>

        <div class="space-y-1 min-w-0 flex-1">
          <p class="text-xs font-bold text-white truncate">
            {{ selectedFile ? selectedFile.name : (form.image ? 'Saved Cover Photo' : 'Active Image') }}
          </p>
          <p class="text-[10px] text-white/40">
            {{ selectedFile ? `${(selectedFile.size / 1024 / 1024).toFixed(2)} MB • Ready to save` : 'Active on live storefront' }}
          </p>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="triggerFileInput"
            class="rounded-lg border border-white/10 bg-white/[0.04] px-2.5 py-1.5 text-[11px] font-semibold text-white/70 hover:bg-white/[0.08] hover:text-white transition-colors"
          >
            Replace
          </button>
          <button
            type="button"
            @click="$emit('clear-image')"
            class="rounded-lg border border-rose-500/20 bg-rose-500/10 p-1.5 text-rose-400 hover:bg-rose-500/20 transition-colors"
            title="Remove cover photo"
          >
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Upload Zone when No Image Selected -->
      <div
        v-else
        @click="triggerFileInput"
        class="group cursor-pointer rounded-xl border border-dashed border-white/15 bg-white/[0.02] p-6 text-center transition-all hover:border-[#FF6700]/50 hover:bg-[#FF6700]/[0.02]"
      >
        <PhotoIcon class="mx-auto h-8 w-8 text-white/30 group-hover:text-[#FF6700] transition-colors" />
        <p class="mt-2 text-xs font-bold text-white group-hover:text-[#FF6700] transition-colors">
          Upload Cover Image
        </p>
        <p class="mt-0.5 text-[11px] text-white/40">
          JPG, PNG, or WEBP up to 5 MB
        </p>
      </div>

      <!-- Hidden Real File Input -->
      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
        @change="handleFileChange"
      />
    </div>

    <!-- Overlay Opacity Slider -->
    <div class="space-y-3 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          Dark Overlay Contrast
        </label>
        <span class="inline-flex items-center rounded-md border border-[#FF6700]/30 bg-[#FF6700]/10 px-2 py-0.5 text-[11px] font-bold font-mono text-[#FF6700]">
          {{ form.opacity }}%
        </span>
      </div>

      <input
        v-model.number="form.opacity"
        type="range"
        min="0"
        max="100"
        class="w-full accent-[#FF6700] cursor-pointer"
      />

      <p class="text-[11px] text-white/40 leading-relaxed">
        Controls the darkness of the obsidian tint over the photography to guarantee maximum legibility of white typography.
      </p>
    </div>

    <!-- Reset Actions -->
    <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between">
      <button
        type="button"
        @click="$emit('reset-text')"
        class="text-xs font-semibold text-white/50 hover:text-white transition-colors"
      >
        Reset Copy to Defaults
      </button>

      <button
        v-if="previewImage"
        type="button"
        @click="$emit('clear-image')"
        class="text-xs font-semibold text-rose-400 hover:text-rose-300 transition-colors"
      >
        Remove Cover Image
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { PhotoIcon, TrashIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true
  },
  selectedFile: {
    type: Object,
    default: null
  },
  previewImage: {
    type: String,
    default: ''
  },
  pageLabel: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['select-image', 'clear-image', 'reset-text']);

const fileInputRef = ref(null);

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const handleFileChange = (e) => {
  const file = e.target.files?.[0] || null;
  if (file) {
    emit('select-image', file);
  }
};
</script>
