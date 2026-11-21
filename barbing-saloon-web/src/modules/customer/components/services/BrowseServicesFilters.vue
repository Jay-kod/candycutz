<template>
  <div class="space-y-4">
    <!-- Controls Row: Search & Sort -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[var(--color-text-muted)]">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
          </svg>
        </div>
        <input
          type="text"
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          placeholder="Search haircuts, beard trims, treatments..."
          class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] text-sm text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] focus:outline-none focus:border-[var(--color-accent)] focus:ring-1 focus:ring-[var(--color-accent)] transition-all"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="$emit('update:searchQuery', '')"
          class="absolute inset-y-0 right-0 pr-3 flex items-center text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)]"
          title="Clear search"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
          </svg>
        </button>
      </div>

      <!-- Sort Selection -->
      <div class="flex items-center gap-2 shrink-0">
        <label for="sortOrderSelect" class="text-xs font-medium text-[var(--color-text-muted)] whitespace-nowrap">Sort by:</label>
        <div class="relative">
          <select
            id="sortOrderSelect"
            :value="sortOrder"
            @change="$emit('update:sortOrder', $event.target.value)"
            class="appearance-none pl-3.5 pr-8 py-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] text-xs font-semibold text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)] cursor-pointer transition-all"
          >
            <option value="featured">Featured / Default</option>
            <option value="price-low">Price: Low to High</option>
            <option value="price-high">Price: High to Low</option>
            <option value="duration-short">Duration: Shortest</option>
            <option value="duration-long">Duration: Longest</option>
            <option value="name-az">Name: A to Z</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--color-text-muted)]">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Filter Tabs with Dynamic Counts -->
    <div class="flex flex-wrap gap-2.5 pt-1">
      <button
        v-for="cat in categories"
        :key="cat.name"
        type="button"
        @click="$emit('category-change', cat.name)"
        :class="[
          'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all focus:outline-none',
          activeCategory === cat.name
            ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] shadow-md shadow-[var(--color-accent)]/20 scale-[1.02]'
            : 'bg-[var(--color-bg-secondary)] text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/40 border border-[var(--color-border)]'
        ]"
      >
        <span>{{ cat.name }}</span>
        <span
          :class="[
            'px-1.5 py-0.5 rounded-full text-[10px] font-bold',
            activeCategory === cat.name
              ? 'bg-black/20 text-[var(--color-accent-foreground)]'
              : 'bg-[var(--color-bg-tertiary)] text-[var(--color-text-muted)]'
          ]"
        >
          {{ cat.count }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
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

defineEmits(['update:searchQuery', 'update:sortOrder', 'category-change']);
</script>

