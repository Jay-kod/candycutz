<template>
  <div class="min-h-screen bg-theme-bg text-theme-text flex flex-col transition-colors duration-300">
    <header 
      class="fixed w-full top-0 z-50 transition-all duration-300"
      :class="[(y > 20 || route.path !== '/') ? 'bg-theme-bg/95 backdrop-blur border-b border-theme-border shadow-sm' : 'bg-transparent border-transparent']"
    >
      <div class="mx-auto flex max-w-7xl items-center justify-between px-4 md:px-6 py-5 transition-all duration-300" :class="[(y > 20 || route.path !== '/') ? 'py-3' : 'py-5']">
        <RouterLink to="/" class="font-display text-3xl font-bold text-gold tracking-wide flex items-center gap-2">
          <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-8 w-8 object-contain rounded-full" />
          <span>CandyCutz</span>
        </RouterLink>
        
        <!-- Desktop Nav -->
        <nav class="hidden gap-8 md:flex items-center text-sm font-medium tracking-wide">
          <RouterLink to="/" exact-active-class="text-gold" class="hover:text-gold transition-colors">Home</RouterLink>
          <RouterLink to="/about" active-class="text-gold" class="hover:text-gold transition-colors">About us</RouterLink>
          <RouterLink to="/contact" active-class="text-gold" class="hover:text-gold transition-colors">Contact</RouterLink>
          
          <button @click="toggleDark()" class="ml-4 p-2 text-theme-text hover:text-gold transition-colors" aria-label="Toggle theme">
            <!-- Sun Icon (shown in dark mode) -->
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>
            <!-- Moon Icon (shown in light mode) -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
          </button>
          
          <RouterLink to="/customer/login" class="ml-2 rounded-full bg-gold px-5 py-2 text-obsidian hover:bg-gold-light transition-colors">Book Now</RouterLink>
        </nav>

        <!-- Mobile Menu Toggle -->
        <div class="flex items-center md:hidden gap-4">
          <button @click="toggleDark()" class="p-2 text-theme-text hover:text-gold transition-colors">
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
          </button>
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gold focus:outline-none p-2">
            <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Nav -->
      <nav v-show="mobileMenuOpen" class="md:hidden border-t border-theme-border bg-theme-bg px-4 md:px-6 py-4 flex flex-col gap-4 text-base transition-colors duration-300">
        <RouterLink to="/" @click="mobileMenuOpen = false" exact-active-class="text-gold" class="hover:text-gold transition-colors">Home</RouterLink>
        <RouterLink to="/about" @click="mobileMenuOpen = false" active-class="text-gold" class="hover:text-gold transition-colors">About us</RouterLink>
        <RouterLink to="/contact" @click="mobileMenuOpen = false" active-class="text-gold" class="hover:text-gold transition-colors">Contact</RouterLink>
        <RouterLink to="/customer/login" @click="mobileMenuOpen = false" class="mt-2 text-center rounded-full bg-gold px-5 py-3 text-obsidian font-semibold">Book Now</RouterLink>
      </nav>
    </header>

    <main class="flex-grow"><slot /></main>

    <!-- Footer -->
    <footer class="border-t border-white/10 bg-obsidian py-16 text-ivory/80">
      <div class="mx-auto max-w-7xl px-4 md:px-6 grid gap-12 md:grid-cols-4">
        <div class="md:col-span-1">
          <RouterLink to="/" class="font-display text-3xl font-bold text-gold tracking-wide flex items-center gap-2">
            <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-8 w-8 object-contain rounded-full" />
            <span>CandyCutz</span>
          </RouterLink>
          <p class="mt-4 text-sm leading-relaxed">Premium grooming with a sharper standard. Redefining the modern barbershop experience.</p>
        </div>
        <div>
          <h3 class="text-sm font-semibold tracking-widest uppercase text-gold">Explore</h3>
          <ul class="mt-4 space-y-3 text-sm">
            <li><RouterLink to="/about" class="hover:text-gold transition-colors">About us</RouterLink></li>
            <li><RouterLink to="/contact" class="hover:text-gold transition-colors">Contact Us</RouterLink></li>
            <li><RouterLink to="/customer/login" class="hover:text-gold transition-colors">Customer Portal</RouterLink></li>
          </ul>
        </div>
        <div>
          <h3 class="text-sm font-semibold tracking-widest uppercase text-gold">Company</h3>
          <ul class="mt-4 space-y-3 text-sm">
            <li><RouterLink to="/privacy" class="hover:text-gold transition-colors">Privacy Policy</RouterLink></li>
            <li><RouterLink to="/terms" class="hover:text-gold transition-colors">Terms of Service</RouterLink></li>
          </ul>
        </div>
        <div>
          <h3 class="text-sm font-semibold tracking-widest uppercase text-gold mb-4">Connect</h3>
          <div class="flex flex-wrap gap-3">
            <a v-if="socialWhatsapp" :href="socialWhatsapp" target="_blank" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-ivory/80 hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">WhatsApp</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.031 21.996c-1.63 0-3.235-.417-4.664-1.216L2.617 22l1.241-4.613a9.988 9.988 0 01-1.31-4.99c0-5.505 4.492-9.985 10.01-9.985 5.518 0 10.007 4.48 10.007 9.985 0 5.506-4.489 9.999-10.01 9.999zm-4.721-13.06c-.274-.775-.561-.79-.817-.803h-.696c-.237 0-.623.089-.95.446-.326.357-1.246 1.218-1.246 2.972 0 1.754 1.275 3.448 1.453 3.685.178.238 2.512 3.834 6.082 5.38 3.57 1.547 3.57 1.031 4.223.972.652-.06 2.106-.862 2.403-1.695.297-.833.297-1.547.208-1.696-.089-.148-.326-.238-.682-.416s-2.106-1.04-2.433-1.16c-.326-.118-.564-.178-.801.179-.237.357-.92 1.159-1.127 1.397-.208.238-.415.267-.771.089s-1.503-.553-2.863-1.77c-1.057-.946-1.771-2.116-1.979-2.473-.208-.356-.022-.55.156-.728.16-.16.356-.416.534-.624.178-.208.237-.356.356-.594.119-.237.06-.445-.03-.624-.089-.178-.801-1.93-1.098-2.644z" clip-rule="evenodd" /></svg>
            </a>
            <a v-if="socialInstagram" :href="socialInstagram" target="_blank" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-ivory/80 hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">Instagram</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
            </a>
            <a v-if="socialTiktok" :href="socialTiktok" target="_blank" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-ivory/80 hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">TikTok</span>
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.12-3.44-3.37-3.4-5.74.06-2.06.96-4.04 2.51-5.45 1.55-1.41 3.65-2.11 5.76-1.95v4.02c-1.14-.09-2.31.25-3.18.98-.87.72-1.32 1.83-1.28 2.96.04 1.13.56 2.19 1.41 2.92.85.73 1.98 1.05 3.08.91 1.54-.18 2.91-1.3 3.32-2.8.2-1.15.22-2.34.22-3.52V.02z" /></svg>
            </a>
            <a v-if="socialFacebook" :href="socialFacebook" target="_blank" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-ivory/80 hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">Facebook</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
            </a>
            <a v-if="socialTwitter" :href="socialTwitter" target="_blank" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-ivory/80 hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">Twitter</span>
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 5.922H5.078z" /></svg>
            </a>
            <span v-if="!socialInstagram && !socialTwitter && !socialFacebook && !socialWhatsapp && !socialTiktok" class="text-sm text-ivory/30">No social links</span>
          </div>
        </div>
      </div>
      <div class="mx-auto max-w-7xl px-4 md:px-6 mt-16 pt-8 border-t border-white/5 text-xs text-ivory/50 flex flex-col md:flex-row justify-between items-center gap-4">
        <p>&copy; {{ new Date().getFullYear() }} CandyCutz. All rights reserved.</p>
        <div class="flex gap-4">
          <RouterLink to="/privacy" class="hover:text-ivory transition-colors">Privacy Policy</RouterLink>
          <RouterLink to="/terms" class="hover:text-ivory transition-colors">Terms of Service</RouterLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useDark, useToggle, useWindowScroll } from '@vueuse/core';
import { publicApi } from '../../modules/public/api/public.api';

const route = useRoute();
const { y } = useWindowScroll();

const mobileMenuOpen = ref(false);

const socialInstagram = ref('');
const socialFacebook = ref('');
const socialTwitter = ref('');
const socialWhatsapp = ref('');
const socialTiktok = ref('');

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});
const toggleDark = useToggle(isDark);

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.social_instagram) socialInstagram.value = settings.social_instagram;
      if (settings.social_facebook) socialFacebook.value = settings.social_facebook;
      if (settings.social_twitter) socialTwitter.value = settings.social_twitter;
      if (settings.social_whatsapp) socialWhatsapp.value = settings.social_whatsapp;
      if (settings.social_tiktok) socialTiktok.value = settings.social_tiktok;
    }
  } catch (error) {
    console.error('Failed to load settings in footer:', error);
  }
};

onMounted(() => {
  fetchSettings();
});
</script>