<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)]/80 font-medium">Customer Dashboard</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-[var(--color-text-primary)]">
            Your <span class="text-[var(--color-accent)]">booking overview</span>
          </h1>
          <p class="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)] leading-relaxed">
            Track your upcoming appointments, manage your profile, and keep your review history in one place.
          </p>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
        <article v-for="item in statsCards" :key="item.label" class="group rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 transition-colors duration-300 hover:border-[var(--color-accent)]/40 hover:shadow-lg hover:shadow-[var(--color-shadow)]">
          <div class="flex items-center gap-2 text-[var(--color-text-secondary)]">
            <component :is="item.icon" class="w-5 h-5 shrink-0" />
            <p class="text-xs font-bold uppercase tracking-wider">{{ item.label }}</p>
          </div>
          <p class="mt-3 font-display text-3xl text-[var(--color-text-primary)] tabular-nums">{{ item.value }}</p>
        </article>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <!-- Appointments -->
        <article class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Upcoming <span class="text-[var(--color-accent)]">Appointments</span></h2>
            <RouterLink to="/customer/dashboard/bookings" class="text-sm font-semibold text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] transition-colors">View all</RouterLink>
          </div>
          <div class="mt-6 space-y-4">
            <div v-for="booking in dashboard.upcoming_appointments || []" :key="booking.id" class="group rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-5 transition-colors duration-300 hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-secondary)]">
              <div class="flex items-start justify-between">
                <div class="flex gap-4">
                  <div class="hidden sm:flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/20">
                    <CalendarIcon class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="font-display text-lg text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors">{{ booking.service?.name }}</p>
                    <div class="mt-1 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 text-sm text-[var(--color-text-secondary)]">
                      <span class="flex items-center gap-1"><ClockIcon class="h-4 w-4 text-[var(--color-accent)]/60" /> {{ booking.appointment_date }} at {{ booking.appointment_time }}</span>
                      <span class="hidden sm:block text-[var(--color-border)]">•</span>
                      <span class="flex items-center gap-1"><UserIcon class="h-4 w-4 text-[var(--color-accent)]/60" /> {{ booking.barber?.name }}</span>
                    </div>
                    <div v-if="booking.status === 'confirmed' && booking.verification_code" class="mt-3">
                      <p class="text-[10px] uppercase tracking-widest text-emerald-400 mb-1 font-bold">Verification Code</p>
                      <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/30 rounded px-2 py-1 text-emerald-400 font-mono text-sm tracking-[0.2em] font-bold">
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
                <span class="rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 px-3 py-1 text-xs font-semibold text-[var(--color-accent)]">Upcoming</span>
              </div>
            </div>
            
            <div v-if="(dashboard.upcoming_appointments || []).length === 0" class="rounded-lg border border-dashed border-[var(--color-border)] p-8 text-center">
              <p class="text-sm text-[var(--color-text-muted)]">No upcoming appointments yet.</p>
              <RouterLink to="/" class="mt-4 inline-block text-sm font-bold text-[var(--color-accent)] hover:text-[var(--color-accent-hover)]">Book a service &rarr;</RouterLink>
            </div>
          </div>
        </article>

        <!-- Quick Links -->
        <article class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6">
          <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Quick <span class="text-[var(--color-accent)]">Links</span></h2>
          <div class="mt-6 space-y-3">
            <RouterLink to="/customer/dashboard/bookings" class="group flex items-center justify-between rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-4 transition-colors hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-secondary)]">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-secondary)] group-hover:text-[var(--color-accent)] group-hover:border-[var(--color-accent)]/40 transition-colors">
                  <CalendarDaysIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors">Bookings</p>
                  <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">Manage your appointments</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-[var(--color-text-muted)] group-hover:text-[var(--color-accent)] group-hover:translate-x-1 transition-all" />
            </RouterLink>
            <RouterLink to="/customer/dashboard/profile" class="group flex items-center justify-between rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-4 transition-colors hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-secondary)]">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-secondary)] group-hover:text-[var(--color-accent)] group-hover:border-[var(--color-accent)]/40 transition-colors">
                  <UserCircleIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors">Profile</p>
                  <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">Update personal details</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-[var(--color-text-muted)] group-hover:text-[var(--color-accent)] group-hover:translate-x-1 transition-all" />
            </RouterLink>
            <RouterLink to="/customer/dashboard/reviews" class="group flex items-center justify-between rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-4 transition-colors hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-secondary)]">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-secondary)] group-hover:text-[var(--color-accent)] group-hover:border-[var(--color-accent)]/40 transition-colors">
                  <ChatBubbleBottomCenterTextIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors">Reviews</p>
                  <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">Leave feedback</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-[var(--color-text-muted)] group-hover:text-[var(--color-accent)] group-hover:translate-x-1 transition-all" />
            </RouterLink>
            <RouterLink to="/customer/dashboard/wishlist" class="group flex items-center justify-between rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-4 transition-colors hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-secondary)]">
              <div class="flex items-center gap-4">
                <div class="p-2.5 rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-secondary)] group-hover:text-[var(--color-accent)] group-hover:border-[var(--color-accent)]/40 transition-colors">
                  <HeartIcon class="h-5 w-5" />
                </div>
                <div>
                  <p class="text-sm font-semibold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors">Wishlist</p>
                  <p class="mt-0.5 text-xs text-[var(--color-text-secondary)]">View saved styles</p>
                </div>
              </div>
              <ChevronRightIcon class="h-5 w-5 text-[var(--color-text-muted)] group-hover:text-[var(--color-accent)] group-hover:translate-x-1 transition-all" />
            </RouterLink>
          </div>
        </article>

        <!-- Notifications -->
        <article class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 lg:col-span-2">
          <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Recent <span class="text-[var(--color-accent)]">Notifications</span></h2>
          <div class="mt-6 space-y-4">
            <div v-for="notif in notifications" :key="notif.id" class="flex gap-4 p-4 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 hover:bg-[var(--color-bg-secondary)] transition-colors">
              <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 flex items-center justify-center text-[var(--color-accent)] font-display text-lg">
                {{ notif.sender_name?.charAt(0) || 'B' }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h3 class="font-bold text-[var(--color-text-primary)]">{{ notif.title }}</h3>
                  <span class="text-[10px] text-[var(--color-text-secondary)]">{{ new Date(notif.created_at).toLocaleDateString() }}</span>
                </div>
                <p class="mt-1 text-sm text-[var(--color-text-secondary)]">{{ notif.message }}</p>
                <p class="mt-2 text-xs text-[var(--color-accent)]/80 italic">- {{ notif.sender_name }}</p>
              </div>
            </div>
            
            <div v-if="notifications.length === 0" class="rounded-lg border border-dashed border-[var(--color-border)] p-8 text-center">
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-muted)] mb-3">
                <BellIcon class="h-6 w-6" />
              </div>
              <p class="text-sm text-[var(--color-text-muted)]">You have no new notifications.</p>
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
import { customerApi } from '@/shared/api/old_customerApi';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
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
import { useToast } from '../../../core/composables/useToast';

const dashboard = ref({ stats: {}, upcoming_appointments: [] });
const notifications = ref([]);
const toast = useToast();
let pollInterval = null;

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const statsCards = computed(() => [
  { label: 'Total bookings', value: formatNumber(dashboard.value.stats?.total_bookings), icon: CalendarIcon },
  { label: 'Upcoming', value: formatNumber(dashboard.value.stats?.upcoming_bookings), icon: ClockIcon },
  { label: 'Completed', value: formatNumber(dashboard.value.stats?.completed_bookings), icon: CheckCircleIcon },
  { label: 'Reviews', value: formatNumber(dashboard.value.stats?.reviews_count), icon: StarIcon },
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
