<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-white">
            Your <span class="text-gold">booking overview</span>
          </h1>
          <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
            Track your upcoming appointments, manage your profile, and keep your review history in one place.
          </p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
        <article v-for="item in statsCards" :key="item.label" class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:border-gold/30 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]">
          <!-- Watermark icon top-right background -->
          <div class="absolute -right-4 -top-4 opacity-[0.06] transition-all duration-700 group-hover:opacity-[0.12] group-hover:scale-110 group-hover:rotate-6 pointer-events-none" :class="item.colorClass">
            <component :is="item.icon" class="w-28 h-28" />
          </div>
          <div class="relative z-10 flex items-start gap-4">
            <!-- Large icon on left, no background -->
            <component :is="item.icon" class="w-10 h-10 shrink-0 mt-0.5 transition-colors duration-300" :class="item.colorClass" />
            <div class="flex-1 min-w-0">
              <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-muted">{{ item.label }}</p>
              <p class="mt-2 font-display text-4xl text-theme-text group-hover:text-gold transition-colors">{{ item.value }}</p>
            </div>
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
            <div v-for="booking in dashboard.upcoming_appointments || []" :key="booking.id" class="group rounded-xl border border-theme-border bg-theme-bg/50 p-5 transition-all duration-300 hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-[0_0_20px_rgba(212,175,55,0.05)]">
              <div class="flex items-start justify-between">
                <div class="flex gap-4">
                  <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gold/10 text-gold border border-gold/20 group-hover:scale-110 transition-transform">
                    <CalendarIcon class="h-6 w-6" />
                  </div>
                  <div>
                    <p class="font-display text-lg text-theme-text group-hover:text-gold transition-colors">{{ booking.service?.name }}</p>
                    <div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 text-sm text-theme-muted">
                      <span class="flex items-center gap-1"><ClockIcon class="h-4 w-4 text-gold/50" /> {{ booking.appointment_date }} at {{ booking.appointment_time }}</span>
                      <span class="hidden sm:block text-theme-border">•</span>
                      <span class="flex items-center gap-1"><UserIcon class="h-4 w-4 text-gold/50" /> {{ booking.barber?.name }}</span>
                    </div>
                    <div v-if="booking.status === 'confirmed' && booking.verification_code" class="mt-3">
                      <p class="text-[10px] uppercase tracking-widest text-emerald-400 mb-1 font-bold">Verification Code</p>
                      <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 rounded px-2 py-1 text-emerald-400 font-mono text-sm tracking-[0.2em] shadow-inner font-bold">
                        <span>{{ booking.verification_code }}</span>
                        <button 
                          @click.prevent="copyCode(booking.verification_code)"
                          class="p-1 hover:bg-emerald-500/20 rounded transition-colors"
                          title="Copy Code"
                        >
                          <DocumentDuplicateIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <span class="rounded-full bg-gold/10 border border-gold/20 px-3 py-1 text-xs font-semibold text-gold shadow-[0_0_10px_rgba(212,175,55,0.1)]">Upcoming</span>
              </div>
            </div>
            
            <div v-if="(dashboard.upcoming_appointments || []).length === 0" class="rounded-xl border border-dashed border-theme-border p-8 text-center">
              <p class="text-sm text-theme-muted">No upcoming appointments yet.</p>
              <RouterLink to="/" class="mt-4 inline-block text-sm font-bold text-gold hover:text-gold-light">Book a service &rarr;</RouterLink>
            </div>
          </div>
        </article>

        <!-- Quick Links -->
        <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
          <h2 class="font-display text-2xl text-theme-text">Quick <span class="text-gold">Links</span></h2>
          <div class="mt-6 space-y-3">
            <RouterLink to="/customer/dashboard/bookings" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-lg">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-theme-surface border border-theme-border text-theme-muted group-hover:text-gold group-hover:border-gold/30 transition-colors">
                  <CalendarDaysIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Bookings</p>
                  <p class="mt-0.5 text-xs text-theme-muted">Manage your appointments</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-theme-muted/50 group-hover:text-gold group-hover:translate-x-1 transition-all" />
            </RouterLink>
            <RouterLink to="/customer/dashboard/profile" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-lg">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-theme-surface border border-theme-border text-theme-muted group-hover:text-gold group-hover:border-gold/30 transition-colors">
                  <UserCircleIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Profile</p>
                  <p class="mt-0.5 text-xs text-theme-muted">Update personal details</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-theme-muted/50 group-hover:text-gold group-hover:translate-x-1 transition-all" />
            </RouterLink>
            <RouterLink to="/customer/dashboard/reviews" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-lg">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-theme-surface border border-theme-border text-theme-muted group-hover:text-gold group-hover:border-gold/30 transition-colors">
                  <ChatBubbleBottomCenterTextIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Reviews</p>
                  <p class="mt-0.5 text-xs text-theme-muted">Leave feedback</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-theme-muted/50 group-hover:text-gold group-hover:translate-x-1 transition-all" />
            </RouterLink>
            <RouterLink to="/customer/dashboard/wishlist" class="group flex items-center justify-between rounded-xl border border-theme-border bg-theme-bg/50 p-4 transition-all hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-lg">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-theme-surface border border-theme-border text-theme-muted group-hover:text-gold group-hover:border-gold/30 transition-colors">
                  <HeartIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">Wishlist</p>
                  <p class="mt-0.5 text-xs text-theme-muted">View saved styles</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-theme-muted/50 group-hover:text-gold group-hover:translate-x-1 transition-all" />
            </RouterLink>
          </div>
        </article>

        <!-- Notifications -->
        <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm lg:col-span-2">
          <h2 class="font-display text-2xl text-theme-text">Recent <span class="text-gold">Notifications</span></h2>
          <div class="mt-6 space-y-4">
            <div v-for="notif in notifications" :key="notif.id" class="flex gap-4 p-4 rounded-xl border border-theme-border bg-theme-bg/50 hover:bg-theme-surface/50 transition-colors">
              <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-gold/10 border border-gold/20 flex items-center justify-center text-gold font-display text-lg">
                {{ notif.sender_name?.charAt(0) || 'B' }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-bold text-theme-text">{{ notif.title }}</h3>
                  <span class="text-[10px] text-theme-muted">{{ new Date(notif.created_at).toLocaleDateString() }}</span>
                </div>
                <p class="mt-1 text-sm text-theme-muted">{{ notif.message }}</p>
                <p class="mt-2 text-xs text-gold/70 italic">- {{ notif.sender_name }}</p>
              </div>
            </div>
            
            <div v-if="notifications.length === 0" class="rounded-xl border border-dashed border-theme-border p-8 text-center">
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-theme-surface border border-theme-border text-theme-muted/50 mb-3">
                <BellIcon class="h-6 w-6" />
              </div>
              <p class="text-sm text-theme-muted">You have no new notifications.</p>
            </div>
          </div>
        </article>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import { customerApi } from '../api/customer.api';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { 
  CalendarIcon, 
  ClockIcon, 
  CheckCircleIcon, 
  StarIcon, 
  CalendarDaysIcon, 
  UserCircleIcon, 
  ChatBubbleBottomCenterTextIcon, 
  HeartIcon, 
  ChevronRightIcon,
  UserIcon,
  BellIcon,
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline';
import ReceiptViewer from '../../../core/components/ReceiptViewer.vue';
import { useToast } from '../../../core/composables/useToast';

const dashboard = ref({ stats: {}, upcoming_appointments: [] });
const notifications = ref([]);
const loading = ref(true);
const toast = useToast();
let pollInterval = null;

const statsCards = computed(() => [
  { label: 'Total bookings', value: dashboard.value.stats?.total_bookings ?? 0, icon: CalendarIcon, colorClass: 'text-blue-400 group-hover:text-blue-300' },
  { label: 'Upcoming', value: dashboard.value.stats?.upcoming_bookings ?? 0, icon: ClockIcon, colorClass: 'text-amber-400 group-hover:text-amber-300' },
  { label: 'Completed', value: dashboard.value.stats?.completed_bookings ?? 0, icon: CheckCircleIcon, colorClass: 'text-emerald-400 group-hover:text-emerald-300' },
  { label: 'Reviews', value: dashboard.value.stats?.reviews_count ?? 0, icon: StarIcon, colorClass: 'text-purple-400 group-hover:text-purple-300' },
]);

async function loadData() {
  try {
    const [dashRes, notifRes] = await Promise.all([
      customerApi.dashboard(),
      customerApi.notifications()
    ]);
    dashboard.value = dashRes.data.data;
    notifications.value = notifRes.data.data;
  } catch (err) {
    console.error(err);
  }
}

onMounted(() => {
  loadData();
  pollInterval = setInterval(loadData, 30000);
});

async function copyCode(code) {
  if (!code) return;
  try {
    await navigator.clipboard.writeText(code);
    toast.success('Code copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy code');
  }
}

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});
</script>