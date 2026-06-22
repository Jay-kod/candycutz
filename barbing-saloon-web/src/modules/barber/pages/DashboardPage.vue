<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Welcome Banner -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-gold/8 blur-3xl"></div>
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Barber Dashboard</p>
              <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
                Today's <span class="text-gold">Chair View</span>
              </h1>
              <p class="mt-2 max-w-2xl text-sm text-ivory/50 leading-relaxed">
                Review today's customers, manage your schedule, and close out appointments after the cut.
              </p>
            </div>
            
            <div class="flex items-center gap-3 bg-theme-surface/40 p-3 rounded-xl border border-white/5 backdrop-blur-md shrink-0">
              <div class="flex flex-col items-end mr-2">
                <span class="text-xs font-bold uppercase tracking-wider text-theme-text">My Status</span>
                <span class="text-[10px]" :class="myStatus === 'suspended' ? 'text-red-400' : (myStatus === 'pending_approval' ? 'text-amber-400' : 'text-ivory/40')">
                  {{ formatStatus(myStatus) }}
                </span>
              </div>
              <template v-if="myStatus === 'active' || myStatus === 'on_leave'">
                <button 
                  @click="toggleMyStatus('active')"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase transition-all"
                  :class="myStatus === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-theme-bg text-ivory/30 hover:bg-white/5 border border-transparent'"
                >Active</button>
                <button 
                  @click="toggleMyStatus('on_leave')"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase transition-all"
                  :class="myStatus === 'on_leave' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-theme-bg text-ivory/30 hover:bg-white/5 border border-transparent'"
                >Not Active</button>
              </template>
              <template v-else>
                <div class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase border"
                     :class="myStatus === 'suspended' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'">
                  {{ myStatus === 'suspended' ? 'Suspended by Admin' : 'Pending Admin Approval' }}
                </div>
              </template>
            </div>
          </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <article
          v-for="(item, index) in statsCards"
          :key="item.label"
          class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-5 backdrop-blur-sm transition-all duration-300 hover:border-gold/20 hover:shadow-[0_0_30px_rgba(212,175,55,0.08)]"
        >
          <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-100"
            :class="item.gradient"
          ></div>
          <div class="relative z-10 min-h-[100px] flex flex-col">
            <!-- Fainted icon top right -->
            <div class="absolute -top-2 -right-2 opacity-15 pointer-events-none transition-all duration-300 group-hover:scale-110 group-hover:opacity-25 group-hover:-translate-y-1 group-hover:rotate-6">
              <component :is="item.icon" class="h-20 w-20" :class="item.iconColor" />
            </div>
            
            <div class="mt-1">
              <p class="text-5xl font-extrabold text-theme-text font-sans tracking-tight leading-none">{{ item.value }}</p>
            </div>
            
            <div class="mt-auto pt-6">
              <p class="text-xs text-ivory/50 uppercase tracking-widest font-medium">{{ item.label }}</p>
            </div>
          </div>
        </article>
      </div>

      <!-- Main Grid: Appointments + Actions -->
      <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <!-- Today's Appointments -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
          <div class="flex items-center justify-between border-b border-theme-border px-6 py-5">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10">
                <CalendarDaysIcon class="h-5 w-5 text-gold" />
              </div>
              <h2 class="font-display text-lg text-theme-text">Today's Appointments</h2>
            </div>
            <RouterLink
              to="/barber/appointments"
              class="flex items-center gap-1 text-xs font-semibold text-gold/70 hover:text-gold transition-colors"
            >
              View all
              <ChevronRightIcon class="h-4 w-4" />
            </RouterLink>
          </div>

          <div class="divide-y divide-white/[0.04]">
            <div
              v-for="appointment in (dashboard.today_appointments || [])"
              :key="appointment.id"
              class="group flex items-start gap-4 px-6 py-4 transition-colors hover:bg-theme-surface/50"
            >
              <!-- Time Indicator -->
              <div class="flex flex-col items-center pt-1">
                <div class="h-2.5 w-2.5 rounded-full border-2 border-gold bg-gold/30 shadow-[0_0_8px_rgba(212,175,55,0.4)]"></div>
                <div class="mt-1 h-full w-px bg-gradient-to-b from-gold/30 to-transparent"></div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-semibold text-theme-text truncate">{{ appointment.client_name }}</p>
                  <span class="shrink-0 text-xs text-gold/60 font-medium tracking-wider">{{ appointment.appointment_time }}</span>
                </div>
                <p class="mt-0.5 text-xs text-ivory/40">{{ appointment.service?.name || 'General' }}</p>
                <div class="mt-2.5 flex items-center gap-2">
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                    :class="statusClass(appointment.status)"
                  >
                    {{ appointment.status }}
                  </span>
                  <button
                    @click="complete(appointment.id)"
                    class="inline-flex items-center gap-1 rounded-full bg-gold/10 px-2.5 py-0.5 text-[10px] font-semibold text-gold uppercase tracking-wider hover:bg-gold/20 transition-colors"
                  >
                    <CheckIcon class="h-3 w-3" />
                    Complete
                  </button>
                  <button
                    @click="markNoShow(appointment.id)"
                    class="inline-flex items-center gap-1 rounded-full border border-theme-border px-2.5 py-0.5 text-[10px] font-semibold text-ivory/40 uppercase tracking-wider hover:border-red-400/30 hover:text-red-400 transition-colors"
                  >
                    No show
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty state -->
            <div v-if="(dashboard.today_appointments || []).length === 0" class="px-6 py-12 text-center">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/[0.04]">
                <CalendarDaysIcon class="h-7 w-7 text-ivory/20" />
              </div>
              <p class="mt-4 text-sm text-ivory/30">No appointments scheduled for today.</p>
              <p class="mt-1 text-xs text-ivory/20">Enjoy your day off or check your schedule.</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-4">
          <!-- Action Card -->
          <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm p-6">
            <div class="flex items-center gap-3 mb-5">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10">
                <BoltIcon class="h-5 w-5 text-gold" />
              </div>
              <h2 class="font-display text-lg text-theme-text">Quick Actions</h2>
            </div>
            <div class="space-y-2">
              <RouterLink
                v-for="action in quickActions"
                :key="action.to"
                :to="action.to"
                class="group flex items-center gap-3 rounded-xl border border-theme-border bg-theme-surface/50 px-4 py-3.5 transition-all duration-200 hover:border-gold/15 hover:bg-gold/5"
              >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors"
                  :class="action.iconBg"
                >
                  <component :is="action.icon" class="h-4 w-4" :class="action.iconColor" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-theme-text group-hover:text-gold transition-colors">{{ action.label }}</p>
                  <p class="text-[11px] text-ivory/30">{{ action.description }}</p>
                </div>
                <ChevronRightIcon class="h-4 w-4 text-ivory/20 transition-all group-hover:text-gold/60 group-hover:translate-x-0.5" />
              </RouterLink>
              <button
                @click="showNotificationModal = true"
                class="w-full group flex items-center gap-3 rounded-xl border border-theme-border bg-theme-surface/50 px-4 py-3.5 transition-all duration-200 hover:border-gold/15 hover:bg-gold/5"
              >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors bg-purple-400/10 group-hover:bg-purple-400/20">
                  <MegaphoneIcon class="h-4 w-4 text-purple-400" />
                </div>
                <div class="flex-1 min-w-0 text-left">
                  <p class="text-sm font-medium text-theme-text group-hover:text-gold transition-colors">Notify Customers</p>
                  <p class="text-[11px] text-ivory/30">Send a quick update</p>
                </div>
                <ChevronRightIcon class="h-4 w-4 text-ivory/20 transition-all group-hover:text-gold/60 group-hover:translate-x-0.5" />
              </button>
            </div>
          </div>

          <!-- Today's Overview Mini Card -->
          <div class="rounded-2xl border border-theme-border bg-gradient-to-br from-gold/10 via-charcoal/80 to-charcoal/80 backdrop-blur-sm p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/15">
                <ClockIcon class="h-5 w-5 text-gold" />
              </div>
              <div>
                <h3 class="text-sm font-semibold text-theme-text">Today's Progress</h3>
                <p class="text-[11px] text-ivory/40">{{ formattedDate }}</p>
              </div>
            </div>
            <div class="space-y-3">
              <div>
                <div class="flex justify-between text-xs mb-1.5">
                  <span class="text-ivory/50">Appointments completed</span>
                  <span class="text-gold font-semibold">{{ dashboard.stats?.completed_bookings ?? 0 }}/{{ dashboard.stats?.today_bookings ?? 0 }}</span>
                </div>
                <div class="h-1.5 rounded-full bg-white/[0.06] overflow-hidden">
                  <div
                    class="h-full rounded-full bg-gradient-to-r from-gold to-gold-light transition-all duration-700 ease-out"
                    :style="{ width: progressWidth + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Notification Modal -->
      <transition name="fade">
        <div v-if="showNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-obsidian/80 backdrop-blur-sm" @click="showNotificationModal = false"></div>
          <div class="relative w-full max-w-md rounded-2xl border border-theme-border bg-theme-surface shadow-2xl overflow-hidden animate-slide-up">
            <div class="border-b border-theme-border bg-theme-bg/30 px-6 py-4 flex justify-between items-center">
              <h3 class="font-display text-lg text-theme-text">Send Notification</h3>
              <button @click="showNotificationModal = false" class="text-ivory/40 hover:text-white"><XMarkIcon class="h-5 w-5"/></button>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <label class="text-xs text-theme-muted ml-1">Title</label>
                <input v-model="notificationForm.title" type="text" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-gold/50 focus:ring-2 focus:ring-gold/20 mt-1" placeholder="e.g. I am around now!" />
              </div>
              <div>
                <label class="text-xs text-theme-muted ml-1">Message</label>
                <textarea v-model="notificationForm.message" rows="3" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-sm text-theme-text outline-none transition-all focus:border-gold/50 focus:ring-2 focus:ring-gold/20 mt-1 resize-none" placeholder="Let your customers know you are available or running late..."></textarea>
              </div>
              <button @click="sendNotification" :disabled="sendingNotification" class="w-full rounded-xl bg-gradient-to-r from-gold to-gold-light py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-50 mt-2">
                {{ sendingNotification ? 'Sending...' : 'Send to All Customers' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </section>
  </BarberLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import {
  CalendarDaysIcon,
  ClockIcon,
  ChevronRightIcon,
  CheckIcon,
  UserGroupIcon,
  ArrowTrendingUpIcon,
  ExclamationTriangleIcon,
  CalendarIcon,
  ClipboardDocumentCheckIcon,
  UserIcon,
  MegaphoneIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';
import { BoltIcon } from '@heroicons/vue/24/solid';
import { useToast } from '../../../core/composables/useToast';

const toast = useToast();

const dashboard = ref({ stats: {}, today_appointments: [] });
const myStatus = ref('active');
const showNotificationModal = ref(false);
const sendingNotification = ref(false);
const notificationForm = ref({ title: '', message: '' });

function formatStatus(status) {
  switch (status) {
    case 'active': return 'Available';
    case 'on_leave': return 'Not Active';
    case 'pending_approval': return 'Pending';
    case 'suspended': return 'Suspended';
    default: return status;
  }
}

const statsCards = computed(() => [
  {
    label: 'Today',
    value: dashboard.value.stats?.today_bookings ?? 0,
    icon: CalendarDaysIcon,
    iconBg: 'bg-gold/10',
    iconColor: 'text-gold',
    gradient: 'from-gold/[0.03] to-transparent',
  },
  {
    label: 'Upcoming',
    value: dashboard.value.stats?.upcoming_bookings ?? 0,
    icon: ArrowTrendingUpIcon,
    iconBg: 'bg-blue-400/10',
    iconColor: 'text-blue-400',
    gradient: 'from-blue-400/[0.03] to-transparent',
  },
  {
    label: 'Completed',
    value: dashboard.value.stats?.completed_bookings ?? 0,
    icon: UserGroupIcon,
    iconBg: 'bg-emerald-400/10',
    iconColor: 'text-emerald-400',
    gradient: 'from-emerald-400/[0.03] to-transparent',
  },
  {
    label: 'No Shows',
    value: dashboard.value.stats?.no_show_count ?? 0,
    icon: ExclamationTriangleIcon,
    iconBg: 'bg-red-400/10',
    iconColor: 'text-red-400',
    gradient: 'from-red-400/[0.03] to-transparent',
  },
]);

const quickActions = [
  {
    label: 'Open Schedule',
    description: 'Manage working hours',
    to: '/barber/schedule',
    icon: CalendarIcon,
    iconBg: 'bg-gold/10 group-hover:bg-gold/20',
    iconColor: 'text-gold',
  },
  {
    label: 'Appointments',
    description: 'Review all bookings',
    to: '/barber/appointments',
    icon: ClipboardDocumentCheckIcon,
    iconBg: 'bg-blue-400/10 group-hover:bg-blue-400/20',
    iconColor: 'text-blue-400',
  },
  {
    label: 'My Profile',
    description: 'Update your details',
    to: '/barber/profile',
    icon: UserIcon,
    iconBg: 'bg-emerald-400/10 group-hover:bg-emerald-400/20',
    iconColor: 'text-emerald-400',
  },
];

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'short',
    day: 'numeric',
  });
});

const progressWidth = computed(() => {
  const total = dashboard.value.stats?.today_bookings ?? 0;
  const completed = dashboard.value.stats?.completed_bookings ?? 0;
  if (total === 0) return 0;
  return Math.round((completed / total) * 100);
});

function statusClass(status) {
  const s = (status || '').toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-emerald-400/10 text-emerald-400';
  if (s === 'pending') return 'bg-gold/10 text-gold';
  if (s === 'cancelled' || s === 'no_show') return 'bg-red-400/10 text-red-400';
  return 'bg-white/[0.06] text-ivory/50';
}

async function loadDashboard() {
  try {
    const response = await barberApi.dashboard();
    dashboard.value = response.data.data;
  } catch (e) {
    // API may not have data yet
  }
}

async function loadMyStatus() {
  try {
    const res = await barberApi.profile();
    if (res.data?.data) {
      myStatus.value = res.data.data.status || 'active';
    }
  } catch (e) {
    // Ignore error
  }
}

async function toggleMyStatus(status) {
  if (myStatus.value === 'suspended' || myStatus.value === 'pending_approval') {
    toast.error(`You cannot change your status while your account is ${formatStatus(myStatus.value)}`);
    return;
  }
  
  try {
    await barberApi.updateMyStatus({ status, is_available: status === 'active' });
    myStatus.value = status;
    toast.success(`Status updated to ${status === 'active' ? 'Active' : 'Not Active'}`);
  } catch (error) {
    toast.error(error.response?.data?.error || 'Failed to update status');
  }
}

async function sendNotification() {
  if (!notificationForm.value.title || !notificationForm.value.message) {
    toast.error('Please enter a title and message');
    return;
  }
  
  sendingNotification.value = true;
  try {
    await barberApi.sendNotification(notificationForm.value);
    toast.success('Notification sent successfully!');
    showNotificationModal.value = false;
    notificationForm.value = { title: '', message: '' };
  } catch (error) {
    toast.error('Failed to send notification');
  } finally {
    sendingNotification.value = false;
  }
}

async function complete(id) {
  await barberApi.complete(id);
  await loadDashboard();
}

async function markNoShow(id) {
  await barberApi.noShow(id);
  await loadDashboard();
}

onMounted(() => {
  loadDashboard();
  loadMyStatus();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>