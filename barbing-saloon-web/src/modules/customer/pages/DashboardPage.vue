<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
            Your <span class="text-gold">booking overview</span>
          </h1>
          <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
            Track your upcoming appointments, manage your profile, and keep your review history in one place.
          </p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
        <article v-for="item in statsCards" :key="item.label" class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all hover:border-gold/20 hover:shadow-[0_0_20px_rgba(212,175,55,0.05)]">
          <div class="absolute right-0 top-0 -mr-4 -mt-4 opacity-5 transition-opacity group-hover:opacity-10">
            <!-- decorative bg icon (optional, using text character as a placeholder for now) -->
            <span class="font-display text-9xl text-theme-text">#</span>
          </div>
          <div class="relative z-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">{{ item.label }}</p>
            <p class="mt-2 font-display text-4xl text-theme-text group-hover:text-gold transition-colors">{{ item.value }}</p>
          </div>
        </article>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <!-- Appointments -->
        <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl text-theme-text">Upcoming <span class="text-gold">Appointments</span></h2>
            <RouterLink to="/customer/dashboard/bookings" class="text-sm font-semibold text-gold hover:text-gold-light transition-colors">View all</RouterLink>
          </div>
          <div class="mt-6 space-y-4">
            <div v-for="booking in dashboard.upcoming_appointments || []" :key="booking.id" class="rounded-xl border border-theme-border bg-theme-bg/50 p-5 transition-all hover:border-gold/30 hover:bg-theme-surface/50">
              <div class="flex items-start justify-between">
                <div>
                  <p class="font-display text-lg text-theme-text">{{ booking.service?.name }}</p>
                  <p class="mt-1 text-sm text-ivory/50">{{ booking.appointment_date }} at {{ booking.appointment_time }} with <span class="text-ivory/80 font-medium">{{ booking.barber?.name }}</span></p>
                </div>
                <span class="rounded-full bg-gold/10 px-3 py-1 text-xs font-semibold text-gold">Upcoming</span>
              </div>
            </div>
            
            <div v-if="(dashboard.upcoming_appointments || []).length === 0" class="rounded-xl border border-dashed border-theme-border p-8 text-center">
              <p class="text-sm text-ivory/40">No upcoming appointments yet.</p>
              <RouterLink to="/" class="mt-4 inline-block text-sm font-bold text-gold hover:text-gold-light">Book a service &rarr;</RouterLink>
            </div>
          </div>
        </article>

        <!-- Quick Links -->
        <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
          <h2 class="font-display text-2xl text-theme-text">Quick <span class="text-gold">Links</span></h2>
          <div class="mt-6 space-y-3">
            <RouterLink to="/customer/dashboard/bookings" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50">
              <div>
                <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Bookings</p>
                <p class="mt-0.5 text-xs text-ivory/40">Book or manage appointments</p>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-ivory/20 group-hover:text-gold transition-colors" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </RouterLink>
            <RouterLink to="/customer/dashboard/profile" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50">
              <div>
                <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Profile</p>
                <p class="mt-0.5 text-xs text-ivory/40">Update personal details & avatar</p>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-ivory/20 group-hover:text-gold transition-colors" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </RouterLink>
            <RouterLink to="/customer/dashboard/reviews" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50">
              <div>
                <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Reviews</p>
                <p class="mt-0.5 text-xs text-ivory/40">Leave or review feedback</p>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-ivory/20 group-hover:text-gold transition-colors" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </RouterLink>
            <RouterLink to="/customer/dashboard/wishlist" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50">
              <div>
                <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Wishlist</p>
                <p class="mt-0.5 text-xs text-ivory/40">View your saved styles & services</p>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-ivory/20 group-hover:text-gold transition-colors" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
            </RouterLink>
          </div>
        </article>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { customerApi } from '../api/customer.api';

const dashboard = ref({ stats: {}, upcoming_appointments: [] });

const statsCards = computed(() => [
  { label: 'Total bookings', value: dashboard.value.stats?.total_bookings ?? 0 },
  { label: 'Upcoming', value: dashboard.value.stats?.upcoming_bookings ?? 0 },
  { label: 'Completed', value: dashboard.value.stats?.completed_bookings ?? 0 },
  { label: 'Reviews', value: dashboard.value.stats?.reviews_count ?? 0 },
]);

onMounted(async () => {
  const response = await customerApi.dashboard();
  dashboard.value = response.data.data;
});
</script>