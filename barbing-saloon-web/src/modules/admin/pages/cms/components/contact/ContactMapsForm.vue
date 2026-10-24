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
          <p class="text-xs text-white/50 mt-0.5">Embed your physical studio map so customers can locate your premises and get turn-by-turn directions.</p>
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
          Google Maps Iframe Embed Code
        </label>
        <textarea 
          v-model="content.contact_map_iframe" 
          rows="4" 
          placeholder='<iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>' 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs font-mono text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-none leading-relaxed"
        ></textarea>
        <p class="text-[11px] text-white/40">
          Open Google Maps → Find your salon location → Click "Share" → Click "Embed a map" → Copy the HTML iframe snippet and paste it directly above.
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
          Paste your short Google Maps share link. Customers can tap the "Open in Google Maps" button to launch navigation directly in their GPS app.
        </p>
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
          Could not extract a valid map URL from the pasted code. Make sure you copied the full iframe embed code snippet from Google Maps.
        </p>
      </div>

      <div v-else class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-8 text-center text-xs text-white/40 italic">
        (No Google Maps iframe code entered yet. Follow the instructions above to embed your map.)
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  MapPinIcon, 
  ArrowTopRightOnSquareIcon, 
  MapIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  },
  extractedMapSrc: {
    type: String,
    default: ''
  }
});
</script>

