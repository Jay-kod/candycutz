<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-6">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400 shadow-[0_0_15px_rgba(14,165,233,0.15)]">
          <MapPinIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Google Maps Integration & Location</h2>
          <p class="text-xs text-white/50 mt-0.5">Embed your studio map and keep the customer directions pin synced to your exact shop coordinates.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 04
      </span>
    </div>

    <!-- Form Inputs -->
    <div class="space-y-5">
      <div class="space-y-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
          Google Maps Iframe Embed Code or URL
        </label>
        <textarea 
          v-model="content.contact_map_iframe" 
          rows="4" 
          placeholder='<iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>' 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs font-mono text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed"
        ></textarea>
        <p class="text-[11px] text-white/40">
          Open Google Maps → Find your salon location → Click "Share" → "Embed a map" → Paste either the HTML iframe snippet or the <span class="font-mono text-white/60">https://www.google.com/maps/embed...</span> URL directly.
        </p>
      </div>

      <div class="space-y-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
          Google Maps Direct Navigation Link (Optional)
        </label>
        <div class="relative">
          <input 
            v-model="content.contact_map_link" 
            type="url" 
            placeholder="https://maps.app.goo.gl/... or https://www.google.com/maps/place/..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none font-mono" 
          />
          <ArrowTopRightOnSquareIcon class="h-4 w-4 text-white/30 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        </div>
        <p class="text-[11px] text-white/40">
          Paste your Google Maps share link. Customers can tap "Open in Google Maps" on the public Contact page.
        </p>
      </div>

      <!-- Exact Shop Coordinates for Customer Directions Map -->
      <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-white/80">
              Shop Coordinates (Customer Directions Map Pin)
            </h3>
            <p class="text-[11px] text-white/45 mt-0.5">
              Auto-extracted from Google Maps links when present, or set directly from your device GPS while at the salon.
            </p>
          </div>
          <button
            type="button"
            :disabled="locatingShop"
            class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-1.5 text-[11px] font-semibold text-emerald-300 hover:bg-emerald-500/20 disabled:opacity-60 transition cursor-pointer"
            @click="captureShopGps"
          >
            {{ locatingShop ? 'Detecting GPS...' : 'Use Current Shop GPS' }}
          </button>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <label class="block text-[11px] font-semibold text-white/60 mb-1">Latitude</label>
            <input
              v-model="content.latitude"
              type="text"
              inputmode="decimal"
              placeholder="e.g. 8.848600"
              class="w-full rounded-lg border border-white/[0.08] bg-white/[0.04] px-3 py-2 text-xs font-mono text-white placeholder-white/20 focus:border-admin/60 outline-none"
            />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-white/60 mb-1">Longitude</label>
            <input
              v-model="content.longitude"
              type="text"
              inputmode="decimal"
              placeholder="e.g. 7.873600"
              class="w-full rounded-lg border border-white/[0.08] bg-white/[0.04] px-3 py-2 text-xs font-mono text-white placeholder-white/20 focus:border-admin/60 outline-none"
            />
          </div>
        </div>

        <p v-if="gpsError" class="text-[11px] text-rose-400">{{ gpsError }}</p>
        <p v-else-if="lockDiagnostic.status === 'locked'" class="text-[11px] text-emerald-400 font-mono">
          ✓ {{ lockDiagnostic.message }}
        </p>
        <div
          v-else
          class="rounded-lg border border-amber-500/40 bg-amber-500/10 p-3 flex flex-wrap items-center justify-between gap-2"
        >
          <p class="text-[11px] text-amber-200">{{ lockDiagnostic.message }}</p>
          <button
            v-if="embedCoords"
            type="button"
            class="rounded-md bg-amber-500 px-2.5 py-1 text-[11px] font-bold text-black hover:bg-amber-400 cursor-pointer"
            @click="syncCoordsFromEmbed"
          >
            Lock Coords to Google Map
          </button>
        </div>
      </div>
    </div>

    <!-- Live Extracted Map Preview -->
    <div class="pt-4 border-t border-white/[0.06] space-y-3">
      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-sky-400">
          <MapIcon class="h-4 w-4" />
          Live Interactive Map Simulation
        </label>
        <span 
          v-if="extractedMapSrc" 
          class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
        >
          ✓ Ready to Display
        </span>
      </div>

      <div v-if="extractedMapSrc" class="overflow-hidden rounded-2xl h-[340px] w-full border border-white/[0.1] shadow-2xl bg-black">
        <iframe 
          :src="extractedMapSrc" 
          width="100%" 
          height="100%" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy"
        ></iframe>
      </div>

      <div v-else-if="content.contact_map_iframe" class="rounded-xl border border-rose-500/30 bg-rose-500/5 p-4 flex items-start gap-3">
        <span class="text-rose-400 font-bold">⚠</span>
        <p class="text-xs text-rose-300">
          Could not extract a valid Google Maps embed URL from the pasted code. Make sure you copied the full iframe embed code snippet or <span class="font-mono">https://www.google.com/maps/embed...</span> URL from Google Maps.
        </p>
      </div>

      <div v-else class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-8 text-center text-xs text-white/40 italic">
        (No Google Maps iframe code entered yet. Follow the instructions above to embed your map.)
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { 
  MapPinIcon, 
  ArrowTopRightOnSquareIcon, 
  MapIcon 
} from '@heroicons/vue/24/outline';
import { extractGoogleMapsLocation, haversineDistanceMeters } from '@/features/directions/utils/googleMaps';

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  extractedMapSrc: {
    type: String,
    default: ''
  },
  resolvedShop: {
    type: Object,
    default: null
  }
});

const locatingShop = ref(false);
const gpsError = ref('');

const embedCoords = computed(() =>
  extractGoogleMapsLocation(props.content.contact_map_link) ||
  extractGoogleMapsLocation(props.content.contact_map_iframe)
);

const hasValidCoords = computed(() => {
  const lat = Number(props.content.latitude);
  const lng = Number(props.content.longitude);
  return (
    String(props.content.latitude ?? '').trim() !== '' &&
    String(props.content.longitude ?? '').trim() !== '' &&
    Number.isFinite(lat) &&
    Number.isFinite(lng) &&
    lat >= -90 &&
    lat <= 90 &&
    lng >= -180 &&
    lng <= 180
  );
});

const lockDiagnostic = computed(() => {
  if (!hasValidCoords.value) {
    return {
      status: 'coords_missing',
      message: 'Shop coordinates are missing. Paste a Google Maps embed URL or click "Use Current Shop GPS".',
    };
  }
  if (!props.extractedMapSrc) {
    return {
      status: 'embed_missing',
      message: 'Google Maps embed URL is missing or invalid. Customer Directions will fall back to saved coordinates only.',
    };
  }
  if (props.resolvedShop?.location_lock?.status === 'locked' && props.resolvedShop?.location_lock?.verified) {
    const shopLabel = props.resolvedShop.name || 'CandyCutz Studio';
    return {
      status: 'locked',
      message: `${shopLabel} & Customer Directions pin locked to ${props.resolvedShop.latitude}, ${props.resolvedShop.longitude}.`,
    };
  }
  if (embedCoords.value) {
    const maxAllowedDrift = embedCoords.value.precise ? 150 : 500;
    const drift = haversineDistanceMeters(
      Number(props.content.latitude),
      Number(props.content.longitude),
      Number(embedCoords.value.latitude),
      Number(embedCoords.value.longitude)
    );
    if (drift > maxAllowedDrift) {
      return {
        status: 'sync_mismatch',
        message: `Problem detected: Saved coords (${props.content.latitude}, ${props.content.longitude}) differ by ${drift}m from Google Map pin (${embedCoords.value.latitude}, ${embedCoords.value.longitude}).`,
      };
    }
  }
  return {
    status: 'locked',
    message: `Main shop location & Customer Directions pin locked to ${props.content.latitude}, ${props.content.longitude}.`,
  };
});

const syncCoordsFromEmbed = () => {
  if (!embedCoords.value) return;
  props.content.latitude = embedCoords.value.latitude;
  props.content.longitude = embedCoords.value.longitude;
  gpsError.value = '';
};

const captureShopGps = () => {
  gpsError.value = '';
  if (typeof navigator === 'undefined' || !navigator.geolocation) {
    gpsError.value = 'Device GPS is unavailable in this browser.';
    return;
  }

  locatingShop.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      props.content.latitude = String(Number(pos.coords.latitude.toFixed(6)));
      props.content.longitude = String(Number(pos.coords.longitude.toFixed(6)));
      locatingShop.value = false;
    },
    (err) => {
      gpsError.value = err?.message || 'Unable to read current GPS location.';
      locatingShop.value = false;
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
};
</script>
