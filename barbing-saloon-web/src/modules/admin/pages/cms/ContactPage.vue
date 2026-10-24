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
          <div class="grid gap-8 border-t border-white/5 pt-6 xl:grid-cols-2">
            <div class="space-y-6">
              <div>
                <label for="contact-hero-image" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Hero cover image</label>
                <input
                  id="contact-hero-image"
                  ref="heroImageInput"
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-theme-text file:mr-4 file:rounded-lg file:border-0 file:bg-admin/15 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-admin-light"
                  @change="handleHeroImageUpload($event.target.files?.[0])"
                />
                <p class="mt-2 text-xs text-ivory/45">JPG, PNG, or WEBP, up to 5 MB. Save Changes to publish the cover image.</p>
              </div>
              <div>
                <div class="mb-2 flex items-center justify-between gap-4">
                  <label for="contact-hero-opacity" class="block text-xs font-bold uppercase tracking-widest text-ivory/50">Image overlay opacity</label>
                  <span class="text-sm font-semibold text-admin-light">{{ heroOpacity }}%</span>
                </div>
                <input
                  id="contact-hero-opacity"
                  v-model.number="heroOpacity"
                  type="range"
                  min="0"
                  max="100"
                  class="w-full accent-admin"
                />
                <p class="mt-2 text-xs text-ivory/45">Higher opacity darkens the cover image behind the title.</p>
              </div>
              <button
                v-if="previewHeroImage"
                type="button"
                class="min-h-10 rounded-xl border border-red-500/30 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/10"
                @click="clearHeroImage"
              >
                Remove hero cover image
              </button>
            </div>
            <div class="min-w-0">
              <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-ivory/50">Live hero preview</p>
              <PageHero
                page-key="contact"
                :fallback-title="content.contact_title || 'Contact Us'"
                :fallback-subtitle="content.contact_subtitle"
                :background-image="previewHeroImage"
                :overlay-opacity="heroOpacity"
                :load-settings="false"
              />
            </div>
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

      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Contact Form Placeholders</h2>
          <p class="mt-1 text-xs text-ivory/40">Edit the example text shown inside the public contact form fields.</p>
        </div>
        <div class="grid gap-6 p-8 md:grid-cols-2">
          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Name</span>
            <input v-model="content.contact_form_name_placeholder" type="text" maxlength="180" class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:outline-none" />
          </label>
          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Email</span>
            <input v-model="content.contact_form_email_placeholder" type="text" maxlength="180" class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:outline-none" />
          </label>
          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Phone</span>
            <input v-model="content.contact_form_phone_placeholder" type="text" maxlength="180" class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:outline-none" />
          </label>
          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Message</span>
            <input v-model="content.contact_form_message_placeholder" type="text" maxlength="500" class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:outline-none" />
          </label>
        </div>
      </div>

      <!-- Social Media Links & Visibility Control -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="font-display text-xl text-theme-text flex items-center gap-2.5">
              <ShareIcon class="w-5 h-5 text-admin" /> Social Media Links & Visibility
            </h2>
            <p class="mt-1 text-xs text-ivory/50">
              Control your social profiles and choose where each icon appears: Website Footer, Contact Page, or both.
            </p>
          </div>
          <span class="text-[11px] px-3.5 py-1.5 rounded-full bg-admin/10 border border-admin/30 text-admin font-semibold tracking-wide self-start sm:self-auto">
            {{ activeSocialCount }} Active Profile{{ activeSocialCount === 1 ? '' : 's' }}
          </span>
        </div>

        <div class="p-8 space-y-6">
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <div
              v-for="platform in platformsList"
              :key="platform.id"
              class="rounded-xl border border-white/10 bg-white/[0.03] p-5 hover:border-admin/30 transition-all space-y-4"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors shadow-sm"
                    :class="content[platform.linkKey] ? platform.activeBg : 'bg-white/5 text-ivory/40'"
                  >
                    <SocialIcon :name="platform.id" class="w-4 h-4" />
                  </div>
                  <div>
                    <h3 class="text-sm font-bold text-theme-text">{{ platform.name }}</h3>
                    <p class="text-[11px] text-ivory/40">{{ platform.hint }}</p>
                  </div>
                </div>

                <!-- Status Badge -->
                <span
                  class="text-[10px] uppercase font-bold tracking-wider px-2.5 py-0.5 rounded-full border transition-colors"
                  :class="getPlatformStatusBadge(platform)"
                >
                  {{ getPlatformStatusText(platform) }}
                </span>
              </div>

              <!-- URL / Link Input -->
              <div>
                <label class="block text-[10px] uppercase tracking-wider text-ivory/50 font-bold mb-1.5">Profile Link / Phone</label>
                <div class="relative">
                  <input
                    v-model="content[platform.linkKey]"
                    :type="platform.inputType || 'url'"
                    :placeholder="platform.placeholder"
                    class="w-full rounded-xl border border-white/10 bg-black/40 px-4 py-2.5 text-xs text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-1 focus:ring-admin/30 focus:outline-none transition-all font-mono"
                  />
                  <button
                    v-if="content[platform.linkKey]"
                    @click="content[platform.linkKey] = ''"
                    type="button"
                    title="Clear link"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-ivory/40 hover:text-rose-400 text-xs transition-colors p-1"
                  >
                    ✕
                  </button>
                </div>
              </div>

              <!-- Appearance Checkboxes -->
              <div class="pt-3 border-t border-white/5 flex flex-wrap items-center gap-3 text-xs">
                <span class="text-ivory/40 font-medium text-[11px]">Display On:</span>

                <!-- Footer Checkbox -->
                <label
                  class="flex items-center gap-2 cursor-pointer select-none rounded-lg px-2.5 py-1.5 transition-all text-xs"
                  :class="isOptionChecked(platform.footerKey) && content[platform.linkKey] ? 'bg-admin/15 text-admin border border-admin/30 shadow-sm' : 'bg-white/5 text-ivory/60 hover:text-white border border-transparent'"
                >
                  <input
                    type="checkbox"
                    :checked="isOptionChecked(platform.footerKey)"
                    @change="toggleOption(platform.footerKey, $event)"
                    class="rounded border-white/20 text-admin focus:ring-0 bg-black/40 w-3.5 h-3.5"
                  />
                  <span class="font-semibold text-[11px]">Website Footer</span>
                </label>

                <!-- Contact Page Checkbox -->
                <label
                  class="flex items-center gap-2 cursor-pointer select-none rounded-lg px-2.5 py-1.5 transition-all text-xs"
                  :class="isOptionChecked(platform.contactKey) && content[platform.linkKey] ? 'bg-admin/15 text-admin border border-admin/30 shadow-sm' : 'bg-white/5 text-ivory/60 hover:text-white border border-transparent'"
                >
                  <input
                    type="checkbox"
                    :checked="isOptionChecked(platform.contactKey)"
                    @change="toggleOption(platform.contactKey, $event)"
                    class="rounded border-white/20 text-admin focus:ring-0 bg-black/40 w-3.5 h-3.5"
                  />
                  <span class="font-semibold text-[11px]">Contact Page</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Live Mockup Previews -->
          <div class="rounded-2xl border border-white/10 bg-black/40 p-6 space-y-6 mt-8">
            <div class="flex items-center justify-between pb-3 border-b border-white/5">
              <h3 class="text-xs font-bold uppercase tracking-widest text-admin flex items-center gap-2">
                <EyeIcon class="w-4 h-4" /> Live Appearance Previews
              </h3>
              <span class="text-[11px] text-ivory/40">Visual feedback as you edit links</span>
            </div>

            <!-- Preview 1: Footer Social Bar -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-[11px] text-ivory/60">
                <span class="font-semibold">1. Footer Social Bar Preview</span>
                <span class="font-mono text-[10px] text-admin">{{ activeFooterPreviewCount }} icon{{ activeFooterPreviewCount === 1 ? '' : 's' }} showing</span>
              </div>
              <div class="rounded-xl border border-white/10 bg-[#070708] p-5 text-center shadow-inner">
                <div class="relative flex items-center">
                  <div class="flex-grow border-t border-white/10"></div>
                  <div v-if="activeFooterPreviewCount > 0" class="flex items-center gap-4 px-4 text-zinc-400">
                    <span
                      v-for="p in activeFooterPreviewList"
                      :key="p.id"
                      class="text-zinc-400 hover:text-gold transition-colors"
                      :title="p.name + ': ' + content[p.linkKey]"
                    >
                      <SocialIcon :name="p.id" class="h-4 w-4" />
                    </span>
                  </div>
                  <div v-else class="px-4 text-[11px] text-zinc-600 italic">
                    (No social icons enabled for footer)
                  </div>
                  <div class="flex-grow border-t border-white/10"></div>
                </div>
              </div>
            </div>

            <!-- Preview 2: Contact Page Connect With Us -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-[11px] text-ivory/60">
                <span class="font-semibold">2. Contact Page "Connect With Us" Preview</span>
                <span class="font-mono text-[10px] text-admin">{{ activeContactPreviewCount }} icon{{ activeContactPreviewCount === 1 ? '' : 's' }} showing</span>
              </div>
              <div class="rounded-xl border border-white/10 bg-black/60 p-5 shadow-inner">
                <h4 class="text-gold font-semibold uppercase tracking-widest text-xs mb-3">Connect With Us</h4>
                <div v-if="activeContactPreviewCount > 0" class="flex flex-wrap gap-3">
                  <div
                    v-for="p in activeContactPreviewList"
                    :key="p.id"
                    class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-ivory/80 shadow-md"
                    :title="p.name + ': ' + content[p.linkKey]"
                  >
                    <SocialIcon :name="p.id" class="w-5 h-5" />
                  </div>
                </div>
                <div v-else class="text-xs text-ivory/40 italic">
                  (No social icons enabled for Contact page)
                </div>
              </div>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '@/shared/api/old_adminApi';
import PageHero from '@/shared/ui/PageHero.vue';
import { getStorageUrl } from '@/core/utils/url';
import { EnvelopeIcon, CheckIcon, ShareIcon, EyeIcon } from '@heroicons/vue/24/outline';
import SocialIcon from '@/components/SocialIcon.vue';

const toast = useToast();
const saving = ref(false);
const currentHeroImage = ref('');
const selectedHeroImage = ref(null);
const heroImageInput = ref(null);
const heroOpacity = ref(76);
const removeHeroImage = ref(false);
const heroPreviewUrl = ref('');

const previewHeroImage = computed(() => {
  if (heroPreviewUrl.value) return heroPreviewUrl.value;
  if (removeHeroImage.value || !currentHeroImage.value) return '';
  return getStorageUrl(currentHeroImage.value);
});

const clearHeroPreviewUrl = () => {
  if (heroPreviewUrl.value) URL.revokeObjectURL(heroPreviewUrl.value);
  heroPreviewUrl.value = '';
};

const handleHeroImageUpload = (file) => {
  if (!file) return;
  selectedHeroImage.value = file;
  removeHeroImage.value = false;
  clearHeroPreviewUrl();
  heroPreviewUrl.value = URL.createObjectURL(file);
};

const clearHeroImage = () => {
  selectedHeroImage.value = null;
  if (heroImageInput.value) heroImageInput.value.value = '';
  removeHeroImage.value = true;
  clearHeroPreviewUrl();
};

const platformsList = [
  {
    id: 'whatsapp',
    name: 'WhatsApp',
    hint: 'Direct instant chats with clients',
    linkKey: 'social_whatsapp',
    footerKey: 'social_show_footer_whatsapp',
    contactKey: 'social_show_contact_whatsapp',
    placeholder: '+234 810 000 0000 or https://wa.me/234...',
    inputType: 'text',
    activeBg: 'bg-emerald-500/20 text-emerald-400',
  },
  {
    id: 'instagram',
    name: 'Instagram',
    hint: 'Cut portfolio, stories & styling reels',
    linkKey: 'social_instagram',
    footerKey: 'social_show_footer_instagram',
    contactKey: 'social_show_contact_instagram',
    placeholder: 'https://instagram.com/candycutz',
    inputType: 'url',
    activeBg: 'bg-pink-500/20 text-pink-400',
  },
  {
    id: 'facebook',
    name: 'Facebook',
    hint: 'Community page, reviews & updates',
    linkKey: 'social_facebook',
    footerKey: 'social_show_footer_facebook',
    contactKey: 'social_show_contact_facebook',
    placeholder: 'https://facebook.com/candycutz',
    inputType: 'url',
    activeBg: 'bg-blue-500/20 text-blue-400',
  },
  {
    id: 'twitter',
    name: 'X (formerly Twitter)',
    hint: 'Announcements, trends & news',
    linkKey: 'social_twitter',
    footerKey: 'social_show_footer_twitter',
    contactKey: 'social_show_contact_twitter',
    placeholder: 'https://x.com/candycutz',
    inputType: 'url',
    activeBg: 'bg-white/20 text-white',
  },
  {
    id: 'tiktok',
    name: 'TikTok',
    hint: 'Viral transformations & barber clips',
    linkKey: 'social_tiktok',
    footerKey: 'social_show_footer_tiktok',
    contactKey: 'social_show_contact_tiktok',
    placeholder: 'https://tiktok.com/@candycutz',
    inputType: 'url',
    activeBg: 'bg-rose-500/20 text-rose-400',
  },
  {
    id: 'youtube',
    name: 'YouTube',
    hint: 'Full haircuts, shop tours & podcasts',
    linkKey: 'social_youtube',
    footerKey: 'social_show_footer_youtube',
    contactKey: 'social_show_contact_youtube',
    placeholder: 'https://youtube.com/@candycutz',
    inputType: 'url',
    activeBg: 'bg-red-500/20 text-red-400',
  },
  {
    id: 'linkedin',
    name: 'LinkedIn',
    hint: 'Corporate partnerships & career opportunities',
    linkKey: 'social_linkedin',
    footerKey: 'social_show_footer_linkedin',
    contactKey: 'social_show_contact_linkedin',
    placeholder: 'https://linkedin.com/company/candycutz',
    inputType: 'url',
    activeBg: 'bg-sky-500/20 text-sky-400',
  },
];

const content = ref({
  contact_title: 'Talk to CandyCutz',
  contact_subtitle: 'Use the form to ask a question, request availability, or start a booking conversation.',
  contact_form_name_placeholder: 'John Doe',
  contact_form_email_placeholder: 'john@example.com',
  contact_form_phone_placeholder: '+234...',
  contact_form_message_placeholder: 'How can we help?',
  contact_phone: '+234 800 000 0000',
  contact_email: 'info@candycutz.com',
  contact_receiver_email: '',
  contact_address: '123 Grooming Street\nLagos, Nigeria',
  contact_hours: 'Mon - Sat: 9:00 AM - 8:00 PM\nSun: Closed',
  // Social profile links
  social_whatsapp: '',
  social_instagram: '',
  social_facebook: '',
  social_twitter: '',
  social_tiktok: '',
  social_youtube: '',
  social_linkedin: '',
  // Footer visibility toggles
  social_show_footer_whatsapp: 'true',
  social_show_footer_instagram: 'true',
  social_show_footer_facebook: 'true',
  social_show_footer_twitter: 'true',
  social_show_footer_tiktok: 'true',
  social_show_footer_youtube: 'true',
  social_show_footer_linkedin: 'true',
  // Contact page visibility toggles
  social_show_contact_whatsapp: 'true',
  social_show_contact_instagram: 'true',
  social_show_contact_facebook: 'true',
  social_show_contact_twitter: 'true',
  social_show_contact_tiktok: 'true',
  social_show_contact_youtube: 'true',
  social_show_contact_linkedin: 'true',
  // Google Maps
  contact_map_iframe: '',
  contact_map_link: '',
});

const isOptionChecked = (key) => {
  const val = content.value[key];
  if (val === undefined || val === null || val === '') return true;
  return val === true || val === 'true' || val === 1 || val === '1';
};

const toggleOption = (key, event) => {
  content.value[key] = event.target.checked ? 'true' : 'false';
};

const getPlatformStatusText = (platform) => {
  const link = (content.value[platform.linkKey] || '').trim();
  if (!link) return 'Not Set';
  const showFooter = isOptionChecked(platform.footerKey);
  const showContact = isOptionChecked(platform.contactKey);
  if (showFooter && showContact) return 'Footer & Contact';
  if (showFooter) return 'Footer Only';
  if (showContact) return 'Contact Only';
  return 'Hidden';
};

const getPlatformStatusBadge = (platform) => {
  const link = (content.value[platform.linkKey] || '').trim();
  if (!link) return 'border-white/10 text-ivory/40 bg-white/5';
  const showFooter = isOptionChecked(platform.footerKey);
  const showContact = isOptionChecked(platform.contactKey);
  if (showFooter || showContact) return 'border-emerald-500/30 text-emerald-400 bg-emerald-500/10';
  return 'border-amber-500/30 text-amber-400 bg-amber-500/10';
};

const activeSocialCount = computed(() => {
  return platformsList.filter((p) => {
    const link = (content.value[p.linkKey] || '').trim();
    const active = isOptionChecked(p.footerKey) || isOptionChecked(p.contactKey);
    return Boolean(link && active);
  }).length;
});

const activeFooterPreviewList = computed(() => {
  return platformsList.filter((p) => {
    const link = (content.value[p.linkKey] || '').trim();
    return Boolean(link && isOptionChecked(p.footerKey));
  });
});
const activeFooterPreviewCount = computed(() => activeFooterPreviewList.value.length);

const activeContactPreviewList = computed(() => {
  return platformsList.filter((p) => {
    const link = (content.value[p.linkKey] || '').trim();
    return Boolean(link && isOptionChecked(p.contactKey));
  });
});
const activeContactPreviewCount = computed(() => activeContactPreviewList.value.length);

const extractedMapSrc = computed(() => {
  const raw = (content.value.contact_map_iframe || '').trim();
  if (!raw) return '';
  
  if (raw.startsWith('https://www.google.com/maps/embed')) {
    return raw;
  }
  
  const match = raw.match(/src\s*=\s*["']([^"']+)["']/i);
  if (match) {
    const extracted = match[1].replace(/&amp;/g, '&');
    if (extracted.startsWith('https://www.google.com/maps/embed')) {
      return extracted;
    }
  }
  
  return '';
});

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data?.data || {};
    if (data) {
      const keys = Object.keys(content.value);
      keys.forEach((key) => {
        if (data[key] !== undefined && data[key] !== null) {
          content.value[key] = String(data[key]);
        }
      });
      // Fallback to legacy keys (e.g. instagram -> social_instagram)
      const legacyMap = {
        social_instagram: 'instagram',
        social_facebook: 'facebook',
        social_twitter: 'twitter',
        social_whatsapp: 'whatsapp',
        social_tiktok: 'tiktok',
        social_youtube: 'youtube',
        social_linkedin: 'linkedin',
      };
      Object.entries(legacyMap).forEach(([newKey, oldKey]) => {
        if (!content.value[newKey] && (data[oldKey] || data.social?.[oldKey])) {
          content.value[newKey] = String(data[oldKey] || data.social?.[oldKey]);
        }
      });
      currentHeroImage.value = data.page_hero_contact_image || '';
      heroOpacity.value = Number(data.page_hero_contact_overlay_opacity ?? 76);
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
      formData.append(`settings[${key}]`, value ?? '');
    });
    // Also save legacy keys for interoperability
    formData.append('settings[instagram]', content.value.social_instagram ?? '');
    formData.append('settings[facebook]', content.value.social_facebook ?? '');
    formData.append('settings[whatsapp]', content.value.social_whatsapp ?? '');
    formData.append('settings[twitter]', content.value.social_twitter ?? '');
    formData.append('settings[page_hero_contact_overlay_opacity]', String(heroOpacity.value));
    formData.append('page_hero_page', 'contact');
    if (selectedHeroImage.value) {
      formData.append('page_hero_image', selectedHeroImage.value);
    } else if (removeHeroImage.value) {
      formData.append('settings[page_hero_contact_image]', '');
    }

    const response = await adminApi.updateSettings(formData);
    const data = response.data?.data;
    if (data) currentHeroImage.value = data.page_hero_contact_image || '';
    selectedHeroImage.value = null;
    removeHeroImage.value = false;
    if (heroImageInput.value) heroImageInput.value.value = '';
    clearHeroPreviewUrl();
    toast.success('Social links and contact content updated successfully!');
  } catch (error) {
    toast.error('Failed to save changes. Please try again.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchContent();
});

onBeforeUnmount(clearHeroPreviewUrl);
</script>
