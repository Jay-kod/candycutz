<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive Public Mockup Preview</span>
        <span class="hidden sm:inline-flex text-[10px] text-white/40 font-mono">(Live Simulation)</span>
      </div>
      <button 
        @click="$emit('exit')" 
        class="text-xs font-bold px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 text-white/70 hover:text-white hover:bg-white/10 transition-all"
      >
        Exit Preview Mode
      </button>
    </div>

    <div class="p-4 sm:p-8 space-y-12">
      <!-- 1. Simulated Hero Section -->
      <div class="relative overflow-hidden rounded-3xl border border-white/10 min-h-[380px] sm:min-h-[460px] flex items-center justify-center p-6 sm:p-12 text-center bg-black/60 shadow-inner">
        <!-- Background Image with live opacity -->
        <img 
          v-if="heroImageSrc" 
          :src="heroImageSrc" 
          alt="Hero visual simulation"
          :style="{ opacity: computedOpacity }"
          class="absolute inset-0 w-full h-full object-cover pointer-events-none transition-all duration-300"
        />
        <!-- Ambient radial glow -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0B0F13] via-transparent to-transparent pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl space-y-4">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-admin/30 bg-admin/10 text-admin text-[11px] font-bold uppercase tracking-wider">
            Premium Barbering Salon
          </div>
          <h1 class="font-display text-2xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight leading-tight drop-shadow-md">
            {{ settings.hero_title || 'Premium grooming with a sharper standard.' }}
          </h1>
          <p class="text-xs sm:text-sm text-white/70 leading-relaxed max-w-xl mx-auto">
            {{ settings.hero_subtitle || 'Experience the CandyCutz difference. Log in to explore our full menu of premium services, meet our expert barbers, and book your next appointment seamlessly.' }}
          </p>
          <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            <span class="px-6 py-3 rounded-xl bg-gradient-to-r from-admin to-admin-light text-obsidian text-xs font-bold shadow-lg pointer-events-none">
              {{ settings.hero_btn1_text || 'Book a Service' }}
            </span>
            <span class="px-6 py-3 rounded-xl border border-white/20 bg-white/[0.05] text-white text-xs font-bold pointer-events-none">
              {{ settings.hero_btn2_text || 'About Us' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Simulated Statistics Section -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-white/[0.06] bg-[#12161C] p-4 text-center space-y-1">
          <div class="font-display text-2xl sm:text-3xl font-bold text-admin">{{ settings.stats_years || '10+' }}</div>
          <div class="text-[10px] uppercase font-bold tracking-wider text-white/40">Years Experience</div>
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-[#12161C] p-4 text-center space-y-1">
          <div class="font-display text-2xl sm:text-3xl font-bold text-admin">{{ settings.stats_barbers || '5+' }}</div>
          <div class="text-[10px] uppercase font-bold tracking-wider text-white/40">Master Barbers</div>
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-[#12161C] p-4 text-center space-y-1">
          <div class="font-display text-2xl sm:text-3xl font-bold text-admin">{{ settings.stats_services || '20+' }}</div>
          <div class="text-[10px] uppercase font-bold tracking-wider text-white/40">Premium Services</div>
        </div>
        <div class="rounded-2xl border border-white/[0.06] bg-[#12161C] p-4 text-center space-y-1">
          <div class="font-display text-2xl sm:text-3xl font-bold text-admin">{{ settings.stats_clients || '5k+' }}</div>
          <div class="text-[10px] uppercase font-bold tracking-wider text-white/40">Happy Clients</div>
        </div>
      </div>

      <!-- 3. Simulated Philosophy Teaser -->
      <div class="rounded-3xl border border-white/[0.06] bg-[#12161C] p-6 sm:p-10 grid lg:grid-cols-2 gap-8 items-center">
        <div class="space-y-4">
          <span class="text-xs font-bold uppercase tracking-widest text-amber-400 font-mono">
            {{ settings.about_teaser_title || 'Our Philosophy' }}
          </span>
          <h2 class="font-display text-2xl sm:text-3xl text-white font-bold tracking-tight">
            {{ settings.about_teaser_subtitle || 'A Sharper Standard' }}
          </h2>
          <p class="text-xs sm:text-sm text-white/70 leading-relaxed">
            {{ settings.about_teaser_text || "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut." }}
          </p>
          <div class="pt-2">
            <span class="inline-flex items-center gap-2 text-xs font-bold text-amber-400 border border-amber-400/30 bg-amber-400/10 px-4 py-2 rounded-xl pointer-events-none">
              Learn More About Us →
            </span>
          </div>
        </div>
        <div class="relative overflow-hidden rounded-2xl aspect-[4/3] border border-white/10 shadow-xl">
          <img 
            :src="aboutImageSrc || 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'" 
            alt="Philosophy interior" 
            class="w-full h-full object-cover" 
          />
        </div>
      </div>

      <!-- 4. Simulated Customer Portal CTA -->
      <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-r from-obsidian via-[#161B22] to-obsidian p-8 text-center space-y-4 shadow-2xl">
        <span class="text-xs font-bold uppercase tracking-widest text-sky-400 font-mono">
          {{ settings.portal_teaser_title || 'The CandyCutz Portal' }}
        </span>
        <h2 class="font-display text-2xl sm:text-3xl text-white font-bold tracking-tight">
          {{ settings.portal_teaser_subtitle || 'Unlock the Full Experience' }}
        </h2>
        <p class="text-xs sm:text-sm text-white/60 leading-relaxed max-w-xl mx-auto">
          {{ settings.portal_teaser_text || 'Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.' }}
        </p>
        <div class="pt-3 flex flex-wrap items-center justify-center gap-3">
          <span class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-admin to-admin-light text-obsidian text-xs font-bold pointer-events-none">
            Log in to Customer Portal
          </span>
          <span class="px-5 py-2.5 rounded-xl border border-white/20 bg-white/[0.05] text-white text-xs font-bold pointer-events-none">
            Create Account
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  settings: {
    type: Object,
    required: true
  },
  previewUrl: {
    type: String,
    default: null
  },
  currentHeroImage: {
    type: String,
    default: null
  },
  previewAboutUrl: {
    type: String,
    default: null
  },
  currentAboutImage: {
    type: String,
    default: null
  },
  getStorageUrl: {
    type: Function,
    required: true
  }
});

defineEmits(['exit']);

const heroImageSrc = computed(() => {
  if (props.previewUrl) return props.previewUrl;
  if (props.currentHeroImage) return props.getStorageUrl(props.currentHeroImage);
  return 'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80';
});

const aboutImageSrc = computed(() => {
  if (props.previewAboutUrl) return props.previewAboutUrl;
  if (props.currentAboutImage) return props.getStorageUrl(props.currentAboutImage);
  return 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
});

const computedOpacity = computed(() => {
  const raw = props.settings?.hero_image_opacity;
  if (raw === undefined || raw === null || raw === '') return 0.22;
  const num = Number(raw);
  if (isNaN(num)) return 0.22;
  const clamped = Math.max(0, Math.min(100, num));
  return clamped > 1 ? clamped / 100 : clamped;
});
</script>
