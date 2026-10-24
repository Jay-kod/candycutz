<template>
  <PublicLayout>
    <BarberCta
      :title="ctaTitle"
      :title-part-one="ctaTitlePartOne"
      :title-part-two="ctaTitlePartTwo"
      :title-color-order="ctaTitleColorOrder"
      :subtitle="ctaSubtitle"
      :caption="ctaCaption"
      :primary-text="ctaPrimaryText"
      :secondary-text="heroBtn2Text"
      :background-image="heroImage"
      :image-opacity="heroImageOpacity"
      :overlay-opacity="heroOverlayOpacity"
    />

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
    <section class="bg-theme-surface text-theme-text py-16 md:py-24 border-y border-white/5 overflow-hidden">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div data-reveal class="grid gap-12 lg:grid-cols-2 lg:items-center">
          <div>
            <span class="inline-block px-3.5 py-1 mb-3.5 text-xs font-semibold tracking-widest uppercase text-gold bg-gold/10 border border-gold/20 rounded-full">
              {{ aboutTitle || 'Our Story' }}
            </span>
            <h2 class="mt-2 font-display text-3xl sm:text-4xl lg:text-5xl text-gold">
              {{ aboutSubtitle }}
            </h2>
            <p class="mt-5 text-base sm:text-lg text-theme-muted leading-relaxed whitespace-pre-wrap">
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
          <div class="relative overflow-hidden rounded-3xl aspect-[4/3] border border-theme-border shadow-2xl">
            <img :src="aboutImage || 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'" alt="CandyCutz Shop Interior" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
          </div>
        </div>
      </div>
    </section>

    <!-- Customer Testimonials (Horizontal Infinite Marquee) -->
    <TestimonialsMarquee
      :testimonials="testimonials"
      :loading="testimonialsLoading"
      :speed="testimonialsFlowSpeed"
      :direction="testimonialsFlowDirection"
    />

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
import { onMounted, onUnmounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { publicApi } from '@/shared/api/old_publicApi';
import { getStorageUrl } from '@/core/utils/url';
import BarberCta from '@/shared/ui/BarberCta.vue';
import FeaturesSection from '@/components/FeaturesSection.vue';
import PortalCtaSection from '@/components/PortalCtaSection.vue';
import StatisticsSection from '@/components/StatisticsSection.vue';
import TestimonialsMarquee from '@/components/TestimonialsMarquee.vue';

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
const ctaTitle = ref('Fresh Cut. Zero Stress.');
const ctaTitlePartOne = ref('Fresh Cut.');
const ctaTitlePartTwo = ref('Zero Stress.');
const ctaTitleColorOrder = ref('first-white');
const ctaSubtitle = ref('Pick your barber, choose your style, lock your time.');
const ctaCaption = ref('Expert barbers. Fresh fades. A better kind of barbershop experience.');
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
const testimonialsFlowSpeed = ref(45);
const testimonialsFlowDirection = ref('left');

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
      const titleParts = splitHeadline(ctaTitle.value);
      ctaTitlePartOne.value = settings.cta_title_part_one ?? titleParts[0];
      ctaTitlePartTwo.value = settings.cta_title_part_two ?? titleParts[1];
      ctaTitleColorOrder.value = settings.cta_title_color_order === 'first-gold' ? 'first-gold' : 'first-white';
      ctaSubtitle.value = settings.cta_subtitle || settings.hero_subtitle || ctaSubtitle.value;
      ctaCaption.value = settings.cta_caption ?? ctaCaption.value;
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

      if (settings.testimonials_flow_speed) {
        const parsedSpeed = Number(settings.testimonials_flow_speed);
        if (Number.isFinite(parsedSpeed) && parsedSpeed >= 10 && parsedSpeed <= 120) {
          testimonialsFlowSpeed.value = parsedSpeed;
        }
      }
      if (settings.testimonials_flow_direction) {
        const dir = String(settings.testimonials_flow_direction).toLowerCase();
        if (dir === 'left' || dir === 'right') {
          testimonialsFlowDirection.value = dir;
        }
      }
    }
  } catch (error) {
    console.error('Failed to load public settings:', error);
  }
};

const fetchTestimonials = async () => {
  try {
    const response = await publicApi.testimonials();
    const items = response.data?.data;
    if (!Array.isArray(items)) {
      throw new TypeError('The testimonials response did not contain a list.');
    }
    testimonials.value = items
      .filter((item) => item.review || item.comment)
      .slice(0, 12)
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

const splitHeadline = (title) => {
  const normalizedTitle = String(title || '').replace(/\s*\n\s*/g, ' ').trim();
  const sentenceBreak = normalizedTitle.match(/^(.+?[.!?])\s+(.+)$/);
  if (sentenceBreak) return [sentenceBreak[1], sentenceBreak[2]];

  const words = normalizedTitle.split(/\s+/);
  if (words.length < 2) return [normalizedTitle, ''];
  const midpoint = Math.ceil(words.length / 2);
  return [words.slice(0, midpoint).join(' '), words.slice(midpoint).join(' ')];
};

const refreshTestimonialsWhenVisible = () => {
  if (document.visibilityState === 'visible') {
    fetchTestimonials();
  }
};

onMounted(() => {
  fetchSettings();
  fetchTestimonials();
  document.addEventListener('visibilitychange', refreshTestimonialsWhenVisible);
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});

onUnmounted(() => {
  document.removeEventListener('visibilitychange', refreshTestimonialsWhenVisible);
});
</script>
