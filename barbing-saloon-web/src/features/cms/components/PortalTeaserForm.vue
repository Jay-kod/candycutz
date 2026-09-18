<template>
  <div class="border-t border-white/5 px-8 py-8 space-y-6">
    <div>
      <h3 class="font-display text-xl text-theme-text">Customer Portal Teaser</h3>
      <p class="text-sm text-ivory/50">The call to action section encouraging users to log in.</p>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="space-y-6">
        <div>
          <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Section Title</label>
          <input v-model="settings.portal_teaser_title" type="text" placeholder="The CandyCutz Portal" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
        </div>
        <div>
          <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Heading</label>
          <input v-model="settings.portal_teaser_subtitle" type="text" placeholder="Unlock the Full Experience" class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all" />
        </div>
      </div>
      <div>
        <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description Text</label>
        <textarea v-model="settings.portal_teaser_text" rows="5" placeholder="Create an account to browse..." class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3 text-theme-text placeholder-ivory/20 focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin/50 transition-all resize-none"></textarea>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  settings: Object
});
</script>
