<template>
  <CustomerLayout>
    <section class="mx-auto max-w-7xl px-4 md:px-6 py-20 text-theme-text">
      <div data-reveal>
        <p class="text-sm uppercase tracking-[0.3em] text-gold-light">Testimonials</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">Client feedback</h1>
      </div>

      <!-- Testimonials Skeleton -->
      <div v-if="isLoading" class="mt-10 grid gap-4 md:grid-cols-3">
        <div v-for="n in 6" :key="'test-skel-'+n" class="rounded-3xl border border-theme-border bg-theme-surface p-8">
          <div class="h-8 w-8 rounded skeleton-shimmer mb-4"></div>
          <div class="flex gap-1 mb-4">
            <div v-for="s in 5" :key="'star-'+s" class="h-5 w-5 rounded skeleton-shimmer"></div>
          </div>
          <div class="space-y-2">
            <div class="h-4 w-full rounded skeleton-shimmer"></div>
            <div class="h-4 w-full rounded skeleton-shimmer"></div>
            <div class="h-4 w-3/4 rounded skeleton-shimmer"></div>
          </div>
          <div class="h-5 w-32 rounded skeleton-shimmer mt-6"></div>
        </div>
      </div>

      <!-- Testimonials Real -->
      <div v-else class="mt-10 grid gap-4 md:grid-cols-3">
        <article v-for="(item, index) in testimonials" :key="item.id" :data-reveal-delay="index * 100" data-reveal class="rounded-3xl border border-theme-border bg-theme-surface p-8 hover:border-gold/30 transition relative">
          <div class="text-gold mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 opacity-50">
              <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.674 6.192.232.226.277.428.254.543a3.73 3.73 0 01-.814 1.686.75.75 0 00.44 1.223zM8.25 10.875a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25zM10.875 12a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875-1.125a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex gap-1 mb-4">
            <span v-for="i in Number(item.rating)" :key="i" class="text-gold text-lg">★</span>
            <span v-for="i in (5 - Number(item.rating))" :key="'e'+i" class="text-gray-600 text-lg">★</span>
          </div>
          <p class="text-sm md:text-base leading-7 text-theme-muted italic">"{{ item.review }}"</p>
          <p class="mt-6 font-semibold text-gold tracking-wide">— {{ item.client_name }}</p>
        </article>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const testimonials = ref([]);
const isLoading = ref(true);

const { init: initScrollReveal } = useScrollReveal();

onMounted(async () => {
  try {
    const response = await publicApi.testimonials();
    testimonials.value = response.data.data;
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (error) {
    console.error('Failed to load testimonials:', error);
    isLoading.value = false;
  }
});
</script>