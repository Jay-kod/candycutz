<template>
  <section class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 shadow-xs space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div>
        <h2 class="text-sm font-bold text-[var(--color-text-primary)]">Saved Addresses</h2>
        <p class="text-[11px] text-[var(--color-text-secondary)]">
          Tap any saved place to set Pin 2 & load its route immediately.
        </p>
      </div>
      <span class="rounded-full bg-[var(--color-bg-tertiary)] px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[var(--color-text-muted)]">
        {{ isAuthenticated ? `${addresses.length} Saved` : 'Device Mode' }}
      </span>
    </div>

    <div v-if="!isAuthenticated" class="rounded-xl bg-[var(--color-bg-tertiary)] p-3 text-xs text-[var(--color-text-secondary)]">
      Edits save on this device.
      <RouterLink to="/customer/login" class="font-bold text-[var(--color-accent)] underline hover:opacity-80">Sign in</RouterLink>
      to sync saved addresses across devices.
    </div>

    <div v-else-if="!addresses.length" class="rounded-xl border border-dashed border-[var(--color-border)] p-3.5 text-center text-xs text-[var(--color-text-muted)]">
      No saved addresses yet. Add your address and pin above, then click Save Address.
    </div>

    <div v-else class="max-h-56 space-y-2 overflow-y-auto pr-0.5">
      <div
        v-for="addr in addresses"
        :key="addr.id"
        :class="[
          'flex items-center justify-between gap-2 rounded-xl border p-3 text-xs transition',
          activeId === addr.id
            ? 'border-[var(--color-accent)] bg-[var(--color-accent)]/10'
            : 'border-[var(--color-border)] bg-[var(--color-bg-primary)]',
        ]"
      >
        <button type="button" class="min-w-0 flex-1 text-left cursor-pointer" @click="$emit('select', addr)">
          <p class="flex flex-wrap items-center gap-1.5 font-bold text-[var(--color-text-primary)]">
            <span>{{ addr.title }}</span>
            <span v-if="addr.is_default" class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-300">
              Default
            </span>
            <span v-if="activeId === addr.id" class="rounded-full bg-[var(--color-accent)]/20 px-2 py-0.5 text-[10px] font-semibold text-[var(--color-accent)]">
              Active
            </span>
          </p>
          <p class="mt-0.5 line-clamp-2 sm:truncate text-[var(--color-text-secondary)]">{{ addr.formatted || addr.street }}</p>
          <p v-if="addr.latitude && addr.longitude" class="mt-0.5 font-mono text-[10px] text-[var(--color-text-muted)]">
            Pin: {{ addr.latitude }}, {{ addr.longitude }}
          </p>
        </button>

        <div class="flex shrink-0 items-center gap-1">
          <button
            type="button"
            class="rounded-lg px-2 py-1 text-[11px] font-semibold text-[var(--color-accent)] hover:bg-[var(--color-accent)]/10 cursor-pointer"
            @click="$emit('select', addr)"
          >
            Edit
          </button>
          <button
            type="button"
            class="rounded-lg px-2 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-500/10 cursor-pointer"
            @click="$emit('remove', addr.id)"
          >
            Remove
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  isAuthenticated: { type: Boolean, default: false },
  addresses: { type: Array, default: () => [] },
  activeId: { type: Number, default: null },
})

defineEmits(['select', 'remove'])
</script>
