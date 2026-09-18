<template>
  <div class="border-t border-white/5 px-8 py-8 space-y-6">
    <div>
      <h3 class="font-display text-xl text-theme-text">Statistics Banner</h3>
      <p class="text-sm text-ivory/50">The numbers displayed immediately below the hero image.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div>
        <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Years Experience</label>
        <input v-model="settings.stats_years" type="text" placeholder="10+" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
      </div>
      <div>
        <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Premium Services</label>
        <input v-model="settings.stats_services" type="text" placeholder="20+" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
      </div>
      <div>
        <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Happy Clients</label>
        <input v-model="settings.stats_clients" type="text" placeholder="5k+" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  settings: Object
});
</script>
