<template>
  <div class="rounded-xl border border-white/10 bg-black/40 p-3 space-y-3 shadow-inner animate-fade-in">
    <!-- Compact Header -->
    <div class="flex items-center justify-between gap-2 pb-2 border-b border-white/[0.08]">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold uppercase tracking-wider text-admin">Preset Palette Manager</span>
        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-white/[0.05] text-white/70 border border-white/10">
          {{ localPresets.length }}
        </span>
      </div>

      <div class="flex items-center gap-1.5">
        <button
          type="button"
          @click="addNewPreset"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold text-admin border border-admin/30 bg-admin/10 hover:bg-admin/20 transition-colors"
          title="Add a new preset color"
        >
          <PlusIcon class="h-3 w-3" />
          <span>Add Preset Color</span>
        </button>

        <button
          type="button"
          @click="resetToDefaults"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium text-white/60 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 transition-colors"
          title="Restore standard 7 luxury presets"
        >
          <ArrowPathIcon class="h-3 w-3" />
          <span>Reset Defaults</span>
        </button>

        <button
          type="button"
          @click="$emit('close')"
          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold text-obsidian bg-admin hover:bg-admin-light shadow-sm transition-colors"
        >
          <CheckIcon class="h-3 w-3" />
          <span>Done</span>
        </button>
      </div>
    </div>

    <!-- Compact Presets List -->
    <div class="space-y-1.5 max-h-[240px] overflow-y-auto pr-1">
      <div
        v-for="(preset, index) in localPresets"
        :key="index"
        class="flex items-center gap-2 p-1.5 rounded-lg border border-white/[0.05] bg-white/[0.02] hover:border-white/10 transition-colors"
      >
        <!-- Color Picker Box -->
        <div 
          class="relative h-6 w-6 rounded-md overflow-hidden border border-white/20 shadow-inner shrink-0 cursor-pointer group"
          :title="`Pick color for ${preset.label}`"
        >
          <div 
            class="h-full w-full transition-transform group-hover:scale-110" 
            :style="{ backgroundColor: preset.value }"
          ></div>
          <input
            type="color"
            :value="safeHex(preset.value)"
            @input="updateColor(index, $event.target.value)"
            :aria-label="`Color picker for ${preset.label}`"
            class="absolute -inset-2 w-10 h-10 cursor-pointer opacity-0"
          />
        </div>

        <!-- Preset Label Input -->
        <div class="flex-1 min-w-[100px]">
          <input
            v-model="preset.label"
            type="text"
            maxlength="40"
            @input="emitUpdate"
            placeholder="Preset Name"
            :aria-label="`Preset ${index + 1} label`"
            class="w-full rounded-md border border-white/[0.08] bg-black/60 px-2 py-0.5 text-xs text-white placeholder:text-white/30 focus:border-admin/60 focus:outline-none focus:ring-1 focus:ring-admin/30"
          />
        </div>

        <!-- Hex Input -->
        <div class="w-20 shrink-0">
          <input
            :value="preset.value"
            @input="updateHex(index, $event.target.value)"
            type="text"
            maxlength="7"
            placeholder="#D4AF37"
            :aria-label="`Preset ${index + 1} hex color`"
            class="w-full rounded-md border border-white/[0.08] bg-black/60 px-1.5 py-0.5 text-center text-xs font-mono font-bold text-white uppercase focus:border-admin/60 focus:outline-none focus:ring-1 focus:ring-admin/30"
          />
        </div>

        <!-- Quick Apply Swatch Button -->
        <button
          type="button"
          @click="$emit('select-color', preset.value)"
          :class="[
            'px-2 py-0.5 rounded text-[10px] font-semibold border transition-all shrink-0',
            isActiveColor(preset.value)
              ? 'bg-admin/20 border-admin text-admin font-bold'
              : 'bg-white/[0.03] border-white/10 text-white/50 hover:text-white hover:bg-white/[0.08]'
          ]"
          :title="isActiveColor(preset.value) ? 'Currently active overlay tone' : 'Apply this color'"
        >
          {{ isActiveColor(preset.value) ? 'Active' : 'Apply' }}
        </button>

        <!-- Delete Preset Button -->
        <button
          type="button"
          @click="removePreset(index)"
          :disabled="localPresets.length <= 1"
          :class="[
            'p-1 rounded border transition-colors shrink-0',
            localPresets.length <= 1
              ? 'opacity-30 cursor-not-allowed border-white/5 text-white/30'
              : 'border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 hover:border-rose-500/40'
          ]"
          :title="localPresets.length <= 1 ? 'At least one preset is required' : 'Delete this preset'"
        >
          <TrashIcon class="h-3 w-3" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { 
  DEFAULT_OVERLAY_PRESETS, 
  parseOverlayPresets, 
  isValidHexColor,
  DEFAULT_OVERLAY_COLOR 
} from '@/core/utils/heroOverlay';
import { 
  PlusIcon, 
  TrashIcon, 
  ArrowPathIcon, 
  CheckIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  modelValue: {
    type: [Array, String],
    default: () => []
  },
  activeColor: {
    type: String,
    default: DEFAULT_OVERLAY_COLOR
  }
});

const emit = defineEmits(['update:modelValue', 'select-color', 'close']);

const localPresets = ref(parseOverlayPresets(props.modelValue));

watch(
  () => props.modelValue,
  (newVal) => {
    const parsed = parseOverlayPresets(newVal);
    if (JSON.stringify(parsed) !== JSON.stringify(localPresets.value)) {
      localPresets.value = parsed;
    }
  },
  { deep: true }
);

const emitUpdate = () => {
  emit('update:modelValue', localPresets.value);
};

const safeHex = (val) => {
  if (isValidHexColor(val)) return val;
  return DEFAULT_OVERLAY_COLOR;
};

const isActiveColor = (val) => {
  if (!val || !props.activeColor) return false;
  return val.trim().toLowerCase() === props.activeColor.trim().toLowerCase();
};

const updateColor = (index, hex) => {
  if (localPresets.value[index]) {
    localPresets.value[index].value = hex.toUpperCase();
    emitUpdate();
  }
};

const updateHex = (index, text) => {
  if (localPresets.value[index]) {
    let clean = text.trim();
    if (!clean.startsWith('#') && clean.length > 0) {
      clean = '#' + clean;
    }
    localPresets.value[index].value = clean.toUpperCase();
    emitUpdate();
  }
};

const addNewPreset = () => {
  const newIndex = localPresets.value.length + 1;
  const newColor = '#FF6700';
  localPresets.value.push({
    label: `Custom Tone ${newIndex}`,
    value: newColor
  });
  emitUpdate();
};

const removePreset = (index) => {
  if (localPresets.value.length > 1) {
    localPresets.value.splice(index, 1);
    emitUpdate();
  }
};

const resetToDefaults = () => {
  localPresets.value = JSON.parse(JSON.stringify(DEFAULT_OVERLAY_PRESETS));
  emitUpdate();
};
</script>
