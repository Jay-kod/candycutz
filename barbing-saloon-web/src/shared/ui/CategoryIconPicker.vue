<template>
  <div class="space-y-2">
    <!-- Selected Icon Trigger / Preview -->
    <div class="flex items-center gap-3">
      <button
        type="button"
        @click="openPicker"
        class="flex-1 flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-left transition-all group"
        :class="[
          modelValue
            ? 'border-theme-border bg-theme-surface hover:border-theme-text-muted/60'
            : 'border-dashed border-theme-border bg-theme-surface-subtle hover:border-theme-text-muted hover:bg-theme-surface',
          themeFocusClass
        ]"
      >
        <div class="flex items-center gap-3.5 min-w-0">
          <div
            class="flex size-11 shrink-0 items-center justify-center rounded-xl transition-transform group-hover:scale-105 shadow-sm"
            :class="modelValue ? themeBadgeClass : 'bg-theme-border/30 text-theme-text-muted'"
          >
            <CategoryIcon :name="modelValue || 'sparkles'" custom-class="size-5" />
          </div>
          <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-theme-text-muted">
              {{ modelValue ? 'Selected Icon' : 'No icon selected' }}
            </p>
            <p class="truncate text-sm font-bold text-theme-text capitalize">
              {{ currentIconLabel || 'Click to choose a real icon…' }}
            </p>
          </div>
        </div>

        <span
          class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold transition-colors"
          :class="themeButtonClass"
        >
          {{ modelValue ? 'Change' : 'Browse icons' }}
        </span>
      </button>

      <button
        v-if="modelValue"
        type="button"
        @click="clearIcon"
        title="Remove icon"
        aria-label="Remove selected icon"
        class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-theme-border bg-theme-surface text-theme-text-muted hover:bg-theme-surface-subtle hover:text-rose-400 transition"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Modal Dialog Picker -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        role="dialog"
        aria-modal="true"
        aria-label="Select Category Icon"
        class="fixed inset-0 z-[120] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 sm:p-6"
        @click.self="closePicker"
      >
        <div
          class="w-full max-w-2xl max-h-[85vh] rounded-3xl border border-theme-border bg-theme-surface shadow-2xl flex flex-col overflow-hidden animate-fade-in"
        >
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-theme-border p-5 sm:p-6">
            <div>
              <h3 class="font-display text-xl font-bold text-theme-text">Select Category Icon</h3>
              <p class="mt-1 text-xs sm:text-sm text-theme-text-muted">
                Pick a real icon that customers and staff will see for this category.
              </p>
            </div>
            <button
              type="button"
              aria-label="Close icon picker"
              @click="closePicker"
              class="flex size-10 items-center justify-center rounded-xl border border-theme-border bg-theme-surface-subtle text-theme-text-muted hover:text-theme-text transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Controls: Search & Category Pills -->
          <div class="space-y-3 border-b border-theme-border bg-theme-surface-subtle/50 p-4 sm:p-6">
            <!-- Search Bar -->
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="absolute left-4 top-1/2 -translate-y-1/2 size-4 text-theme-text-muted">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
              </svg>
              <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search real icons (e.g. scissors, crown, camera, sparkles, flame)..."
                class="w-full rounded-xl border border-theme-border bg-theme-surface py-2.5 pl-11 pr-14 text-sm text-theme-text placeholder:text-theme-text-muted outline-none transition"
                :class="themeFocusClass"
              />
              <button
                v-if="searchQuery"
                type="button"
                @click="searchQuery = ''"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-theme-text-muted hover:text-theme-text"
              >
                Clear
              </button>
            </div>

            <!-- Group Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
              <button
                v-for="group in ICON_GROUPS"
                :key="group.key"
                type="button"
                @click="selectedGroup = group.key"
                class="shrink-0 rounded-lg px-3 py-1.5 text-xs font-bold transition"
                :class="selectedGroup === group.key
                  ? themeSolidButtonClass
                  : 'bg-theme-surface text-theme-text-muted border border-theme-border hover:bg-theme-surface-subtle hover:text-theme-text'"
              >
                {{ group.name }}
              </button>
            </div>
          </div>

          <!-- Icons Grid -->
          <div class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar">
            <div v-if="filteredIcons.length === 0" class="py-12 text-center">
              <p class="text-sm font-semibold text-theme-text-muted">No icons matching "{{ searchQuery }}"</p>
              <button
                type="button"
                @click="searchQuery = ''; selectedGroup = 'all'"
                class="mt-3 text-xs font-bold hover:underline"
                :class="themeTextClass"
              >
                Reset filters
              </button>
            </div>

            <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2.5">
              <button
                v-for="item in filteredIcons"
                :key="item.key"
                type="button"
                @click="selectIcon(item.key)"
                class="group relative flex flex-col items-center justify-center gap-2 rounded-2xl border p-3.5 text-center transition-all duration-200"
                :class="isSelected(item.key)
                  ? themeActiveTileClass
                  : themeIdleTileClass"
              >
                <div
                  class="flex size-11 items-center justify-center rounded-xl transition-transform group-hover:scale-110"
                  :class="isSelected(item.key) ? themeSolidButtonClass : themeIdleIconBoxClass"
                >
                  <CategoryIcon :name="item.key" custom-class="size-6" />
                </div>
                <span class="truncate max-w-full text-[11px] font-semibold text-theme-text capitalize">
                  {{ item.name }}
                </span>

                <!-- Check badge if selected -->
                <span
                  v-if="isSelected(item.key)"
                  class="absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full text-[10px] shadow"
                  :class="themeSolidButtonClass"
                >
                  ✓
                </span>
              </button>
            </div>
          </div>

          <!-- Footer with Preview & Actions -->
          <div class="flex items-center justify-between border-t border-theme-border bg-theme-surface-subtle/50 p-4 sm:p-5">
            <div class="flex items-center gap-3">
              <span class="text-xs text-theme-text-muted">Preview:</span>
              <div class="flex items-center gap-2 rounded-xl border border-theme-border bg-theme-surface px-3 py-1.5">
                <CategoryIcon :name="modelValue || 'sparkles'" custom-class="size-4" :class="themeTextClass" />
                <span class="text-xs font-bold text-theme-text capitalize">{{ currentIconLabel || 'Sparkles' }}</span>
              </div>
              <span class="hidden sm:inline text-[11px] text-theme-text-muted">
                {{ filteredIcons.length }} {{ filteredIcons.length === 1 ? 'icon' : 'icons' }}
              </span>
            </div>

            <button
              type="button"
              @click="closePicker"
              class="rounded-xl px-5 py-2.5 text-xs sm:text-sm font-bold transition shadow-sm"
              :class="themeSolidButtonClass"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
import CategoryIcon from './CategoryIcon.vue';
import { ICON_GROUPS, ICONS_CATALOG, getCategoryIconLabel } from './categoryIcons';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  colorTheme: {
    type: String,
    default: 'admin'
  }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const selectedGroup = ref('all');
const searchInputRef = ref(null);

const normalizedValue = computed(() =>
  props.modelValue ? String(props.modelValue).trim().toLowerCase().replace(/_/g, '-') : ''
);

const currentIconLabel = computed(() => getCategoryIconLabel(props.modelValue));

const filteredIcons = computed(() => {
  let list = ICONS_CATALOG;

  if (selectedGroup.value !== 'all') {
    list = list.filter(i => i.group === selectedGroup.value);
  }

  const query = searchQuery.value.trim().toLowerCase();
  if (query) {
    list = list.filter(i =>
      i.name.toLowerCase().includes(query) ||
      i.key.toLowerCase().includes(query) ||
      i.tags.toLowerCase().includes(query)
    );
  }

  return list;
});

function isSelected(key) {
  return normalizedValue.value === key;
}

function openPicker() {
  isOpen.value = true;
}

function closePicker() {
  isOpen.value = false;
}

function selectIcon(key) {
  emit('update:modelValue', key);
  isOpen.value = false;
}

function clearIcon() {
  emit('update:modelValue', '');
}

function handleKeydown(event) {
  if (event.key === 'Escape' && isOpen.value) {
    event.preventDefault();
    closePicker();
  }
}

watch(isOpen, async (open) => {
  if (typeof window === 'undefined') return;
  if (open) {
    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', handleKeydown);
    await nextTick();
    searchInputRef.value?.focus();
  } else {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', handleKeydown);
  }
});

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', handleKeydown);
  }
});

const isGold = computed(() => props.colorTheme === 'gold');

const themeBadgeClass = computed(() =>
  isGold.value
    ? 'bg-gold/15 text-gold border border-gold/30'
    : 'bg-admin/15 text-admin border border-admin/30'
);

const themeFocusClass = computed(() =>
  isGold.value ? 'focus:border-gold' : 'focus:border-admin'
);

const themeButtonClass = computed(() =>
  isGold.value
    ? 'bg-gold/15 text-gold hover:bg-gold/25'
    : 'bg-admin/15 text-admin hover:bg-admin/25'
);

const themeSolidButtonClass = computed(() =>
  isGold.value
    ? 'bg-gold text-obsidian hover:bg-amber-400'
    : 'bg-admin text-white hover:bg-admin-light'
);

const themeTextClass = computed(() =>
  isGold.value ? 'text-gold' : 'text-admin'
);

const themeActiveTileClass = computed(() =>
  isGold.value
    ? 'border-gold bg-gold/15 text-gold shadow-md ring-2 ring-gold/30'
    : 'border-admin bg-admin/15 text-admin shadow-md ring-2 ring-admin/30'
);

const themeIdleTileClass = computed(() =>
  isGold.value
    ? 'border-theme-border bg-theme-surface hover:border-gold/50 hover:bg-theme-surface-subtle text-theme-text'
    : 'border-theme-border bg-theme-surface hover:border-admin/50 hover:bg-theme-surface-subtle text-theme-text'
);

const themeIdleIconBoxClass = computed(() =>
  isGold.value
    ? 'bg-theme-surface-subtle text-theme-text group-hover:text-gold'
    : 'bg-theme-surface-subtle text-theme-text group-hover:text-admin'
);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(150, 150, 150, 0.2);
  border-radius: 9999px;
}
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.animate-fade-in {
  animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}
</style>
