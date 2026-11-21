<template>
  <div class="relative z-10 shrink-0 mt-auto border-t border-[var(--color-border-subtle)] dark:border-white/[0.08] p-3">
    <!-- Expanded Profile Card -->
    <div
      v-if="!isCollapsed"
      class="rounded-xl border border-[var(--color-border-subtle)] dark:border-white/[0.06] bg-[var(--color-bg-tertiary)]/70 dark:bg-white/[0.02] p-2.5 transition-all duration-200 hover:border-amber-500/20"
    >
      <div class="flex items-center gap-3">
        <!-- User Avatar with Online Dot -->
        <RouterLink
          v-if="profileRoute"
          :to="profileRoute"
          class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full overflow-hidden border border-amber-500/30 group"
          title="View Settings / Profile"
        >
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
          <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold bg-amber-500/15 text-amber-500 dark:text-amber-400">
            {{ userInitials }}
          </div>
          <span class="absolute bottom-0 right-0 h-2 w-2 rounded-full ring-2 ring-[var(--color-bg-secondary)] dark:ring-[#121824] bg-emerald-500"></span>
        </RouterLink>
        <div
          v-else
          class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full overflow-hidden border border-amber-500/30"
        >
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
          <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold bg-amber-500/15 text-amber-500 dark:text-amber-400">
            {{ userInitials }}
          </div>
          <span class="absolute bottom-0 right-0 h-2 w-2 rounded-full ring-2 ring-[var(--color-bg-secondary)] dark:ring-[#121824] bg-emerald-500"></span>
        </div>

        <!-- User Information -->
        <div class="min-w-0 flex-1">
          <RouterLink
            v-if="profileRoute"
            :to="profileRoute"
            class="block truncate text-xs font-semibold text-[var(--color-text-primary)] hover:text-amber-500 dark:hover:text-amber-400 transition-colors"
          >
            {{ userName }}
          </RouterLink>
          <span v-else class="block truncate text-xs font-semibold text-[var(--color-text-primary)]">
            {{ userName }}
          </span>
          <p class="truncate text-[10.5px] text-[var(--color-text-muted)]">
            {{ userEmail || (portalName + ' Staff') }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-1 shrink-0">
          <RouterLink
            v-if="profileRoute"
            :to="profileRoute"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-bg-secondary)] dark:hover:bg-white/[0.08] transition-colors"
            title="Account Settings"
          >
            <Cog6ToothIcon class="h-4 w-4" />
          </RouterLink>
          <button
            type="button"
            @click="$emit('logout')"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-rose-500/80 hover:text-rose-500 hover:bg-rose-500/10 transition-colors"
            title="Sign Out"
          >
            <ArrowRightOnRectangleIcon class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Collapsed Profile & Sign Out -->
    <div v-else class="flex flex-col items-center gap-2">
      <RouterLink
        v-if="profileRoute"
        :to="profileRoute"
        class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full overflow-hidden border border-amber-500/30"
        :title="userName + ' - Profile'"
      >
        <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
        <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold bg-amber-500/15 text-amber-500 dark:text-amber-400">
          {{ userInitials }}
        </div>
        <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full ring-2 ring-[var(--color-bg-secondary)] dark:ring-[#121824] bg-emerald-500"></span>
      </RouterLink>
      <button
        type="button"
        @click="$emit('logout')"
        class="flex h-9 w-9 items-center justify-center rounded-xl text-rose-500/80 hover:text-rose-500 hover:bg-rose-500/10 transition-colors"
        title="Sign Out"
      >
        <ArrowRightOnRectangleIcon class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { Cog6ToothIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';

defineProps({
  portalName: { type: String, default: 'Portal' },
  profileRoute: { type: String, default: '' },
  isCollapsed: { type: Boolean, default: false },
  userAvatarUrl: { type: String, default: null },
  userInitials: { type: String, default: 'U' },
  userName: { type: String, default: 'User' },
  userEmail: { type: String, default: '' },
});

defineEmits(['logout']);
</script>

