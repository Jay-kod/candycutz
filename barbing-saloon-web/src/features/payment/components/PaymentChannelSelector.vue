<template>
  <div class="space-y-2">
    <!-- Section Heading -->
    <div class="flex items-center justify-between">
      <h2 class="text-xs font-bold text-white/90 tracking-wide uppercase">
        Choose Payment Option
      </h2>
      <span class="text-[9px] uppercase font-mono tracking-wider text-amber-400/80 font-semibold">
        Instant Matching
      </span>
    </div>

    <!-- Mobile Screen View: Horizontal Pill Selector (visible on mobile < md) -->
    <div class="flex md:hidden items-center gap-1.5 overflow-x-auto pb-1.5 no-scrollbar scroll-smooth -mx-1 px-1">
      <button
        v-for="channel in channels"
        :key="'mob-' + channel.id"
        type="button"
        @click="$emit('update:selectedChannel', channel.id)"
        :class="[
          'shrink-0 flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition-all cursor-pointer whitespace-nowrap',
          selectedChannel === channel.id
            ? 'border-amber-400/60 bg-amber-500/20 text-white shadow-sm ring-1 ring-amber-400/30'
            : 'border-white/[0.08] bg-[#12161F]/80 text-white/60 hover:border-white/20 hover:text-white/80'
        ]"
      >
        <span class="text-xs">{{ channel.emoji }}</span>
        <span>{{ channel.label }}</span>
        <span
          v-if="channel.badge"
          class="text-[8px] font-bold px-1 py-0.2 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase"
        >
          {{ channel.badge }}
        </span>
      </button>
    </div>

    <!-- Desktop Screen View: Rich Vertical List (visible on md:) -->
    <div class="hidden md:block space-y-1.5">
      <button
        v-for="channel in channels"
        :key="channel.id"
        type="button"
        @click="$emit('update:selectedChannel', channel.id)"
        :class="[
          'w-full flex items-center justify-between rounded-lg border px-3 py-2 transition-all text-left cursor-pointer group',
          selectedChannel === channel.id
            ? 'border-amber-500/50 bg-gradient-to-r from-amber-500/10 via-[#151C25] to-[#11151D] text-white shadow-md shadow-black/40 ring-1 ring-amber-500/30'
            : 'border-white/[0.06] bg-[#0E131A]/70 text-white/60 hover:border-white/15 hover:text-white/90 hover:bg-[#121720]'
        ]"
      >
        <!-- Radio Circle & Label -->
        <div class="flex items-center gap-2.5">
          <!-- Custom Radio Pill -->
          <div
            :class="[
              'flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full border transition-all',
              selectedChannel === channel.id
                ? 'border-amber-400 bg-amber-400 text-slate-950 font-black'
                : 'border-white/25 bg-transparent group-hover:border-white/40'
            ]"
          >
            <!-- Checkmark SVG when selected -->
            <svg
              v-if="selectedChannel === channel.id"
              class="h-2 w-2 text-slate-950 stroke-[3]"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </div>

          <div class="space-y-0.5">
            <div class="flex items-center gap-1.5">
              <span class="text-xs font-semibold tracking-wide" :class="selectedChannel === channel.id ? 'text-white' : 'text-white/80'">
                {{ channel.label }}
              </span>
              <span
                v-if="channel.badge"
                :class="[
                  'text-[8px] font-bold px-1.5 py-0.2 rounded-full uppercase tracking-wider',
                  channel.id === 'bank_transfer'
                    ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                    : 'bg-white/10 text-white/60'
                ]"
              >
                {{ channel.badge }}
              </span>
            </div>
            <p class="text-[9px] text-white/40 leading-tight">
              {{ channel.description }}
            </p>
          </div>
        </div>

        <!-- Right Side Icon Graphic -->
        <div class="shrink-0 flex items-center justify-center pl-2">
          <!-- Bank Transfer Icon -->
          <div v-if="channel.id === 'bank_transfer'" class="flex items-center">
            <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L2 7v2h20V7L12 2zM4 11v8h3v-8H4zm6 0v8h4v-8h-4zm7 0v8h3v-8h-3zM2 21v2h20v-2H2z" />
            </svg>
          </div>

          <!-- USSD Icon -->
          <div v-else-if="channel.id === 'ussd'" class="flex items-center">
            <svg class="h-4 w-4 text-white/40 group-hover:text-amber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2zM15 7h2m-2 4h2" />
            </svg>
          </div>

          <!-- Bank Account / POS Icon -->
          <div v-else-if="channel.id === 'bank_account'" class="flex items-center">
            <svg class="h-4 w-4 text-white/40 group-hover:text-amber-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4" />
            </svg>
          </div>

          <!-- NQR Icon -->
          <div v-else-if="channel.id === 'nqr'" class="flex items-center">
            <svg class="h-4 w-4 text-white/40 group-hover:text-amber-400 transition-colors" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v2h-3v-2zm-2 2h2v3h-2v-3zm3 0h3v3h-3v-3z" />
            </svg>
          </div>

          <!-- Card Icon -->
          <div v-else class="flex items-center">
            <svg class="h-4 w-4 text-white/40 group-hover:text-amber-400 transition-colors" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
            </svg>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  selectedChannel: {
    type: String,
    default: 'bank_transfer'
  },
  enabledChannels: {
    type: Array,
    default: () => ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card']
  }
});

defineEmits(['update:selectedChannel']);

const allChannels = [
  {
    id: 'bank_transfer',
    label: 'Bank Transfer',
    emoji: '🏛️',
    badge: 'Popular',
    description: 'Direct transfer from any banking app'
  },
  {
    id: 'ussd',
    label: 'USSD',
    emoji: '📱',
    badge: 'Fast',
    description: 'Dial bank code from your registered phone'
  },
  {
    id: 'bank_account',
    label: 'Bank Account',
    emoji: '🏦',
    badge: null,
    description: 'Instant debit from verified bank account'
  },
  {
    id: 'nqr',
    label: 'NQR',
    emoji: '📷',
    badge: 'Scan',
    description: 'Scan NQR code with your mobile banking app'
  },
  {
    id: 'card',
    label: 'Card',
    emoji: '💳',
    badge: null,
    description: 'Debit or credit card (Mastercard/Visa/Verve)'
  }
];

const channels = computed(() => {
  if (!props.enabledChannels || props.enabledChannels.length === 0) return allChannels;
  return allChannels.filter(c => props.enabledChannels.includes(c.id));
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
