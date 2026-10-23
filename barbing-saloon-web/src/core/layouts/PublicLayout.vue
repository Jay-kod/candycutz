<template>
  <div class="min-h-screen bg-theme-bg text-theme-text flex flex-col transition-colors duration-300">
    <header class="public-header">
      <div class="public-nav-shell" :class="{ 'public-nav-shell--scrolled': y > 20 || route.path !== '/' }">
        <RouterLink to="/" class="public-brand" :aria-label="`${brandName} home`">
          <img src="/images/logo-icon.png" alt="" class="public-brand__mark" />
          <BrandWordmark class="public-brand__name" :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />
        </RouterLink>

        <nav class="public-nav-links" aria-label="Main navigation">
          <RouterLink to="/" exact-active-class="public-nav-link--active" class="public-nav-link">Home</RouterLink>
          <RouterLink to="/about" active-class="public-nav-link--active" class="public-nav-link">About</RouterLink>
          <RouterLink to="/services" active-class="public-nav-link--active" class="public-nav-link">Services</RouterLink>
          <RouterLink to="/gallery" active-class="public-nav-link--active" class="public-nav-link">Gallery</RouterLink>
          <RouterLink to="/contact" active-class="public-nav-link--active" class="public-nav-link">Contact</RouterLink>
        </nav>

        <div class="public-nav-actions flex items-center">
          <ThemeToggleSwitch />
          <RouterLink to="/customer/register" class="public-signup">Sign Up</RouterLink>
        </div>

        <button
          type="button"
          class="public-menu-toggle"
          :aria-expanded="mobileMenuOpen"
          aria-controls="public-mobile-menu"
          :aria-label="mobileMenuOpen ? 'Close navigation menu' : 'Open navigation menu'"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
          </svg>
        </button>

        <nav
          id="public-mobile-menu"
          v-show="mobileMenuOpen"
          class="public-mobile-menu"
          aria-label="Mobile navigation"
        >
          <RouterLink to="/" exact-active-class="public-nav-link--active" class="public-mobile-link" @click="mobileMenuOpen = false">Home</RouterLink>
          <RouterLink to="/about" active-class="public-nav-link--active" class="public-mobile-link" @click="mobileMenuOpen = false">About</RouterLink>
          <RouterLink to="/services" active-class="public-nav-link--active" class="public-mobile-link" @click="mobileMenuOpen = false">Services</RouterLink>
          <RouterLink to="/gallery" active-class="public-nav-link--active" class="public-mobile-link" @click="mobileMenuOpen = false">Gallery</RouterLink>
          <RouterLink to="/contact" active-class="public-nav-link--active" class="public-mobile-link" @click="mobileMenuOpen = false">Contact</RouterLink>
          <div class="py-2 px-3 flex items-center justify-between border-y border-white/10 my-1">
            <span class="text-xs font-bold uppercase tracking-wider text-theme-muted">Theme</span>
            <ThemeToggleSwitch />
          </div>
          <RouterLink to="/customer/register" class="public-mobile-signup" @click="mobileMenuOpen = false">Sign Up</RouterLink>
        </nav>
      </div>
    </header>

    <main class="flex-grow"><slot /></main>

    <!-- Redesigned Footer (Modern SaaS Structure with Real Website Content) -->
    <footer class="border-t border-white/10 bg-[#070708] pt-16 pb-12 text-zinc-400">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <!-- Top Section: 3 Spaced Columns (Brand Identity, Explore, Legal & Policies) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-10 lg:gap-12 xl:gap-16 items-start text-left">
          <!-- Section 1: Brand & Shop Identity -->
          <div class="md:col-span-2 lg:col-span-1 space-y-4 flex flex-col items-start">
            <RouterLink to="/" class="inline-flex items-center justify-start gap-2.5 text-2xl font-bold tracking-tight text-white font-display group">
              <img src="/images/logo-icon.png" :alt="`${brandName} logo`" class="h-8 w-8 object-contain rounded-full shadow-[0_0_12px_rgba(255,153,0,0.3)] transition-transform duration-300 group-hover:scale-105" />
              <BrandWordmark class="tracking-tight" :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />
            </RouterLink>
            <p class="max-w-sm text-xs leading-relaxed text-zinc-400 sm:text-[13px]">
              {{ brandTagline }}
            </p>

            <div class="space-y-3 pt-1 text-xs text-zinc-400">
              <div v-if="shopAddress" class="flex items-start gap-2 text-zinc-400">
                <MapPinIcon class="h-4 w-4 text-gold shrink-0 mt-0.5" />
                <span class="leading-snug">{{ shopAddress }}</span>
              </div>
              <div class="flex items-center gap-2 text-zinc-400">
                <ClockIcon class="h-4 w-4 text-gold shrink-0" />
                <span>Mon – Sat: 8:00 AM – 9:00 PM • Sun: 12:00 PM – 8:00 PM</span>
              </div>
            </div>

            <div class="pt-1">
              <RouterLink
                to="/customer/login"
                class="inline-flex items-center gap-2 rounded-lg bg-gold/10 border border-gold/30 px-3.5 py-1.5 text-xs font-semibold text-gold transition-all duration-300 hover:bg-gold/20 hover:border-gold hover:shadow-[0_0_16px_rgba(255,153,0,0.2)]"
              >
                <span>Book Appointment</span>
                <span class="text-xs font-bold">→</span>
              </RouterLink>
            </div>
          </div>

          <!-- Section 2: Explore -->
          <div class="md:col-span-1 md:pl-6 lg:pl-10">
            <h3 class="text-sm font-semibold tracking-tight text-white mb-3">Explore</h3>
            <ul class="flex flex-col items-start space-y-2.5 text-left text-xs sm:text-sm">
              <li><RouterLink to="/" class="hover:text-gold transition-colors">Home</RouterLink></li>
              <li><RouterLink to="/services" class="hover:text-gold transition-colors">Services</RouterLink></li>
              <li><RouterLink to="/gallery" class="hover:text-gold transition-colors">Gallery</RouterLink></li>
              <li><RouterLink to="/about" class="hover:text-gold transition-colors">About Us</RouterLink></li>
              <li><RouterLink to="/contact" class="hover:text-gold transition-colors">Contact Shop</RouterLink></li>
            </ul>
          </div>

          <!-- Section 3: Legal & Policies -->
          <div class="md:col-span-1">
            <h3 class="text-sm font-semibold tracking-tight text-white mb-3">Legal & Policies</h3>
            <ul class="flex flex-col items-start space-y-2.5 text-left text-xs sm:text-sm">
              <li><RouterLink to="/privacy" class="hover:text-gold transition-colors">Privacy Policy</RouterLink></li>
              <li><RouterLink to="/terms" class="hover:text-gold transition-colors">Terms of Service</RouterLink></li>
              <li><RouterLink to="/account-deletion" class="hover:text-gold transition-colors">Account Deletion</RouterLink></li>
              <li><RouterLink to="/about" class="hover:text-gold transition-colors">Hygiene Standards</RouterLink></li>
              <li><RouterLink to="/contact" class="hover:text-gold transition-colors">Support Desk</RouterLink></li>
            </ul>
          </div>
        </div>

        <!-- Horizontal Divider with Centered Social Icons Embedded in the Line -->
        <div class="relative flex items-center my-10">
          <div class="flex-grow border-t border-white/10"></div>
          <div v-if="activeFooterSocials.length > 0" class="flex items-center gap-4 px-4 sm:px-6 text-zinc-400">
            <a
              v-for="item in activeFooterSocials"
              :key="item.key"
              :href="item.url"
              target="_blank"
              rel="noopener noreferrer"
              class="hover:text-gold transition-colors duration-200"
              :aria-label="item.label"
            >
              <SocialIcon :name="item.key" class="h-4 w-4" />
            </a>
          </div>
          <div class="flex-grow border-t border-white/10"></div>
        </div>

        <!-- Centered Legal Links Separated by Pipes (Exact reference design) -->
        <div class="mt-8 flex flex-wrap justify-start items-center gap-x-4 gap-y-2 text-xs text-zinc-400">
          <RouterLink to="/legal" class="hover:text-gold transition-colors">Legal Stuff</RouterLink>
          <span class="text-white/20 select-none">|</span>
          <RouterLink to="/privacy" class="hover:text-gold transition-colors">Privacy Policy</RouterLink>
          <span class="text-white/20 select-none">|</span>
          <RouterLink to="/security" class="hover:text-gold transition-colors">Security</RouterLink>
          <span class="text-white/20 select-none">|</span>
          <RouterLink to="/cookies" class="hover:text-gold transition-colors">Manage Cookies</RouterLink>
        </div>

        <!-- Copyright Line -->
        <p class="footer-copyright mt-4 text-center text-xs text-zinc-500">
          Copyright &copy; {{ new Date().getFullYear() }}
          <BrandWordmark :first="brandNameFirst" :second="brandNameSecond" :gold-first="brandGoldPart === 'first'" :space-between="brandNameSeparator === 'space'" />,
          Inc.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useWindowScroll } from '@vueuse/core';
import { publicApi } from '@/shared/api/old_publicApi';
import ThemeToggleSwitch from '@/components/ThemeToggleSwitch.vue';
import SocialIcon from '@/components/SocialIcon.vue';
import { MapPinIcon, ClockIcon } from '@heroicons/vue/24/outline';
import { useBrandIdentity, applyBrandSettings } from '@/shared/composables/useBrandIdentity';
import BrandWordmark from '@/shared/ui/BrandWordmark.vue';

const route = useRoute();
const { y } = useWindowScroll();

const mobileMenuOpen = ref(false);
watch(() => route.fullPath, () => {
  mobileMenuOpen.value = false;
});

const { brandName, brandNameFirst, brandNameSecond, brandNameSeparator, brandGoldPart, brandTagline } = useBrandIdentity();
const shopAddress = ref('Angwan Kare, BCG, Keffi, Nasarawa State');
const shopPhone = ref('+234 812 345 6789');
const shopEmail = ref('info@candycutz.com');

const socialSettings = ref({
  whatsapp: '',
  instagram: '',
  facebook: '',
  twitter: '',
  tiktok: '',
  youtube: '',
  linkedin: '',
  show_footer_whatsapp: true,
  show_footer_instagram: true,
  show_footer_facebook: true,
  show_footer_twitter: true,
  show_footer_tiktok: true,
  show_footer_youtube: true,
  show_footer_linkedin: true,
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

const activeFooterSocials = computed(() => {
  const s = socialSettings.value;
  const list = [];
  if (s.whatsapp && s.show_footer_whatsapp) {
    list.push({ key: 'whatsapp', label: 'WhatsApp', url: formatWhatsAppUrl(s.whatsapp) });
  }
  if (s.instagram && s.show_footer_instagram) {
    list.push({ key: 'instagram', label: 'Instagram', url: s.instagram });
  }
  if (s.facebook && s.show_footer_facebook) {
    list.push({ key: 'facebook', label: 'Facebook', url: s.facebook });
  }
  if (s.twitter && s.show_footer_twitter) {
    list.push({ key: 'twitter', label: 'X (formerly Twitter)', url: s.twitter });
  }
  if (s.tiktok && s.show_footer_tiktok) {
    list.push({ key: 'tiktok', label: 'TikTok', url: s.tiktok });
  }
  if (s.youtube && s.show_footer_youtube) {
    list.push({ key: 'youtube', label: 'YouTube', url: s.youtube });
  }
  if (s.linkedin && s.show_footer_linkedin) {
    list.push({ key: 'linkedin', label: 'LinkedIn', url: s.linkedin });
  }
  return list;
});


const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data?.data || {};

    applyBrandSettings({
      ...settings,
      brand_tagline: settings.brand_tagline || settings.site_description || settings.meta_description || settings.about_teaser_text,
    });
    if (settings.address || settings.contact_address) {
      shopAddress.value = settings.address || settings.contact_address;
    }
    if (settings.phone || settings.contact_phone) {
      shopPhone.value = settings.phone || settings.contact_phone;
    }
    if (settings.email || settings.contact_email) {
      shopEmail.value = settings.email || settings.contact_email;
    }

    const platforms = ['whatsapp', 'instagram', 'facebook', 'twitter', 'tiktok', 'youtube', 'linkedin'];
    platforms.forEach((p) => {
      const val = settings[`social_${p}`] || settings[p] || settings.social?.[p] || '';
      socialSettings.value[p] = val ? String(val).trim() : '';

      const footerKey = `social_show_footer_${p}`;
      socialSettings.value[`show_footer_${p}`] = parseBool(
        settings[footerKey] ?? settings.social?.[footerKey],
        true
      );
    });
  } catch (error) {
    console.error('Failed to load settings in footer:', error);
  }
};

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.public-header {
  position: fixed;
  z-index: 50;
  top: 0;
  right: 0;
  left: 0;
  padding: 0.5rem 1rem;
}

.public-nav-shell {
  position: relative;
  display: grid;
  width: min(100%, 56rem);
  min-height: 48px;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 1rem;
  margin: 0 auto;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  padding: 0.35rem 0.5rem 0.35rem 0.85rem;
  background: rgba(13, 13, 13, 0.82);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  color: #FAFAF8;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.08);
  transition: border-color 200ms ease, background-color 200ms ease, box-shadow 200ms ease;
}

.public-nav-shell--scrolled {
  border-color: rgba(255, 153, 0, 0.3);
  background: rgba(13, 13, 13, 0.95);
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.65), 0 0 16px rgba(255, 153, 0, 0.1);
}

:global([data-theme="light"]) .public-nav-shell {
  background: rgba(255, 255, 255, 0.88);
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  color: #111827;
}

:global([data-theme="light"]) .public-nav-shell--scrolled {
  background: rgba(255, 255, 255, 0.98);
  border-color: rgba(255, 153, 0, 0.4);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.public-brand {
  display: inline-flex;
  min-width: 0;
  align-items: center;
  gap: 0.55rem;
  color: #FAFAF8;
  font-size: 1rem;
  font-weight: 750;
  letter-spacing: -0.025em;
  text-decoration: none;
}

.public-brand__name {
  color: inherit;
}

.footer-copyright {
  display: flex;
  width: 100%;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: center;
  column-gap: 0.25rem;
  text-align: center;
}

.public-brand__mark {
  width: 28px;
  height: 28px;
  flex: 0 0 auto;
  border-radius: 50%;
  object-fit: contain;
}

.public-nav-links,
.public-nav-actions {
  display: flex;
  align-items: center;
}

.public-theme-toggle {
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 0.3rem 0.6rem;
  background: rgba(255, 255, 255, 0.04);
  color: rgba(250, 250, 248, 0.75);
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 150ms ease;
}

:global([data-theme="light"]) .public-theme-toggle {
  border-color: rgba(0, 0, 0, 0.1);
  background: rgba(0, 0, 0, 0.03);
  color: #4B5563;
}

.public-theme-toggle:hover {
  border-color: rgba(255, 153, 0, 0.4);
  color: #FFB84D;
  background: rgba(255, 153, 0, 0.08);
}

.public-nav-links {
  justify-content: center;
  gap: clamp(0.8rem, 2.1vw, 1.5rem);
}

.public-nav-link,
.public-login {
  position: relative;
  color: rgba(250, 250, 248, 0.78);
  font-size: 0.8rem;
  font-weight: 550;
  text-decoration: none;
  padding: 0.35rem 0.45rem;
  border-radius: 6px;
  transition: color 150ms ease, background-color 150ms ease;
}

:global([data-theme="light"]) .public-nav-link,
:global([data-theme="light"]) .public-login {
  color: #4B5563;
}

.public-nav-link:hover,
.public-login:hover {
  color: #FFB84D;
}

.public-nav-link--active {
  color: #FF9900 !important;
  font-weight: 650;
}

.public-nav-link--active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 15%;
  right: 15%;
  height: 2px;
  background: linear-gradient(90deg, #FF9900, #FFB84D);
  border-radius: 9999px;
  box-shadow: 0 0 8px rgba(255, 153, 0, 0.6);
}

.public-nav-actions {
  justify-content: flex-end;
  gap: 0.9rem;
}

.public-signup,
.public-mobile-signup {
  display: inline-flex;
  min-height: 34px;
  align-items: center;
  justify-content: center;
  border: 1px solid #FF9900;
  border-radius: 9px;
  padding: 0.38rem 0.95rem;
  background: linear-gradient(135deg, #FFB84D, #FF9900, #CC7A00);
  color: #0D0D0D;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-decoration: none;
  box-shadow: 0 2px 12px rgba(255, 153, 0, 0.28);
  transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
}

.public-signup:hover,
.public-mobile-signup:hover {
  transform: translateY(-1px);
  filter: brightness(1.1);
  box-shadow: 0 4px 18px rgba(255, 153, 0, 0.45);
}

.public-signup:active,
.public-mobile-signup:active {
  transform: translateY(0) scale(0.98);
}

.public-menu-toggle,
.public-mobile-menu {
  display: none;
}

:global(#features),
:global(#services) {
  scroll-margin-top: 5rem;
}

@media (max-width: 680px) {
  .public-header {
    padding: 0.55rem 0.65rem;
  }

  .public-nav-shell {
    min-height: 46px;
    grid-template-columns: 1fr auto;
    gap: 0.4rem;
    padding: 0.3rem 0.4rem 0.3rem 0.6rem;
  }

  .public-brand {
    font-size: 0.9rem;
  }

  .public-nav-links,
  .public-nav-actions {
    display: none;
  }

  .public-mobile-theme {
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    cursor: pointer;
  }

  .public-menu-toggle {
    display: inline-flex;
    width: 38px;
    height: 38px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.04);
    color: #FAFAF8;
    cursor: pointer;
    transition: all 150ms ease;
  }

  :global([data-theme="light"]) .public-menu-toggle {
    border-color: rgba(0, 0, 0, 0.1);
    color: #111827;
  }

  .public-menu-toggle:hover {
    border-color: rgba(255, 153, 0, 0.4);
    color: #FF9900;
  }

  .public-menu-toggle svg {
    width: 21px;
    height: 21px;
  }

  .public-mobile-menu {
    position: absolute;
    top: calc(100% + 0.45rem);
    right: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    border: 1px solid rgba(255, 153, 0, 0.25);
    border-radius: 14px;
    padding: 0.65rem;
    background: rgba(13, 13, 13, 0.98);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.7), 0 0 20px rgba(255, 153, 0, 0.12);
  }

  :global([data-theme="light"]) .public-mobile-menu {
    background: rgba(255, 255, 255, 0.98);
    border-color: rgba(255, 153, 0, 0.25);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12);
  }

  .public-mobile-link {
    min-height: 42px;
    display: flex;
    align-items: center;
    border-radius: 8px;
    padding: 0.6rem 0.85rem;
    color: rgba(250, 250, 248, 0.8);
    font-size: 0.925rem;
    font-weight: 550;
    text-decoration: none;
    transition: all 150ms ease;
  }

  :global([data-theme="light"]) .public-mobile-link {
    color: #374151;
  }

  .public-mobile-link:hover {
    background: rgba(255, 153, 0, 0.1);
    color: #FFB84D;
  }

  .public-mobile-link.public-nav-link--active {
    background: rgba(255, 153, 0, 0.15);
    color: #FF9900;
    font-weight: 650;
    border-left: 2px solid #FF9900;
  }

  .public-mobile-signup {
    min-height: 42px;
    margin-top: 0.35rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .public-nav-shell,
  .public-nav-link,
  .public-login,
  .public-signup,
  .public-mobile-signup {
    transition: none;
  }
}
</style>