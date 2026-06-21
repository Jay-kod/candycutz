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
          <h3 class="text-sm font-semibold tracking-widest uppercase text-gold">Connect</h3>
          <ul class="mt-4 space-y-3 text-sm">
            <li><a href="#" class="hover:text-gold transition-colors">Instagram</a></li>
            <li><a href="#" class="hover:text-gold transition-colors">Twitter</a></li>
            <li><a href="#" class="hover:text-gold transition-colors">Facebook</a></li>
          </ul>
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
import { ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useDark, useToggle, useWindowScroll } from '@vueuse/core';

const route = useRoute();
const { y } = useWindowScroll();

const mobileMenuOpen = ref(false);

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});
const toggleDark = useToggle(isDark);
</script>