<template>
  <div class="space-y-6">
    <!-- Header / Context -->
    <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
      <div>
        <h3 class="text-sm font-bold text-white">Hero Configuration</h3>
        <p class="text-xs text-white/50">Customise title, subtitle, cover photo, and overlay contrast for {{ pageLabel }}.</p>
      </div>
      <span class="inline-flex items-center rounded-lg border border-white/10 bg-white/[0.04] px-2.5 py-1 text-xs font-mono font-medium text-white/70">
        {{ pageLabel }}
      </span>
    </div>

    <!-- Hero Title -->
    <div class="space-y-2">
      <label class="text-xs font-bold uppercase tracking-wider text-white/60">Headline Title</label>
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
      <label class="text-xs font-bold uppercase tracking-wider text-white/60">Supporting Subtitle</label>
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
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">Cover Image</label>
        <span v-if="previewImage" class="text-[11px] text-emerald-400 font-mono">Custom photo active</span>
        <span v-else class="text-[11px] text-white/40 font-mono">Using default gradient banner</span>
      </div>

      <div v-if="previewImage" class="relative rounded-xl border border-white/10 bg-[#0B0F13] p-2 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <img
            :src="previewImage"
            alt="Hero thumbnail"
            class="h-12 w-20 rounded-lg object-cover border border-white/10"
          />
          <div>
            <div class="text-xs font-semibold text-white truncate max-w-[180px]">
              {{ selectedFile ? selectedFile.name : 'Uploaded Cover' }}
            </div>
            <div class="text-[10px] text-white/40 font-mono">
              {{ selectedFile ? `${(selectedFile.size / 1024).toFixed(1)} KB` : 'Active on site' }}
            </div>
          </div>
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

      <div
        v-else
        @click="triggerFileInput"
        class="border-2 border-dashed border-white/10 hover:border-[#FF6700]/50 rounded-xl p-5 flex flex-col items-center justify-center cursor-pointer transition-colors bg-[#0B0F13]/40 hover:bg-[#0B0F13]/70 group text-center"
      >
        <div class="h-9 w-9 rounded-full bg-white/[0.04] group-hover:bg-[#FF6700]/10 border border-white/10 flex items-center justify-center text-white/60 group-hover:text-[#FF6700] mb-2 transition-colors">
          <PhotoIcon class="h-5 w-5" />
        </div>
        <div class="text-xs font-semibold text-white">Click to upload cover image</div>
        <div class="text-[11px] text-white/40 mt-0.5">JPEG, PNG, WebP up to 5MB</div>
      </div>

      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        class="hidden"
        @change="handleFileChange"
      />
    </div>

    <!-- Overlay Color Picker -->
    <div class="space-y-2 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          {{ isHome ? 'Hero Overlay Tone' : 'Overlay Color Tint' }}
        </label>
        <span class="inline-flex items-center gap-1.5 font-mono text-[11px] text-white/70">
          <span class="h-2 w-2 rounded-full border border-white/30" :style="{ backgroundColor: form.color }"></span>
          <span>{{ form.color }}</span>
        </span>
      </div>

      <div class="flex items-center gap-2">
        <div class="relative h-8 w-8 rounded-lg overflow-hidden border border-white/20 cursor-pointer shadow-sm shrink-0">
          <input
            v-model="form.color"
            type="color"
            aria-label="Pick overlay color"
            class="absolute -inset-2 w-12 h-12 cursor-pointer bg-transparent border-0"
          />
        </div>
        <input
          v-model="form.color"
          type="text"
          maxlength="7"
          placeholder="#D4AF37"
          aria-label="Overlay hex color"
          class="w-24 rounded-lg border border-white/[0.08] bg-[#0B0F13]/80 px-2.5 py-1 text-xs font-mono font-bold text-white uppercase focus:border-[#FF6700]/50 focus:outline-none"
        />
        <div class="flex flex-wrap items-center gap-1.5 ml-2">
          <button
            v-for="preset in (isHome ? homePresets : darkPresets)"
            :key="preset.value"
            type="button"
            @click="form.color = preset.value"
            class="h-6 w-6 rounded-md border border-white/20 hover:scale-110 transition-transform"
            :style="{ backgroundColor: preset.value }"
            :title="preset.label"
          ></button>
        </div>
      </div>
    </div>

    <!-- Overlay Opacity Slider -->
    <div class="space-y-3 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          {{ isHome ? 'Gold Overlay Intensity' : 'Dark Overlay Contrast' }}
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
        step="1"
        aria-label="Overlay opacity slider"
        class="w-full h-2 rounded-lg bg-white/10 appearance-none cursor-pointer accent-[#FF6700]"
      />

      <div class="flex items-center justify-between text-[10px] text-white/40 font-mono">
        <span>0% (Transparent)</span>
        <span>Default: {{ isHome ? '45%' : '76%' }}</span>
        <span>100% (Solid)</span>
      </div>
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
import { ref, computed } from 'vue';
import { PhotoIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { DEFAULT_OVERLAY_PRESETS } from '@/core/utils/heroOverlay';

const props = defineProps({
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

const isHome = computed(() => (props.pageLabel || '').toLowerCase() === 'home');
const homePresets = DEFAULT_OVERLAY_PRESETS;
const darkPresets = [
  { label: 'Obsidian', value: '#111827' },
  { label: 'Dark Onyx', value: '#0D0D0D' },
  { label: 'Luxury Gold', value: '#D4AF37' },
  { label: 'Emerald', value: '#059669' }
];

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
