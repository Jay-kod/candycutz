<template>
  <PublicLayout>
    <PageHero
      page-key="contact"
      eyebrow="Contact"
      fallback-title="Talk to CandyCutz"
      fallback-subtitle="Use the form to ask a question, request availability, or start a booking conversation."
      title-setting-key="contact_title"
      subtitle-setting-key="contact_subtitle"
    />
    <section class="mx-auto grid max-w-7xl gap-10 px-4 md:px-6 pt-16 pb-20 text-theme-text lg:grid-cols-[0.9fr_1.1fr]">
      <div data-reveal>
        <div class="grid gap-6 sm:grid-cols-2">
          <!-- Location Card -->
          <div v-if="contactAddress" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Location</h3>
            <p class="text-sm text-theme-muted whitespace-pre-wrap leading-relaxed">{{ contactAddress }}</p>
          </div>

          <!-- Hours Card -->
          <div v-if="contactHours" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Hours</h3>
            <p class="text-sm text-theme-muted whitespace-pre-wrap leading-relaxed">{{ contactHours }}</p>
          </div>

          <!-- Phone Card -->
          <div v-if="contactPhone" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.25-3.95-6.847-6.847l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Phone</h3>
            <p class="text-sm text-theme-muted"><a :href="'tel:' + contactPhone" class="hover:text-ivory transition-colors">{{ contactPhone }}</a></p>
          </div>

          <!-- Email Card -->
          <div v-if="contactEmail" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Email</h3>
            <p class="text-sm text-theme-muted"><a :href="'mailto:' + contactEmail" class="hover:text-ivory transition-colors break-all">{{ contactEmail }}</a></p>
          </div>
        </div>

        <!-- 9-Disc Honeycomb Constellation (Exact Reference Spacing from media_1792774438527_82a2316c.png) -->
        <div class="mt-8 pt-8 border-t border-theme-border" data-reveal>
          <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-b from-white/[0.04] via-[#0E0E11]/90 to-obsidian py-8 px-4 sm:px-6 text-center shadow-2xl backdrop-blur-xl">
            <!-- Background Soft Ambient Glow -->
            <div
              class="pointer-events-none absolute -bottom-10 left-1/2 -translate-x-1/2 w-80 h-40 bg-gold/10 blur-[55px] rounded-full"
              aria-hidden="true"
            ></div>

            <!-- Top Header (Exact Typography Hierarchy from Reference) -->
            <h3 class="font-display text-xl sm:text-2xl text-ivory font-semibold tracking-tight text-center">
              Works where you work
            </h3>
            <p class="mt-1.5 text-xs sm:text-[12.5px] text-theme-muted font-light max-w-xs sm:max-w-sm mx-auto leading-relaxed text-center">
              Connect with our master barbers, view signature drops, or locate our studio across your favourite platforms.
            </p>

            <!-- 9-Disc Honeycomb Constellation (Exact Reference Spacing: D=70px, ΔX=132px, Air Gap=62px) -->
            <div class="relative mt-8 sm:mt-10 mb-4 flex items-center justify-center">
              <div class="relative mx-auto h-[160px] w-[470px] scale-[0.68] min-[400px]:scale-[0.8] min-[480px]:scale-90 sm:scale-100 origin-center transition-transform">
                <template v-for="disc in allConstellationDiscs" :key="disc.id">
                  <!-- Empty / Future Addition Slot Disc -->
                  <div
                    v-if="disc.type === 'empty'"
                    :style="{ left: disc.left, top: disc.top, zIndex: disc.zIndex }"
                    class="group/empty absolute -translate-x-1/2 flex h-[70px] w-[70px] items-center justify-center rounded-full border border-white/[0.08] bg-gradient-to-b from-white/[0.035] via-[#121215]/90 to-[#0A0A0C] shadow-[inset_0_1px_1px_rgba(255,255,255,0.06),0_8px_18px_rgba(0,0,0,0.7)] backdrop-blur-md transition-all duration-300 hover:scale-110 hover:-translate-y-1 hover:border-gold/40 hover:shadow-[0_0_18px_rgba(255,153,0,0.25)] hover:z-50 cursor-default select-none"
                  >
                    <!-- Specular Top Rim Light -->
                    <div
                      class="pointer-events-none absolute top-1.5 inset-x-3 h-[1px] bg-gradient-to-r from-transparent via-white/15 to-transparent rounded-full"
                      aria-hidden="true"
                    ></div>

                    <!-- Faint Future Slot Target Glyph (+) on hover -->
                    <div class="flex items-center justify-center text-white/10 group-hover/empty:text-gold/80 transition-colors duration-300">
                      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                      </svg>
                    </div>

                    <!-- Floating Tooltip -->
                    <div class="pointer-events-none absolute -bottom-8 left-1/2 -translate-x-1/2 opacity-0 group-hover/empty:opacity-100 transition-all duration-200 z-50 whitespace-nowrap rounded-md bg-obsidian/95 border border-white/15 px-2 py-0.5 text-[9.5px] font-medium text-gold/90 shadow-xl">
                      {{ disc.label }}
                    </div>
                  </div>

                  <!-- Active Social Platform Disc -->
                  <a
                    v-else
                    :href="disc.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    :aria-label="disc.label"
                    :style="{ left: disc.left, top: disc.top, zIndex: disc.zIndex }"
                    class="group/bubble absolute -translate-x-1/2 flex h-[70px] w-[70px] items-center justify-center rounded-full border border-white/10 bg-gradient-to-b from-[#1C1C20] via-[#141417] to-[#0D0D0F] shadow-[inset_0_1px_1px_rgba(255,255,255,0.18),0_10px_22px_-4px_rgba(0,0,0,0.85)] backdrop-blur-xl transition-all duration-300 ease-out hover:scale-110 hover:-translate-y-1.5 hover:border-gold/60 hover:shadow-[0_0_24px_rgba(255,153,0,0.35),inset_0_1px_2px_rgba(255,255,255,0.3)] hover:z-50 focus:outline-none focus:ring-2 focus:ring-gold"
                  >
                    <!-- Specular Top Rim Light -->
                    <div
                      class="pointer-events-none absolute top-1.5 inset-x-3 h-[1px] bg-gradient-to-r from-transparent via-white/30 to-transparent rounded-full"
                      aria-hidden="true"
                    ></div>

                    <!-- Icon Glyphs with Vibrant Authentic Colors (size-7 = 28px) -->
                    <div class="transition-transform duration-300 group-hover/bubble:scale-110 flex items-center justify-center">
                      <!-- Google Maps Pin (Matching Reference Image) -->
                      <svg v-if="disc.key === 'maps'" class="size-7" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#EA4335" />
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 1.7.6 3.3 1.6 4.7L12 2z" fill="#4285F4" opacity="0.3" />
                        <circle cx="12" cy="9" r="3.2" fill="#fff" />
                        <circle cx="12" cy="9" r="2.2" fill="#EA4335" />
                      </svg>

                      <!-- WhatsApp (Vibrant Green) -->
                      <svg v-else-if="disc.key === 'whatsapp'" class="size-7" viewBox="0 0 24 24" fill="#25D366">
                        <path fill-rule="evenodd" d="M12.031 21.996c-1.63 0-3.235-.417-4.664-1.216L2.617 22l1.241-4.613a9.988 9.988 0 01-1.31-4.99c0-5.505 4.492-9.985 10.01-9.985 5.518 0 10.007 4.48 10.007 9.985 0 5.506-4.489 9.999-10.01 9.999zm-4.721-13.06c-.274-.775-.561-.79-.817-.803h-.696c-.237 0-.623.089-.95.446-.326.357-1.246 1.218-1.246 2.972 0 1.754 1.275 3.448 1.453 3.685.178.238 2.512 3.834 6.082 5.38 3.57 1.547 3.57 1.031 4.223.972.652-.06 2.106-.862 2.403-1.695.297-.833.297-1.547.208-1.696-.089-.148-.326-.238-.682-.416s-2.106-1.04-2.433-1.16c-.326-.118-.564-.178-.801.179-.237.357-.92 1.159-1.127 1.397-.208.238-.415.267-.771.089s-1.503-.553-2.863-1.77c-1.057-.946-1.771-2.116-1.979-2.473-.208-.356-.022-.55.156-.728.16-.16.356-.416.534-.624.178-.208.237-.356.356-.594.119-.237.06-.445-.03-.624-.089-.178-.801-1.93-1.098-2.644z" clip-rule="evenodd" />
                      </svg>

                      <!-- Instagram (Gradient Camera) -->
                      <svg v-else-if="disc.key === 'instagram'" class="size-7" viewBox="0 0 24 24" fill="none">
                        <defs>
                          <linearGradient id="ig-grad-contact" x1="0%" y1="100%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#FEC053" />
                            <stop offset="30%" stop-color="#F2203E" />
                            <stop offset="70%" stop-color="#B71E8F" />
                            <stop offset="100%" stop-color="#6559CA" />
                          </linearGradient>
                        </defs>
                        <rect x="2" y="2" width="20" height="20" rx="5.5" fill="url(#ig-grad-contact)" />
                        <path fill="#fff" d="M12 7a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.2-8.4a1.2 1.2 0 11-2.4 0 1.2 1.2 0 012.4 0z" />
                      </svg>

                      <!-- TikTok (Cyan & Magenta Offset Glyph) -->
                      <svg v-else-if="disc.key === 'tiktok'" class="size-6" viewBox="0 0 24 24" fill="none">
                        <path d="M16.6 5.82s.51.5 1.4 1c1.02.58 2.05.74 2.05.74V4.02s-.97-.04-1.87-.41a4.8 4.8 0 01-1.58-1.18V.02h-3.9v14.4c0 1.8-1.46 3.26-3.26 3.26a3.26 3.26 0 01-3.26-3.26c0-1.8 1.46-3.26 3.26-3.26.35 0 .69.06 1.01.16V7.2a7.3 7.3 0 00-1.01-.07A7.26 7.26 0 002 14.39a7.26 7.26 0 007.26 7.26c4.01 0 7.26-3.25 7.26-7.26V5.82z" fill="#25F4EE" />
                        <path d="M17.8 5.4s.51.5 1.4 1c1.02.58 2.05.74 2.05.74V4.4s-.97-.04-1.87-.41a4.8 4.8 0 01-1.58-1.18V.4h-3.5v14.4c0 1.8-1.46 3.26-3.26 3.26a3.26 3.26 0 01-3.26-3.26c0-1.8 1.46-3.26 3.26-3.26.35 0 .69.06 1.01.16V7.6a7.3 7.3 0 00-1.01-.07A7.26 7.26 0 003.2 14.79a7.26 7.26 0 007.26 7.26c4.01 0 7.26-3.25 7.26-7.26V5.4z" fill="#FE2C55" opacity="0.8" />
                        <path d="M17.2 5.6s.51.5 1.4 1c1.02.58 2.05.74 2.05.74V3.8s-.97-.04-1.87-.41a4.8 4.8 0 01-1.58-1.18V-.2h-3.7v14.4c0 1.8-1.46 3.26-3.26 3.26a3.26 3.26 0 01-3.26-3.26c0-1.8 1.46-3.26 3.26-3.26.35 0 .69.06 1.01.16V7.4a7.3 7.3 0 00-1.01-.07A7.26 7.26 0 002.6 14.59a7.26 7.26 0 007.26 7.26c4.01 0 7.26-3.25 7.26-7.26V5.6z" fill="#FFFFFF" />
                      </svg>

                      <!-- YouTube (Red Icon) -->
                      <svg v-else-if="disc.key === 'youtube'" class="size-7" viewBox="0 0 24 24" fill="#FF0000">
                        <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z" />
                        <path d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z" fill="#fff" />
                      </svg>

                      <!-- Facebook (Blue Icon) -->
                      <svg v-else-if="disc.key === 'facebook'" class="size-7" viewBox="0 0 24 24" fill="#1877F2">
                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                      </svg>

                      <!-- Twitter / X (Ivory Icon) -->
                      <svg v-else class="size-6" viewBox="0 0 24 24" fill="#FAFAF8">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 5.922H5.078z" />
                      </svg>
                    </div>

                    <!-- Floating Tooltip on Hover -->
                    <div class="pointer-events-none absolute -bottom-8 left-1/2 -translate-x-1/2 opacity-0 group-hover/bubble:opacity-100 transition-all duration-200 z-50 whitespace-nowrap rounded-md bg-obsidian/95 border border-white/15 px-2 py-0.5 text-[9.5px] font-medium text-ivory shadow-xl">
                      {{ disc.label }}
                    </div>
                  </a>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>

      <form data-reveal data-reveal-delay="200" class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-2xl backdrop-blur-md" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
          <label class="grid gap-2">
            <span class="text-sm font-medium text-theme-text">Name</span>
            <input v-model="form.name" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" :placeholder="formPlaceholders.name" required />
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-medium text-theme-text">Email</span>
            <input v-model="form.email" type="email" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" :placeholder="formPlaceholders.email" required />
          </label>
        </div>
        <label class="mt-6 grid gap-2">
          <span class="text-sm font-medium text-theme-text">Phone</span>
          <input v-model="form.phone" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" :placeholder="formPlaceholders.phone" required />
        </label>
        <label class="mt-6 grid gap-2">
          <span class="text-sm font-medium text-theme-text">Message</span>
          <textarea v-model="form.message" rows="5" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors resize-none" :placeholder="formPlaceholders.message" required></textarea>
        </label>
        <button type="submit" class="mt-8 w-full rounded-xl bg-gold px-6 py-4 font-semibold text-obsidian transition hover:bg-gold-light hover:-translate-y-0.5 transform shadow-lg">Send message</button>
        
        <div v-if="status" class="mt-6 p-4 rounded-xl bg-theme-bg text-center text-sm font-medium border border-theme-border">
          <span class="text-gold-light">{{ status }}</span>
        </div>
      </form>
    </section>

    <!-- Map Section -->
    <section v-if="mapEmbedSrc" class="mx-auto max-w-7xl px-4 md:px-6 pb-20 text-theme-text" data-reveal>
      <div class="text-center mb-10">
        <h3 class="text-gold font-display text-4xl">Find Us on the Map</h3>
      </div>
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-4 shadow-2xl">
        <div class="overflow-hidden rounded-2xl h-[450px] w-full bg-theme-bg">
          <iframe 
            :src="mapEmbedSrc" 
            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
      <div class="mt-10 text-center" v-if="contactMapLink">
        <a :href="contactMapLink" target="_blank" class="inline-flex items-center gap-3 rounded-xl border border-gold px-8 py-4 font-bold text-gold transition-all hover:bg-gold hover:text-obsidian hover:shadow-[0_0_20px_rgba(212,175,55,0.4)]">
          Open in Google Maps
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
        </a>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const form = reactive({ name: '', email: '', phone: '', message: '' });
const status = ref('');
const formPlaceholders = reactive({
  name: 'John Doe',
  email: 'john@example.com',
  phone: '+234...',
  message: 'How can we help?',
});

const contactAddress = ref('123 Grooming Street\nLagos, Nigeria');
const contactHours = ref('Mon - Sat: 9:00 AM - 8:00 PM\nSun: Closed');
const contactPhone = ref('+234 800 000 0000');
const contactEmail = ref('info@candycutz.com');
const contactMapIframe = ref('');
const contactMapLink = ref('');

const socialSettings = ref({
  whatsapp: '',
  instagram: '',
  facebook: '',
  twitter: '',
  tiktok: '',
  youtube: '',
  linkedin: '',
  show_contact_whatsapp: true,
  show_contact_instagram: true,
  show_contact_facebook: true,
  show_contact_twitter: true,
  show_contact_tiktok: true,
  show_contact_youtube: true,
  show_contact_linkedin: true,
});

const formatWhatsAppUrl = (val) => {
  if (!val) return '';
  const trimmed = val.trim();
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) return trimmed;
  const digits = trimmed.replace(/\D/g, '');
  return digits ? `https://wa.me/${digits}` : '';
};

const parseBool = (val, defaultVal = true) => {
  if (val === undefined || val === null || val === '') return defaultVal;
  return val === true || val === 'true' || val === 1 || val === '1';
};

/**
 * 9-Disc Staggered Honeycomb Constellation (Exact Reference Spacing from media_1792774438527_82a2316c.png)
 *
 * Measured Metrics:
 * - Circle Diameter D = 70px
 * - Horizontal Step ΔX = 132px (62px air gap between circles in the same row)
 * - Row-to-Row Stagger = 66px (half of 132px)
 * - Row 1 -> Row 2 ΔY = 42px (8.2px diagonal air gap)
 * - Row 2 -> Row 3 ΔY = 37px (5.7px diagonal air gap)
 * - Total Constellation Width = 466px, Height = 149px
 *
 * Geometry:
 * - Row 1 (Top / Peaks, 2 Active Discs): Maps (calc(50% - 66px)), Instagram (calc(50% + 66px)) at top: 0px
 * - Row 2 (Middle, 3 Active Discs): TikTok (calc(50% - 132px)), WhatsApp (50%), X/Social (calc(50% + 132px)) at top: 42px
 * - Row 3 (Bottom, 4 Empty Discs): 4 Foundation slots at calc(50% - 198px), calc(50% - 66px), calc(50% + 66px), calc(50% + 198px) at top: 79px
 */
const allConstellationDiscs = computed(() => {
  const s = socialSettings.value;
  return [
    // --- ROW 1 (TOP / PEAKS, zIndex: 30) ---
    // 1. Peak Left: Google Maps Pin (Directions)
    {
      id: 'maps',
      type: 'active',
      key: 'maps',
      label: 'Google Maps (Directions)',
      url: contactMapLink.value || 'https://maps.google.com/?q=Angwan+Kare,+BCG,+Keffi',
      left: 'calc(50% - 66px)',
      top: '0px',
      zIndex: 30,
    },
    // 2. Peak Right: Instagram (@CandyCutz)
    {
      id: 'instagram',
      type: 'active',
      key: 'instagram',
      label: 'Instagram (@CandyCutz)',
      url: s.instagram || 'https://instagram.com/candycutz',
      left: 'calc(50% + 66px)',
      top: '0px',
      zIndex: 30,
    },

    // --- ROW 2 (MIDDLE / SHOULDERS & CENTER, zIndex: 20) ---
    // 3. Middle Left: TikTok
    {
      id: 'tiktok',
      type: 'active',
      key: 'tiktok',
      label: 'TikTok',
      url: s.tiktok || 'https://tiktok.com/@candycutz',
      left: 'calc(50% - 132px)',
      top: '42px',
      zIndex: 20,
    },
    // 4. Middle Center: WhatsApp (Instant Chat)
    {
      id: 'whatsapp',
      type: 'active',
      key: 'whatsapp',
      label: 'WhatsApp (Instant Chat)',
      url: formatWhatsAppUrl(s.whatsapp) || 'https://wa.me/2348123456789',
      left: '50%',
      top: '42px',
      zIndex: 20,
    },
    // 5. Middle Right: X / YouTube / Facebook
    {
      id: 'social-right',
      type: 'active',
      key: s.youtube ? 'youtube' : (s.facebook ? 'facebook' : 'twitter'),
      label: s.youtube ? 'YouTube' : (s.facebook ? 'Facebook' : 'X (Twitter)'),
      url: s.youtube || s.facebook || s.twitter || 'https://x.com/candycutz',
      left: 'calc(50% + 132px)',
      top: '42px',
      zIndex: 20,
    },

    // --- ROW 3 (BOTTOM FOUNDATION, 4 EMPTY DISCS FOR FUTURE ADDITION, zIndex: 10) ---
    // 6. Far-Left Empty Disc
    {
      id: 'empty-1',
      type: 'empty',
      label: 'Future Addition Slot',
      left: 'calc(50% - 198px)',
      top: '79px',
      zIndex: 10,
    },
    // 7. Mid-Left Empty Disc (Directly below Maps at x = 50% - 66px)
    {
      id: 'empty-2',
      type: 'empty',
      label: 'Future Addition Slot',
      left: 'calc(50% - 66px)',
      top: '79px',
      zIndex: 10,
    },
    // 8. Mid-Right Empty Disc (Directly below Instagram at x = 50% + 66px)
    {
      id: 'empty-3',
      type: 'empty',
      label: 'Future Addition Slot',
      left: 'calc(50% + 66px)',
      top: '79px',
      zIndex: 10,
    },
    // 9. Far-Right Empty Disc
    {
      id: 'empty-4',
      type: 'empty',
      label: 'Future Addition Slot',
      left: 'calc(50% + 198px)',
      top: '79px',
      zIndex: 10,
    },
  ];
});

const mapEmbedSrc = computed(() => {
  const raw = (contactMapIframe.value || '').trim();
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

const { init: initScrollReveal } = useScrollReveal();

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data?.data || {};
    if (settings) {
      if (settings.contact_address || settings.address) contactAddress.value = settings.contact_address || settings.address;
      if (settings.contact_form_name_placeholder) formPlaceholders.name = settings.contact_form_name_placeholder;
      if (settings.contact_form_email_placeholder) formPlaceholders.email = settings.contact_form_email_placeholder;
      if (settings.contact_form_phone_placeholder) formPlaceholders.phone = settings.contact_form_phone_placeholder;
      if (settings.contact_form_message_placeholder) formPlaceholders.message = settings.contact_form_message_placeholder;
      if (settings.contact_hours !== undefined && settings.contact_hours !== null && settings.contact_hours !== '') {
        contactHours.value = settings.contact_hours;
      }
      if (settings.contact_phone || settings.phone || settings.phone_number) contactPhone.value = settings.contact_phone || settings.phone || settings.phone_number;
      if (settings.contact_email || settings.email) contactEmail.value = settings.contact_email || settings.email;
      if (settings.contact_map_iframe || settings.map_iframe) contactMapIframe.value = settings.contact_map_iframe || settings.map_iframe;
      if (settings.contact_map_link || settings.map_link) contactMapLink.value = settings.contact_map_link || settings.map_link;

      const platforms = ['whatsapp', 'instagram', 'facebook', 'twitter', 'tiktok', 'youtube', 'linkedin'];
      platforms.forEach((p) => {
        const val = settings[`social_${p}`] || settings[p] || settings.social?.[p] || '';
        socialSettings.value[p] = val ? String(val).trim() : '';

        const contactKey = `social_show_contact_${p}`;
        socialSettings.value[`show_contact_${p}`] = parseBool(
          settings[contactKey] ?? settings.social?.[contactKey],
          true
        );
      });
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

onMounted(() => {
  fetchSettings();
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});

async function submit() {
  try {
    const response = await publicApi.contact(form);
    status.value = response.data.message || 'Message sent successfully. We will get back to you soon.';
    form.name = ''; form.email = ''; form.phone = ''; form.message = '';
  } catch (error) {
    status.value = 'Failed to send message. Please try again.';
  }
}
</script>
