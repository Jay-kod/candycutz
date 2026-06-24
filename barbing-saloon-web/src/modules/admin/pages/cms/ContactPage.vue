<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <EnvelopeIcon class="w-4 h-4" /> Contact Page Content
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Contact</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Manage the contact information displayed on your public Contact page.</p>
        </div>
        <button 
          @click="saveContent" 
          :disabled="saving" 
          class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="h-5 w-5" />
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>

    <!-- Content Editor -->
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Page Header</h2>
          <p class="mt-1 text-xs text-ivory/40">The main title and description of the contact page.</p>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Page Title</label>
            <input v-model="content.contact_title" type="text" placeholder="Talk to CandyCutz" 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description</label>
            <textarea v-model="content.contact_subtitle" rows="2" placeholder="Use the form to ask a question..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
          </div>
        </div>
      </div>

      <!-- Contact Details -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Business Information</h2>
          <p class="mt-1 text-xs text-ivory/40">Your address, phone, and email shown on the Contact page.</p>
        </div>
        <div class="p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Public Phone Number</label>
              <input v-model="content.contact_phone" type="text" placeholder="+234 800 000 0000" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Public Email Address</label>
              <input v-model="content.contact_email" type="email" placeholder="info@candycutz.com" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
          </div>
          <div class="grid grid-cols-1 gap-6">
            <div class="rounded-xl border border-admin/20 bg-admin/5 p-5">
              <label class="block text-xs uppercase tracking-widest text-admin font-bold mb-2 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                Contact Form Destination Email
              </label>
              <input v-model="content.contact_receiver_email" type="email" placeholder="admin@candycutz.com" 
                class="w-full rounded-xl border border-white/10 bg-black/40 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
              <p class="mt-2 text-xs text-ivory/40">This is the hidden email address where all messages from the Contact Form will be sent to. It won't be shown publicly.</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Business Address</label>
              <textarea v-model="content.contact_address" rows="3" placeholder="123 Main Street, Lagos, Nigeria"
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Business Hours</label>
              <textarea v-model="content.contact_hours" rows="3" placeholder="Mon - Sat: 9:00 AM - 8:00 PM&#10;Sun: Closed"
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Social Links -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Social Media Links</h2>
          <p class="mt-1 text-xs text-ivory/40">Links to your social media profiles.</p>
        </div>
        <div class="p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Instagram</label>
              <input v-model="content.social_instagram" type="url" placeholder="https://instagram.com/candycutz" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Facebook</label>
              <input v-model="content.social_facebook" type="url" placeholder="https://facebook.com/candycutz" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Twitter / X</label>
              <input v-model="content.social_twitter" type="url" placeholder="https://x.com/candycutz" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">WhatsApp</label>
              <input v-model="content.social_whatsapp" type="text" placeholder="+234 800 000 0000" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">TikTok</label>
              <input v-model="content.social_tiktok" type="url" placeholder="https://tiktok.com/@candycutz" 
                class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            </div>
          </div>
        </div>
      </div>

      <!-- Google Maps Integration -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Google Maps Integration</h2>
          <p class="mt-1 text-xs text-ivory/40">Embed your Google Maps location so customers can find you easily.</p>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Google Maps Iframe Code</label>
            <textarea v-model="content.contact_map_iframe" rows="4" placeholder='Paste the full iframe code from Google Maps here, e.g. &lt;iframe src=&quot;https://www.google.com/maps/embed?pb=...&quot; ...&gt;&lt;/iframe&gt;'
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none font-mono text-xs"></textarea>
            <p class="mt-2 text-xs text-ivory/30">Go to Google Maps → Find your business → Click "Share" → "Embed a map" → Copy the full iframe code and paste it here.</p>
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Google Maps Direct Link (Optional)</label>
            <input v-model="content.contact_map_link" type="url" placeholder="https://maps.app.goo.gl/... or https://www.google.com/maps/place/..." 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
            <p class="mt-2 text-xs text-ivory/30">Paste your Google Maps share link. Customers will use the "Open in Google Maps" button for directions.</p>
          </div>
          <!-- Map Preview -->
          <div v-if="extractedMapSrc">
            <label class="block text-xs uppercase tracking-widest text-admin font-bold mb-3 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
              Live Map Preview
            </label>
            <div class="overflow-hidden rounded-2xl h-[350px] w-full border-2 border-admin/20 shadow-lg">
              <iframe 
                :src="extractedMapSrc" 
                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
              </iframe>
            </div>
            <p class="mt-2 text-xs text-emerald-400">✓ Map detected and ready to display on your Contact page.</p>
          </div>
          <div v-else-if="content.contact_map_iframe" class="rounded-xl border border-red-500/30 bg-red-500/5 p-4">
            <p class="text-xs text-red-400">⚠ Could not extract a valid map URL from the pasted code. Make sure you copied the full iframe embed code from Google Maps.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '../../api/admin.api';
import { EnvelopeIcon, CheckIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const saving = ref(false);

const content = ref({
  contact_title: 'Talk to CandyCutz',
  contact_subtitle: 'Use the form to ask a question, request availability, or start a booking conversation.',
  contact_phone: '+234 800 000 0000',
  contact_email: 'info@candycutz.com',
  contact_receiver_email: '',
  contact_address: '123 Grooming Street\nLagos, Nigeria',
  contact_hours: 'Mon - Sat: 9:00 AM - 8:00 PM\nSun: Closed',
  social_instagram: '',
  social_facebook: '',
  social_twitter: '',
  social_whatsapp: '',
  social_tiktok: '',
  contact_map_iframe: '',
  contact_map_link: ''
});

const extractedMapSrc = computed(() => {
  const raw = (content.value.contact_map_iframe || '').trim();
  if (!raw) return '';
  
  // 1. Already a direct embed URL
  if (raw.startsWith('https://www.google.com/maps/embed')) {
    return raw;
  }
  
  // 2. Extract src from iframe HTML tag (handles both " and ' quotes)
  const match = raw.match(/src\s*=\s*["']([^"']+)["']/i);
  if (match) {
    const extracted = match[1].replace(/&amp;/g, '&');
    // Ensure the extracted src is actually an embed URL to prevent X-Frame-Options errors
    if (extracted.startsWith('https://www.google.com/maps/embed')) {
      return extracted;
    }
  }
  
  return '';
});

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data.data;
    if (data) {
      const keys = Object.keys(content.value);
      keys.forEach(key => {
        if (data[key]) content.value[key] = data[key];
      });
    }
  } catch (error) {
    console.error('Failed to load contact content:', error);
  }
};

const saveContent = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    Object.entries(content.value).forEach(([key, value]) => {
      formData.append(`settings[${key}]`, value);
    });
    await adminApi.updateSettings(formData);
    toast.success('Contact page content saved!');
  } catch (error) {
    toast.error('Failed to save changes.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchContent();
});
</script>
