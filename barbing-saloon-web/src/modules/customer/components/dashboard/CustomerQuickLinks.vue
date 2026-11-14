<template>
  <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 shadow-sm flex flex-col justify-between">
    <div>
      <div class="pb-4 border-b border-[var(--color-border-subtle)]">
        <h2 class="font-display text-lg sm:text-xl text-[var(--color-text-primary)] flex items-center gap-2">
          <SparklesIcon class="w-5 h-5 text-[var(--color-accent)]" />
          <span>Quick <strong class="text-[var(--color-accent)] font-semibold">Shortcuts</strong></span>
        </h2>
      </div>

      <div class="mt-5 space-y-2.5">
        <RouterLink 
          v-for="link in quickLinks" 
          :key="link.to" 
          :to="link.to" 
          class="group flex items-center justify-between rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-3 transition-all duration-300 hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-tertiary)]"
        >
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-secondary)] group-hover:text-[var(--color-accent)] group-hover:border-[var(--color-accent)]/40 transition-colors shrink-0">
              <component :is="link.icon" class="h-4.5 w-4.5" />
            </div>
            <div class="min-w-0">
              <p class="text-xs sm:text-sm font-semibold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors truncate">
                {{ link.title }}
              </p>
              <p class="text-[11px] text-[var(--color-text-muted)] truncate">
                {{ link.description }}
              </p>
            </div>
          </div>
          <ChevronRightIcon class="h-4 w-4 text-[var(--color-text-muted)] group-hover:text-[var(--color-accent)] group-hover:translate-x-1 transition-all shrink-0" />
        </RouterLink>
      </div>
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import {
  SparklesIcon,
  ChevronRightIcon,
  CalendarDaysIcon,
  UserCircleIcon,
  HeartIcon,
  QrCodeIcon,
  UserGroupIcon,
} from '@heroicons/vue/24/outline';

const quickLinks = [
  { title: 'Our Barbers', description: 'Meet our master stylists & profiles', to: '/customer/dashboard/barbers', icon: UserGroupIcon },
  { title: 'My Bookings', description: 'Manage and review scheduled appointments', to: '/customer/dashboard/bookings', icon: CalendarDaysIcon },
  { title: 'My Check-In Codes', description: 'Access QR and verification check-in codes', to: '/customer/dashboard/my-codes', icon: QrCodeIcon },
  { title: 'Profile Settings', description: 'Update profile details and preferences', to: '/customer/dashboard/profile', icon: UserCircleIcon },
  { title: 'Saved Styles', description: 'View your favorite styles and haircuts', to: '/customer/dashboard/wishlist', icon: HeartIcon },
];
</script>

