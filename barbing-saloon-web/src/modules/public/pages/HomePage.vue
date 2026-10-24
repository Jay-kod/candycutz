<template>
  <PublicLayout>
    <section class="min-h-screen min-h-[100dvh] min-h-[100svh]">
      <BarberCta
        :title="ctaTitle"
        :subtitle="ctaSubtitle"
        :primary-text="ctaPrimaryText"
        :secondary-text="heroBtn2Text"
        :background-image="heroImage"
        :image-opacity="heroImageOpacity"
        :overlay-opacity="heroOverlayOpacity"
      />
    </section>

    <!-- Features Block (@efferd/features-1) -->
    <div id="features">
      <FeaturesSection
        :badge="featuresBadge"
        :title="featuresTitle"
        :subtitle="featuresSubtitle"
        :features="featuresItems"
      />
    </div>

    <!-- Statistics Section -->
    <StatisticsSection
      :years="statsYears"
      :services="statsServices"
      :clients="statsClients"
      :barbers="statsBarbers"
    />

    <!-- About Us Teaser -->
    <section class="bg-theme-surface text-theme-text py-16 md:py-24">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div data-reveal class="grid gap-12 lg:grid-cols-2 lg:items-center">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-gold-light">{{ aboutTitle }}</p>
            <h2 class="mt-3 font-display text-4xl text-gold md:text-5xl">{{ aboutSubtitle }}</h2>
            <p class="mt-6 text-lg text-theme-muted leading-relaxed whitespace-pre-wrap">
              {{ aboutText }}
            </p>
            <div class="mt-8">
              <RouterLink to="/about" class="inline-flex items-center gap-2 rounded-xl border border-gold/30 px-8 py-4 font-semibold text-gold transition-all hover:border-gold hover:bg-gold/10 hover:-translate-y-0.5">
                Learn More About Us
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
              </RouterLink>
            </div>
          </div>
          <div class="relative overflow-hidden rounded-3xl aspect-[4/3] border border-theme-border shadow-lg">
            <img :src="aboutImage || 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'" alt="CandyCutz Shop Interior" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
          </div>
        </div>
      </div>
    </section>

    <!-- Customer Testimonials -->
    <section
      class="relative overflow-hidden border-y border-white/5 bg-obsidian py-16 text-theme-text md:py-20"
      aria-labelledby="home-testimonials-title"
    >
      <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_70%_60%_at_50%_0%,rgba(255,153,0,0.08),transparent_70%)]"
        aria-hidden="true"
      ></div>
      <div class="relative mx-auto max-w-7xl px-4 md:px-6">
        <header class="mb-8 max-w-2xl" data-reveal>
          <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold">Client stories</p>
          <h2 id="home-testimonials-title" class="mt-3 font-display text-3xl font-bold tracking-tight text-ivory sm:text-4xl">
            What our clients say
          </h2>
          <p class="mt-3 text-sm leading-relaxed text-theme-muted sm:text-base">
            A look at the kind of premium experience customers can expect.
          </p>
        </header>

        <div v-if="testimonials.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <article
            v-for="testimonial in testimonials"
            :key="testimonial.id"
            class="group flex h-full flex-col rounded-2xl border border-white/10 bg-gradient-to-b from-white/[0.045] to-white/[0.015] p-5 shadow-lg transition duration-300 hover:-translate-y-1 hover:border-gold/40 hover:shadow-[0_18px_36px_-16px_rgba(255,153,0,0.2)] sm:p-6"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1" :aria-label="`${testimonial.rating} out of 5 stars`">
                <Star
                  v-for="star in 5"
                  :key="star"
                  class="size-4"
                  :class="star <= Number(testimonial.rating) ? 'fill-gold text-gold' : 'text-white/20'"
                  aria-hidden="true"
                />
              </div>
              <Quote class="size-5 text-gold/60 transition-colors group-hover:text-gold" aria-hidden="true" />
            </div>

            <p class="mt-5 flex-1 whitespace-pre-line text-sm leading-7 text-ivory/85">
              “{{ testimonial.review }}”
            </p>

            <div class="mt-6 flex items-center gap-3 border-t border-white/10 pt-4">
              <img
                v-if="testimonial.client_avatar && !failedTestimonialAvatars.has(testimonial.id)"
                :src="getStorageUrl(testimonial.client_avatar)"
                :alt="`${testimonial.client_name} avatar`"
                class="size-10 rounded-full border border-gold/30 object-cover"
                loading="lazy"
                @error="markTestimonialAvatarFailed(testimonial.id)"
              />
              <div
                v-else
                class="flex size-10 shrink-0 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-sm font-bold text-gold"
                aria-hidden="true"
              >
                {{ getTestimonialInitials(testimonial.client_name) }}
              </div>
              <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-ivory">{{ testimonial.client_name || 'CandyCutz client' }}</p>
                <p class="mt-0.5 text-xs text-theme-muted">
                  {{ isSampleTestimonial(testimonial) ? 'Demonstration sample — not a customer review' : 'CandyCutz client' }}
                </p>
              </div>
            </div>
          </article>
        </div>
        <p v-else-if="!testimonialsLoading" class="rounded-2xl border border-white/10 bg-white/[0.02] px-5 py-8 text-center text-sm text-theme-muted">
          Customer testimonials will appear here once reviews are approved.
        </p>
      </div>
    </section>

    <!-- Customer Portal Teaser Section (Orbit Constellation Design) -->
    <div id="services">
    <PortalCtaSection
      :eyebrow="portalTitle"
      :title="portalSubtitle"
      :description="portalText"
      primary-text="Log in to Customer Portal"
      primary-link="/customer/login"
      secondary-text="Create Account"
      secondary-link="/customer/register"
    />
    </div>

  </PublicLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { Quote, Star } from 'lucide-vue-next';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { publicApi } from '@/shared/api/old_publicApi';
import { getStorageUrl } from '@/core/utils/url';
import BarberCta from '@/shared/ui/BarberCta.vue';
import FeaturesSection from '@/components/FeaturesSection.vue';
import PortalCtaSection from '@/components/PortalCtaSection.vue';
import StatisticsSection from '@/components/StatisticsSection.vue';

const { init: initScrollReveal } = useScrollReveal();

const featuresBadge = ref('');
const featuresTitle = ref('');
const featuresSubtitle = ref('');
const featuresItems = ref([]);

const heroTitle = ref('Premium grooming with a sharper standard.');
const heroSubtitle = ref('Experience the CandyCutz difference. Log in to explore our full menu of premium services, meet our expert barbers, and book your next appointment seamlessly.');
const heroBtn1Text = ref('Book a Service');
const heroBtn2Text = ref('About Us');
const heroImage = ref(null);
const heroImageOpacity = ref(null);
const heroOverlayOpacity = ref(null);
const ctaTitle = ref('Precision Cuts.\nMade for You.');
const ctaSubtitle = ref('Expert barbers. Fresh fades. A better kind of barbershop experience.');
const ctaPrimaryText = ref('Book your cut');

const statsYears = ref('10+');
const statsBarbers = ref('5+');
const statsServices = ref('20+');
const statsClients = ref('5k+');

const aboutTitle = ref('Our Philosophy');
const aboutSubtitle = ref('A Sharper Standard');
const aboutText = ref("Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.");
const aboutImage = ref(null);
const testimonials = ref([]);
const testimonialsLoading = ref(true);
const failedTestimonialAvatars = ref(new Set());

const portalTitle = ref('The CandyCutz Portal');
const portalSubtitle = ref('Unlock the Full Experience');
const portalText = ref('Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.');


const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.hero_title) heroTitle.value = settings.hero_title;
      if (settings.hero_subtitle) heroSubtitle.value = settings.hero_subtitle;
      if (settings.hero_btn1_text) heroBtn1Text.value = settings.hero_btn1_text;
      if (settings.hero_btn2_text) heroBtn2Text.value = settings.hero_btn2_text;
      if (settings.hero_image) heroImage.value = getStorageUrl(settings.hero_image);
      if (settings.hero_image_opacity !== undefined && settings.hero_image_opacity !== null) {
        heroImageOpacity.value = settings.hero_image_opacity;
      } else if (settings.hero_opacity !== undefined && settings.hero_opacity !== null) {
        heroImageOpacity.value = settings.hero_opacity;
      }
      if (settings.page_hero_home_overlay_opacity !== undefined && settings.page_hero_home_overlay_opacity !== null) {
        heroOverlayOpacity.value = settings.page_hero_home_overlay_opacity;
      }
      ctaTitle.value = settings.cta_title || settings.hero_title || ctaTitle.value;
      ctaSubtitle.value = settings.cta_subtitle || settings.hero_subtitle || ctaSubtitle.value;
      ctaPrimaryText.value = settings.cta_primary_text || settings.hero_btn1_text || ctaPrimaryText.value;
      
      statsYears.value = settings.stats_years || settings.stats_years_experience || statsYears.value;
      statsBarbers.value = settings.stats_barbers || settings.stats_experienced_barbers || statsBarbers.value;
      statsServices.value = settings.stats_services || statsServices.value;
      statsClients.value = settings.stats_clients || settings.stats_happy_clients || statsClients.value;
      
      aboutTitle.value = settings.about_teaser_title || settings.about_shop_title || aboutTitle.value;
      aboutSubtitle.value = settings.about_teaser_subtitle || settings.about_shop_subtitle || aboutSubtitle.value;
      aboutText.value = settings.about_teaser_text || settings.about_shop_text_1 || aboutText.value;
      if (settings.about_teaser_image || settings.about_shop_image) {
        aboutImage.value = getStorageUrl(settings.about_teaser_image || settings.about_shop_image);
      }
      
      if (settings.portal_teaser_title) portalTitle.value = settings.portal_teaser_title;
      if (settings.portal_teaser_subtitle) portalSubtitle.value = settings.portal_teaser_subtitle;
      if (settings.portal_teaser_text) portalText.value = settings.portal_teaser_text;

      if (settings.features_badge) featuresBadge.value = settings.features_badge;
      if (settings.features_title) featuresTitle.value = settings.features_title;
      if (settings.features_subtitle) featuresSubtitle.value = settings.features_subtitle;
      if (settings.features_items) {
        try {
          featuresItems.value = typeof settings.features_items === 'string'
            ? JSON.parse(settings.features_items)
            : settings.features_items;
        } catch {
          featuresItems.value = [];
        }
      }
    }
  } catch (error) {
    console.error('Failed to load public settings:', error);
  }
};

const fetchTestimonials = async () => {
  try {
    const response = await publicApi.testimonials({ featured: true });
    const items = response.data?.data;
    if (!Array.isArray(items)) {
      throw new TypeError('The testimonials response did not contain a list.');
    }
    testimonials.value = items
      .filter((item) => item.review)
      .slice(0, 10)
      .map((item) => ({
        ...item,
        client_name: item.client_name || item.customer_name || 'CandyCutz client',
        client_avatar: item.client_avatar || item.avatar_url || null,
      }));
  } catch (error) {
    console.error('Failed to load homepage testimonials:', error);
  } finally {
    testimonialsLoading.value = false;
  }
};

const markTestimonialAvatarFailed = (id) => {
  failedTestimonialAvatars.value = new Set([...failedTestimonialAvatars.value, id]);
};

const getTestimonialInitials = (name) => {
  const words = (name || 'Client').trim().split(/\s+/).filter(Boolean);
  return words.slice(0, 2).map((word) => word[0].toUpperCase()).join('');
};

const isSampleTestimonial = (testimonial) => testimonial.client_name?.startsWith('Sample Customer ');

onMounted(() => {
  fetchSettings();
  fetchTestimonials();
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});
</script>