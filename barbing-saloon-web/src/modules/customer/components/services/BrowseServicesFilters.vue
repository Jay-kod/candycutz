<template>
  <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 shadow-sm space-y-4 transition-colors">
    <!-- Controls Row: Search & Sort -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
      <!-- Search Form with Button -->
      <form @submit.prevent="handleSearchSubmit" class="relative flex-1 flex items-center">
        <!-- Search icon button on left -->
        <button
          type="submit"
          @click="handleSearchSubmit"
          class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[var(--color-text-muted)] hover:text-[var(--color-accent)] transition-colors focus:outline-none"
          title="Search catalogue"
          aria-label="Submit search"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </button>

        <!-- Input -->
        <input
          ref="searchInputRef"
          type="text"
          :value="searchQuery"
          @input="handleInput"
          placeholder="Search haircuts, beard trims, treatments..."
          class="w-full pl-10 pr-24 h-11 sm:h-12 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] text-sm text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] focus:outline-none focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[var(--color-accent)]/20 transition-all"
        />

        <!-- Right actions: Clear button and prominent Search button -->
        <div class="absolute inset-y-0 right-1.5 flex items-center gap-1.5">
          <button
            v-if="searchQuery"
            type="button"
            @click="handleClear"
            class="flex items-center justify-center h-8 w-8 rounded-lg text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-bg-secondary)] transition-colors"
            title="Clear search"
            aria-label="Clear search query"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
              <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
          </button>

          <button
            type="submit"
            @click="handleSearchSubmit"
            class="inline-flex items-center justify-center px-3.5 h-8 sm:h-9 rounded-lg bg-[var(--color-accent)] text-[var(--color-accent-foreground)] font-bold text-xs shadow-sm hover:opacity-90 active:scale-95 transition-all focus:outline-none shrink-0"
            title="Search"
          >
            Search
          </button>
        </div>
      </form>

      <!-- Sort Selection -->
      <div class="flex items-center justify-between sm:justify-end gap-2.5 shrink-0">
        <label for="sortOrderSelect" class="text-xs font-semibold text-[var(--color-text-secondary)] whitespace-nowrap">Sort by:</label>
        <div class="relative flex-1 sm:flex-initial">
          <select
            id="sortOrderSelect"
            :value="sortOrder"
            @change="$emit('update:sortOrder', $event.target.value)"
            class="w-full sm:w-auto appearance-none pl-3.5 pr-9 h-11 sm:h-12 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-primary)] text-xs font-semibold text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)] focus:ring-2 focus:ring-[var(--color-accent)]/20 cursor-pointer transition-all"
          >
            <option value="featured">Featured / Default</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
            <option value="duration-short">Duration: Shortest</option>
            <option value="duration-long">Duration: Longest</option>
            <option value="name-az">Name: A to Z</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--color-text-muted)]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Filter Tabs with Horizontal Scroll on Mobile and Flex Wrap on Larger Screens -->
    <div class="relative -mx-4 px-4 sm:mx-0 sm:px-0">
      <div class="category-scroll-container flex items-center overflow-x-auto sm:flex-wrap gap-2 sm:gap-2.5 pb-1 sm:pb-0 scroll-smooth">
        <button
          v-for="cat in categories"
          :key="cat.name"
          type="button"
          @click="$emit('category-change', cat.name)"
          :class="[
            'category-pill shrink-0 inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold transition-all focus:outline-none min-h-[38px] sm:min-h-[42px] select-none',
            activeCategory === cat.name
              ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] shadow-md shadow-[var(--color-accent)]/25 font-bold'
              : 'bg-[var(--color-bg-primary)] text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/50 border border-[var(--color-border)] hover:bg-[var(--color-bg-tertiary)]'
          ]"
        >
          <span>{{ cat.name }}</span>
          <span
            :class="[
              'px-1.5 py-0.5 rounded-full text-[10px] font-bold min-w-[1.25rem] text-center transition-colors',
              activeCategory === cat.name
                ? 'bg-black/20 text-[var(--color-accent-foreground)]'
                : 'bg-[var(--color-bg-secondary)] border border-[var(--color-border-subtle)] text-[var(--color-text-muted)]'
            ]"
          >
            {{ cat.count }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  activeCategory: {
    type: String,
    default: 'All'
  },
  searchQuery: {
    type: String,
    default: ''
  },
  sortOrder: {
    type: String,
    default: 'featured'
  }
});

const emit = defineEmits(['update:searchQuery', 'update:sortOrder', 'category-change', 'search']);

const searchInputRef = ref(null);

function handleInput(e) {
  emit('update:searchQuery', e.target.value);
}

function handleSearchSubmit() {
  emit('search', props.searchQuery);
  if (searchInputRef.value) {
    searchInputRef.value.blur();
  }
}

function handleClear() {
  emit('update:searchQuery', '');
  emit('search', '');
}
</script>

<style scoped>
.category-scroll-container {
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.category-scroll-container::-webkit-scrollbar {
  display: none;
}

.category-pill:active {
  transform: scale(0.97);
}
</style>
