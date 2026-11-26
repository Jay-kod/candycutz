<template>
  <div
    class="relative overflow-hidden rounded-xl md:rounded-3xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-secondary)] md:bg-gradient-to-br md:from-amber-500/10 md:via-amber-400/5 md:to-white md:dark:from-[#12161C] md:dark:via-[#0E1217] md:dark:to-[#12161C] p-4 sm:p-6 md:p-8 lg:p-10 shadow-none md:shadow-2xl"
  >
    <div
      class="hidden md:block absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"
    ></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6">
      <div class="flex items-start sm:items-center gap-3.5 sm:gap-5 md:gap-6 min-w-0">
        <!-- Avatar Trigger -->
        <button
          type="button"
          class="relative group cursor-pointer shrink-0 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] rounded-xl md:rounded-2xl"
          aria-label="Change profile photo"
          @click="$emit('pick-avatar')"
        >
          <div
            class="h-16 w-16 sm:h-20 sm:w-20 md:h-24 md:w-24 rounded-xl md:rounded-2xl border-2 border-[var(--color-accent)]/40 overflow-hidden shadow-sm md:shadow-lg transition-all group-hover:border-[var(--color-accent)] bg-slate-100 dark:bg-black/40"
          >
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              @error="$emit('avatar-error')"
              alt="Avatar"
              class="h-full w-full object-cover"
            />
            <div v-else class="h-full w-full bg-[var(--color-accent)]/10 flex items-center justify-center">
              <span class="text-xl sm:text-2xl font-display font-bold text-[var(--color-accent)]">
                {{ initials }}
              </span>
            </div>
          </div>

          <div
            class="hidden md:flex absolute inset-0 rounded-2xl bg-black/60 items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
          >
            <CameraIcon class="w-6 h-6 text-white" />
          </div>

          <div
            class="absolute -bottom-1 -right-1 h-6 w-6 rounded-lg bg-[var(--color-accent)] flex items-center justify-center border border-white dark:border-black shadow-sm text-white"
          >
            <PencilSquareIcon class="w-3.5 h-3.5" />
          </div>
        </button>

        <div class="min-w-0 flex-1">
          <p
            class="text-[11px] sm:text-xs uppercase tracking-[0.2em] md:tracking-[0.3em] text-[var(--color-accent)] font-bold"
          >
            Personal Account
          </p>
          <h1
            class="mt-1 font-display text-2xl sm:text-3xl lg:text-4xl font-bold md:font-normal text-slate-900 dark:text-white leading-tight"
          >
            Account <span class="text-[var(--color-accent)] md:text-transparent md:bg-clip-text md:bg-gradient-to-r md:from-[var(--color-accent)] md:to-amber-200">Settings</span>
          </h1>
          <p
            class="mt-1.5 max-w-xl text-xs sm:text-sm text-[var(--color-text-secondary)] leading-relaxed text-left-forced"
          >
            Update your contact details, grooming preferences, and review your account activity.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CameraIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

defineProps({
  avatarPreview: { type: String, default: null },
  initials: { type: String, default: '?' },
});

defineEmits(['pick-avatar', 'avatar-error']);
</script>
