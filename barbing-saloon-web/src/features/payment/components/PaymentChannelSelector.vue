<template>
  <div class="space-y-3">
    <h2 class="text-sm sm:text-base font-bold text-white/90">
      Choose Payment Option
    </h2>

    <div class="space-y-2.5">
      <button
        v-for="channel in channels"
        :key="channel.id"
        type="button"
        @click="$emit('update:selectedChannel', channel.id)"
        :class="[
          'w-full flex items-center justify-between rounded-2xl border px-4 py-3.5 transition-all text-left cursor-pointer',
          selectedChannel === channel.id
            ? 'border-white/30 bg-[#161B22] text-white shadow-lg shadow-black/40 ring-1 ring-white/10'
            : 'border-white/[0.08] bg-[#0E1217]/80 text-white/60 hover:border-white/20 hover:text-white/90 hover:bg-[#12161C]'
        ]"
      >
        <!-- Radio Circle & Label -->
        <div class="flex items-center gap-3">
          <!-- Custom Radio Pill -->
          <div
            :class="[
              'flex h-5 w-5 shrink-0 items-center justify-center rounded-full border transition-all',
              selectedChannel === channel.id
                ? 'border-white bg-black text-white'
                : 'border-white/30 bg-transparent'
            ]"
          >
            <!-- Checkmark SVG when selected -->
            <svg
              v-if="selectedChannel === channel.id"
              class="h-3 w-3 text-white"
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

          <span class="text-sm font-semibold tracking-wide">
            {{ channel.label }}
          </span>
        </div>

        <!-- Right Side Icon Graphic -->
        <div class="shrink-0 flex items-center justify-center text-amber-400/90">
          <!-- Bank Transfer Icon -->
          <div v-if="channel.id === 'bank_transfer'" class="flex items-center">
            <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L2 7v2h20V7L12 2zM4 11v8h3v-8H4zm6 0v8h4v-8h-4zm7 0v8h3v-8h-3zM2 21v2h20v-2H2z" />
            </svg>
          </div>

          <!-- USSD Icon -->
          <div v-else-if="channel.id === 'ussd'" class="flex items-center">
            <svg class="h-6 w-6 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2zM15 7h2m-2 4h2" />
            </svg>
          </div>

          <!-- Bank Account / POS Icon -->
          <div v-else-if="channel.id === 'bank_account'" class="flex items-center">
            <svg class="h-6 w-6 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8h4" />
            </svg>
          </div>

          <!-- NQR Icon -->
          <div v-else-if="channel.id === 'nqr'" class="flex items-center">
            <svg class="h-6 w-6 text-white/50" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v2h-3v-2zm-2 2h2v3h-2v-3zm3 0h3v3h-3v-3z" />
            </svg>
          </div>

          <!-- Card Icon -->
          <div v-else class="flex items-center">
            <svg class="h-6 w-6 text-white/50" viewBox="0 0 24 24" fill="currentColor">
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
  { id: 'bank_transfer', label: 'Bank Transfer' },
  { id: 'ussd', label: 'USSD' },
  { id: 'bank_account', label: 'Bank Account' },
  { id: 'nqr', label: 'NQR' },
  { id: 'card', label: 'Card' }
];

const channels = computed(() => {
  if (!props.enabledChannels || props.enabledChannels.length === 0) return allChannels;
  return allChannels.filter(c => props.enabledChannels.includes(c.id));
});
</script>
