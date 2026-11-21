<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Contact Headline -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Contact Headline</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <EnvelopeIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-xl font-bold text-white tracking-tight truncate">
          {{ content?.contact_title || 'Talk to CandyCutz' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ hasReceiverEmail ? 'Form Inbox Configured' : 'Default Admin Inbox' }}</span>
        <span class="h-1.5 w-1.5 rounded-full" :class="hasReceiverEmail ? 'bg-emerald-400' : 'bg-amber-400'"></span>
      </div>
    </div>

    <!-- Card 2: Business Channels -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Business Channels</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <PhoneIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ configuredChannelsCount }}</span>
        <span class="text-xs text-white/40">/ 4 active</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ channelSummary }}</span>
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
      </div>
    </div>

    <!-- Card 3: Social Profiles -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Social Constellation</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <ShareIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">{{ activeSocialCount }}</span>
        <span class="text-xs text-white/40">connected</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ activeSocialCount > 0 ? 'Visible across site' : 'No profiles set' }}</span>
        <span 
          class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-mono"
          :class="activeSocialCount > 0 ? 'bg-amber-500/10 text-amber-400' : 'bg-white/5 text-white/40'"
        >
          {{ activeSocialCount }}/7
        </span>
      </div>
    </div>

    <!-- Card 4: Google Maps Integration -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Google Maps Embed</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <MapPinIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-xl font-bold tracking-tight truncate"
          :class="hasValidMap ? 'text-sky-400' : 'text-white/60'"
        >
          {{ hasValidMap ? 'Interactive Map' : 'No Map Code' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ hasMapLink ? 'Directions Link Active' : (hasValidMap ? 'Iframe Embedded' : 'Pending Embed') }}</span>
        <span class="h-1.5 w-1.5 rounded-full" :class="hasValidMap ? 'bg-sky-400' : 'bg-white/30'"></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  EnvelopeIcon, 
  PhoneIcon, 
  ShareIcon, 
  MapPinIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({})
  },
  activeSocialCount: {
    type: Number,
    default: 0
  },
  extractedMapSrc: {
    type: String,
    default: ''
  }
});

const hasReceiverEmail = computed(() => Boolean(props.content?.contact_receiver_email?.trim()));

const configuredChannelsCount = computed(() => {
  let count = 0;
  if (props.content?.contact_phone?.trim()) count++;
  if (props.content?.contact_email?.trim()) count++;
  if (props.content?.contact_address?.trim()) count++;
  if (props.content?.contact_hours?.trim()) count++;
  return count;
});

const channelSummary = computed(() => {
  const parts = [];
  if (props.content?.contact_phone?.trim()) parts.push('Phone');
  if (props.content?.contact_email?.trim()) parts.push('Email');
  if (props.content?.contact_address?.trim()) parts.push('Address');
  if (props.content?.contact_hours?.trim()) parts.push('Hours');
  return parts.length ? parts.join(', ') : 'No details added';
});

const hasValidMap = computed(() => Boolean(props.extractedMapSrc));
const hasMapLink = computed(() => Boolean(props.content?.contact_map_link?.trim()));
</script>

