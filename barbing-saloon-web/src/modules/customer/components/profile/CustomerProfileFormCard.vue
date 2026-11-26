<template>
  <form
    class="rounded-xl md:rounded-3xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-secondary)] md:bg-white md:dark:bg-[#12161C] p-4 sm:p-6 lg:p-8 shadow-none md:shadow-xl"
    @submit.prevent="$emit('submit')"
  >
    <h2
      class="font-display text-lg sm:text-xl text-slate-900 dark:text-white pb-3.5 sm:pb-4 border-b border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06] flex items-center gap-2"
    >
      <UserCircleIcon class="w-5 h-5 text-[var(--color-accent)] shrink-0" />
      <span>Personal Details</span>
    </h2>

    <!-- Avatar Preview Row -->
    <div
      v-if="avatarPreview"
      class="mt-4 sm:mt-5 rounded-xl md:rounded-2xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.06] bg-[var(--color-bg-tertiary)]/50 md:bg-slate-50 md:dark:bg-white/[0.03] p-3 sm:p-3.5 flex items-center gap-3 sm:gap-4"
    >
      <img
        :src="avatarPreview"
        @error="$emit('avatar-error')"
        alt="Profile picture"
        class="h-11 w-11 sm:h-12 sm:w-12 rounded-lg md:rounded-xl object-cover border border-[var(--color-accent)]/30 shrink-0"
      />
      <div class="flex-1 min-w-0">
        <p class="text-xs font-bold text-slate-900 dark:text-white truncate text-left-forced">
          {{ form.name || form.username || 'Custom Avatar' }}
        </p>
        <p class="text-[11px] text-[var(--color-text-secondary)] text-left-forced">
          Tap avatar above to change photo
        </p>
      </div>
      <button
        type="button"
        @click="$emit('remove-avatar')"
        :disabled="saving"
        class="min-h-[36px] shrink-0 text-xs font-semibold text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors px-3 py-1.5 rounded-lg border border-red-500/20 hover:bg-red-50 dark:hover:bg-red-500/10 disabled:opacity-50"
      >
        Remove
      </button>
    </div>

    <button
      v-else
      type="button"
      class="mt-4 sm:mt-5 w-full text-left rounded-xl md:rounded-2xl border border-dashed border-[var(--color-border)] md:border-slate-200 md:dark:border-white/10 bg-[var(--color-bg-tertiary)]/40 md:bg-slate-50/50 md:dark:bg-white/[0.02] p-3.5 sm:p-4 flex items-center gap-3 sm:gap-3.5 cursor-pointer hover:border-amber-400/50 dark:hover:border-white/20 transition-colors"
      @click="$emit('pick-avatar')"
    >
      <div
        class="h-10 w-10 sm:h-11 sm:w-11 rounded-lg md:rounded-xl bg-[var(--color-bg-secondary)] md:bg-slate-100 md:dark:bg-white/[0.04] border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/10 flex items-center justify-center shrink-0 text-[var(--color-accent)]"
      >
        <CameraIcon class="w-5 h-5" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-xs font-bold text-slate-900 dark:text-white text-left-forced">
          Upload Profile Photo
        </p>
        <p class="text-[11px] text-[var(--color-text-secondary)] text-left-forced">
          PNG, JPG or WebP (max 5MB)
        </p>
      </div>
    </button>

    <div class="mt-4 sm:mt-5 space-y-3.5 sm:space-y-4">
      <div class="space-y-1.5">
        <label
          for="profile-username"
          class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          Username
        </label>
        <input
          id="profile-username"
          :value="form.username"
          @input="$emit('update:field', { key: 'username', value: $event.target.value })"
          placeholder="Not set"
          :disabled="!canChangeUsername"
          class="w-full min-h-[44px] rounded-lg md:rounded-xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-primary)] md:bg-slate-50 md:dark:bg-white/[0.04] px-3.5 sm:px-4 py-2.5 text-base md:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 disabled:opacity-50 disabled:cursor-not-allowed"
        />
        <p
          v-if="!canChangeUsername"
          class="text-[11px] sm:text-[10px] text-amber-500 dark:text-amber-400 text-left-forced"
        >
          Username can be updated once every 30 days. {{ usernameChangeCountdown }} day(s) remaining.
        </p>
      </div>

      <div class="space-y-1.5">
        <label
          for="profile-name"
          class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          Full Name
        </label>
        <input
          id="profile-name"
          :value="form.name"
          @input="$emit('update:field', { key: 'name', value: $event.target.value })"
          placeholder="Not set"
          class="w-full min-h-[44px] rounded-lg md:rounded-xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-primary)] md:bg-slate-50 md:dark:bg-white/[0.04] px-3.5 sm:px-4 py-2.5 text-base md:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40"
        />
      </div>

      <div class="space-y-1.5">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <label
            for="profile-email"
            class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
          >
            Email Address
          </label>
          <span
            v-if="isEmailVerified"
            class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md md:rounded-full border border-emerald-500/20"
          >
            <CheckCircleIcon class="w-3 h-3 shrink-0" />
            <span>Verified &amp; Protected</span>
          </span>
          <router-link
            v-else
            to="/customer/dashboard/verify-email"
            class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-md md:rounded-full border border-amber-500/20 hover:bg-amber-500/20 transition-colors"
          >
            <ClockIcon class="w-3 h-3 shrink-0" />
            <span>Unverified — Verify Now</span>
          </router-link>
        </div>
        <input
          id="profile-email"
          :value="form.email"
          @input="$emit('update:field', { key: 'email', value: $event.target.value })"
          type="email"
          placeholder="Not set"
          :disabled="!canChangeEmail"
          class="w-full min-h-[44px] rounded-lg md:rounded-xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-primary)] md:bg-slate-50 md:dark:bg-white/[0.04] px-3.5 sm:px-4 py-2.5 text-base md:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 disabled:opacity-50 disabled:cursor-not-allowed"
        />
        <p
          v-if="!canChangeEmail"
          class="text-[11px] sm:text-[10px] text-amber-500 dark:text-amber-400 text-left-forced"
        >
          Email address can only be changed once every 30 days.
        </p>
      </div>

      <div class="space-y-1.5">
        <label
          for="profile-phone"
          class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          Phone Number
        </label>
        <input
          id="profile-phone"
          :value="form.phone"
          @input="$emit('update:field', { key: 'phone', value: $event.target.value })"
          type="tel"
          placeholder="+234..."
          class="w-full min-h-[44px] rounded-lg md:rounded-xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-primary)] md:bg-slate-50 md:dark:bg-white/[0.04] px-3.5 sm:px-4 py-2.5 text-base md:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40"
        />
      </div>

      <div class="space-y-1.5">
        <label
          for="profile-bio"
          class="block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          Grooming &amp; Style Preferences
        </label>
        <textarea
          id="profile-bio"
          :value="form.bio"
          @input="$emit('update:field', { key: 'bio', value: $event.target.value })"
          rows="3"
          placeholder="Notes for your barbers (e.g. skin sensitivities, preferred blade lengths, parting styles)..."
          class="w-full rounded-lg md:rounded-xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-primary)] md:bg-slate-50 md:dark:bg-white/[0.04] px-3.5 sm:px-4 py-2.5 text-base md:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 resize-none leading-relaxed"
        ></textarea>
      </div>
    </div>

    <button
      type="submit"
      :disabled="saving"
      class="mt-5 sm:mt-6 w-full min-h-[44px] rounded-lg md:rounded-xl bg-[var(--color-accent)] py-3 text-sm md:text-xs font-bold text-white shadow-sm md:shadow-lg md:shadow-[var(--color-accent)]/20 transition-all hover:brightness-110 active:scale-[0.98] disabled:opacity-50"
    >
      {{ saving ? 'Saving Profile...' : 'Save Profile Changes' }}
    </button>
    <p
      v-if="status"
      class="mt-3 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 py-2 px-3 rounded-lg border border-emerald-500/20"
    >
      {{ status }}
    </p>
  </form>
</template>

<script setup>
import { CameraIcon, CheckCircleIcon, ClockIcon, UserCircleIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: { type: Object, required: true },
  avatarPreview: { type: String, default: null },
  saving: { type: Boolean, default: false },
  status: { type: String, default: '' },
  isEmailVerified: { type: Boolean, default: false },
  canChangeUsername: { type: Boolean, default: true },
  canChangeEmail: { type: Boolean, default: true },
  usernameChangeCountdown: { type: Number, default: 0 },
});

defineEmits(['submit', 'pick-avatar', 'remove-avatar', 'avatar-error', 'update:field']);
</script>
