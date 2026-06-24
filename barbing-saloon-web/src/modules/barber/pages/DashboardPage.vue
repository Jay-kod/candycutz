<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Welcome Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-10 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-gold/5 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-gold/8 blur-[80px]"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[radial-gradient(circle_at_top_right,rgba(212,175,55,0.06),transparent_70%)]"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
          <div>
            <p class="text-[11px] uppercase tracking-[0.35em] text-gold/70 font-bold">Barber Command Center</p>
            <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg leading-tight">
              Today's <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-amber-400">Chair View</span>
            </h1>
            <p class="mt-3 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Review today's customers, manage your schedule, and close out appointments after the cut.
            </p>
          </div>

          <div class="flex items-center gap-5 shrink-0">
            <!-- Current Time -->
            <div class="hidden sm:flex flex-col items-end">
              <span class="text-2xl font-display text-theme-text tabular-nums">{{ currentTime }}</span>
              <span class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold mt-0.5">{{ currentDate }}</span>
            </div>

            <div class="h-10 w-px bg-white/10 hidden sm:block"></div>

            <!-- System Status -->
            <div class="flex items-center gap-3 bg-theme-surface/60 px-4 py-2.5 rounded-xl border border-white/5 backdrop-blur-md">
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
                  :class="myStatus === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]' : 'bg-theme-bg/50 text-ivory/30 hover:bg-white/5 border border-transparent'"
                >Active</button>
                <button 
                  @click="toggleMyStatus('on_leave')"
                  class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase transition-all"
                  :class="myStatus === 'on_leave' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30 shadow-[0_0_15px_rgba(245,158,11,0.2)]' : 'bg-theme-bg/50 text-ivory/30 hover:bg-white/5 border border-transparent'"
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
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid gap-5 grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-36 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>

      <template v-else>
        <!-- KPI Row -->
        <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
          <article v-for="card in statsCards" :key="card.label" class="group relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#1a1a1a]/80 p-6 backdrop-blur-xl transition-all duration-500 hover:border-white/20 hover:shadow-2xl hover:-translate-y-1">
            <!-- Subtle gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-500 group-hover:opacity-100" :class="card.gradient"></div>
            <!-- Watermark icon top-right background -->
            <div class="absolute -right-4 -top-4 opacity-[0.06] transition-all duration-700 group-hover:opacity-[0.12] group-hover:scale-110 group-hover:rotate-6 pointer-events-none" :class="card.iconColor">
              <component :is="card.icon" class="w-28 h-28" />
            </div>

            <div class="relative z-10 flex items-start gap-4">
              <!-- Large icon on left, no background wrapper -->
              <component :is="card.icon" class="w-10 h-10 shrink-0 mt-0.5 transition-colors duration-300" :class="card.iconColor" />
              <div class="flex-1 min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-white/50 mb-2">{{ card.label }}</p>
                <p class="text-4xl lg:text-5xl font-black tracking-tight font-sans drop-shadow-sm text-white">
                  {{ card.value }}
                </p>
              </div>
            </div>
          </article>
        </div>

        <!-- Main Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Left Column (2/3) - Today's Appointments & Payments -->
          <div class="lg:col-span-2 space-y-6">
            
            <!-- Pending Payments Action Required -->
            <div v-if="dashboard.pending_payments?.length > 0" class="rounded-2xl border border-blue-500/30 bg-blue-500/5 backdrop-blur-sm overflow-hidden shadow-[0_8px_30px_rgba(59,130,246,0.1)]">
              <div class="flex items-center justify-between border-b border-blue-500/20 px-6 py-4 bg-blue-500/10">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/20">
                    <CheckIcon class="h-5 w-5 text-blue-400" />
                  </div>
                  <h2 class="font-display text-lg text-blue-400 drop-shadow-sm">Action Required: Verify Payments</h2>
                </div>
              </div>

              <div class="divide-y divide-blue-500/10">
                <div
                  v-for="booking in dashboard.pending_payments"
                  :key="'pay-' + booking.id"
                  class="group flex items-start gap-4 px-6 py-4 transition-colors hover:bg-blue-500/10"
                >
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                      <p class="text-sm font-semibold text-theme-text truncate">{{ booking.customer_name }}</p>
                      <span class="shrink-0 text-sm text-blue-400 font-bold tracking-tight">₦{{ booking.price?.toLocaleString() }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-ivory/40">{{ booking.service_name || 'General' }}</p>
                    <div class="mt-3 flex items-center gap-2">
                      <button
                        @click="viewReceipt(booking)"
                        class="inline-flex items-center justify-center gap-1 rounded-xl bg-blue-500/20 px-4 py-1.5 text-xs font-bold text-blue-400 uppercase tracking-wider hover:bg-blue-500 hover:text-white hover:shadow-[0_0_15px_rgba(59,130,246,0.5)] transition-all"
                      >
                        View Receipt
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Today's Appointments -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
              <div class="flex items-center justify-between border-b border-white/[0.04] px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10 border border-gold/15">
                    <CalendarDaysIcon class="h-4.5 w-4.5 text-gold" />
                  </div>
                  <div>
                    <h2 class="text-sm font-bold text-theme-text">Today's Appointments</h2>
                    <p class="text-[10px] text-ivory/35 mt-0.5">Your schedule for the day</p>
                  </div>
                </div>
                <RouterLink
                  to="/barber/appointments"
                  class="text-[11px] font-semibold text-gold/70 hover:text-gold transition-colors"
                >
                  View All →
                </RouterLink>
              </div>

              <div class="divide-y divide-white/[0.03]">
                <div
                  v-for="appointment in (dashboard.today_appointments || [])"
                  :key="appointment.id"
                  class="group flex items-start gap-5 px-6 py-5 transition-colors hover:bg-white/[0.02]"
                >
                  <!-- Time Indicator -->
                  <div class="flex flex-col items-center pt-1.5 min-w-[50px]">
                    <span class="text-xs font-bold text-gold tracking-wider">{{ appointment.appointment_time }}</span>
                  </div>
                  
                  <div class="h-full w-px bg-white/5 relative top-1"></div>

                  <div class="flex-1 min-w-0 bg-[#1a1a1a]/50 p-4 rounded-xl border border-white/5 group-hover:border-white/10 transition-colors">
                    <div class="flex items-center justify-between gap-2">
                      <p class="text-sm font-semibold text-theme-text truncate flex items-center gap-2">
                        {{ appointment.client_name }}
                        <span v-if="appointment.status === 'confirmed'" class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        <span v-else-if="appointment.status === 'completed'" class="flex h-2 w-2 rounded-full bg-blue-400"></span>
                      </p>
                    </div>
                    <p class="mt-1 text-xs text-ivory/40 flex items-center gap-1.5">
                      <SparklesIcon class="h-3.5 w-3.5 text-ivory/30" />
                      {{ appointment.service?.name || 'General' }}
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                      <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest"
                        :class="statusClass(appointment.status)"
                      >
                        {{ appointment.status }}
                      </span>
                      <button v-if="appointment.status === 'confirmed'"
                        @click="complete(appointment.id)"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1 text-[10px] font-bold text-emerald-400 uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-colors border border-emerald-500/20 hover:border-emerald-500"
                      >
                        <CheckIcon class="h-3.5 w-3.5" />
                        Complete
                      </button>
                      <button v-if="appointment.status === 'confirmed' || appointment.status === 'pending'"
                        @click="markNoShow(appointment.id)"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/5 px-3 py-1 text-[10px] font-bold text-red-400 uppercase tracking-widest hover:bg-red-500 hover:text-white transition-colors hover:border-red-500"
                      >
                        No show
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Empty state -->
                <div v-if="(dashboard.today_appointments || []).length === 0" class="px-6 py-16 text-center">
                  <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/[0.03] border border-white/[0.05] mb-4">
                    <CalendarDaysIcon class="h-8 w-8 text-ivory/20" />
                  </div>
                  <p class="text-sm font-semibold text-ivory/50">No appointments today</p>
                  <p class="mt-1 text-xs text-ivory/30 max-w-[200px] mx-auto leading-relaxed">Enjoy your day off or double check your schedule availability.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column (1/3) - Quick Actions & Progress -->
          <div class="space-y-6">
            
            <!-- Today's Progress Card -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm p-6 overflow-hidden relative group">
              <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gold/10 blur-[30px] group-hover:bg-gold/20 transition-colors duration-500"></div>
              <div class="relative z-10 flex items-center gap-3 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10 border border-gold/15">
                  <ClockIcon class="h-4.5 w-4.5 text-gold" />
                </div>
                <div>
                  <h3 class="text-sm font-bold text-theme-text">Today's Progress</h3>
                  <p class="text-[10px] text-ivory/35 mt-0.5">{{ currentDate }}</p>
                </div>
              </div>
              <div class="relative z-10 space-y-3">
                <div>
                  <div class="flex justify-between items-end mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-ivory/40">Appointments completed</span>
                    <span class="text-xl font-black text-theme-text tracking-tight font-sans">
                      {{ dashboard.stats?.completed_bookings ?? 0 }}<span class="text-sm text-ivory/30">/{{ dashboard.stats?.today_bookings ?? 0 }}</span>
                    </span>
                  </div>
                  <div class="h-2 rounded-full bg-white/[0.04] overflow-hidden shadow-inner">
                    <div
                      class="h-full rounded-full bg-gradient-to-r from-gold to-amber-400 transition-all duration-1000 ease-out relative"
                      :style="{ width: progressWidth + '%' }"
                    >
                      <div class="absolute top-0 right-0 bottom-0 left-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.2)_50%,transparent_75%,transparent_100%)] bg-[length:20px_20px] animate-[shimmer_2s_linear_infinite]"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm p-6">
              <div class="flex items-center gap-3 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 border border-white/10">
                  <BoltIcon class="h-4.5 w-4.5 text-ivory/70" />
                </div>
                <div>
                  <h2 class="text-sm font-bold text-theme-text">Quick Actions</h2>
                  <p class="text-[10px] text-ivory/35 mt-0.5">Fast access to tools</p>
                </div>
              </div>
              <div class="grid grid-cols-1 gap-3">
                <RouterLink
                  v-for="action in quickActions"
                  :key="action.to"
                  :to="action.to"
                  class="group relative flex items-center gap-4 rounded-xl border border-white/[0.04] bg-[#1a1a1a]/50 p-3.5 transition-all duration-300 hover:border-gold/30 hover:bg-gold/5 hover:shadow-[0_4px_20px_rgba(212,175,55,0.05)] hover:-translate-y-0.5"
                >
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors border shadow-sm"
                    :class="action.iconWrap"
                  >
                    <component :is="action.icon" class="h-5 w-5" :class="action.iconColor" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-theme-text group-hover:text-gold transition-colors truncate">{{ action.label }}</p>
                    <p class="text-[11px] text-ivory/40 mt-0.5 truncate">{{ action.description }}</p>
                  </div>
                  <ChevronRightIcon class="h-4 w-4 text-ivory/20 transition-all group-hover:text-gold/60 group-hover:translate-x-1" />
                </RouterLink>

                <button
                  @click="showNotificationModal = true"
                  class="group relative flex items-center gap-4 rounded-xl border border-white/[0.04] bg-[#1a1a1a]/50 p-3.5 transition-all duration-300 hover:border-purple-500/30 hover:bg-purple-500/5 hover:shadow-[0_4px_20px_rgba(168,85,247,0.05)] hover:-translate-y-0.5 text-left"
                >
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors border shadow-sm bg-purple-500/10 border-purple-500/20 group-hover:bg-purple-500/20">
                    <MegaphoneIcon class="h-5 w-5 text-purple-400" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-theme-text group-hover:text-purple-400 transition-colors truncate">Notify Customers</p>
                    <p class="text-[11px] text-ivory/40 mt-0.5 truncate">Send a quick broadcast alert</p>
                  </div>
                  <ChevronRightIcon class="h-4 w-4 text-ivory/20 transition-all group-hover:text-purple-400/60 group-hover:translate-x-1" />
                </button>
              </div>
            </div>

          </div>
        </div>
      </template>
      
      <!-- Notification Modal -->
      <transition name="fade">
        <div v-if="showNotificationModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="showNotificationModal = false"></div>
          <div class="relative w-full max-w-md rounded-3xl border border-white/10 bg-theme-surface p-8 shadow-[0_20px_60px_rgba(0,0,0,0.5)] overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
              <button @click="showNotificationModal = false" class="text-ivory/40 hover:text-white transition-colors bg-white/5 hover:bg-white/10 p-2 rounded-full">
                <XMarkIcon class="h-5 w-5"/>
              </button>
            </div>
            
            <div class="flex items-center gap-4 mb-6">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500/20 to-purple-600/10 border border-purple-500/20 shadow-inner">
                <MegaphoneIcon class="h-6 w-6 text-purple-400" />
              </div>
              <div>
                <h3 class="font-display text-xl text-theme-text">Broadcast Alert</h3>
                <p class="text-[11px] uppercase tracking-widest text-ivory/40 mt-1 font-bold">Notify all your customers</p>
              </div>
            </div>

            <div class="space-y-5">
              <div>
                <label class="text-[11px] font-bold uppercase tracking-widest text-ivory/50 ml-1 mb-2 block">Alert Title</label>
                <input v-model="notificationForm.title" type="text" class="w-full rounded-xl border border-white/10 bg-theme-bg px-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/50" placeholder="e.g. I am back at the shop!" />
              </div>
              <div>
                <label class="text-[11px] font-bold uppercase tracking-widest text-ivory/50 ml-1 mb-2 block">Message content</label>
                <textarea v-model="notificationForm.message" rows="3" class="w-full rounded-xl border border-white/10 bg-theme-bg px-4 py-3.5 text-sm text-theme-text outline-none transition-all focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/50 resize-none" placeholder="Let your customers know you are available, running late, or have a new promo..."></textarea>
              </div>
              <button @click="sendNotification" :disabled="sendingNotification" class="w-full flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-500 py-3.5 text-sm font-bold text-white transition-all hover:shadow-[0_0_20px_rgba(168,85,247,0.4)] disabled:opacity-50 mt-4 active:scale-[0.98]">
                <MegaphoneIcon v-if="!sendingNotification" class="h-4.5 w-4.5" />
                {{ sendingNotification ? 'Broadcasting...' : 'Send Broadcast' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Receipt Viewer Modal -->
      <ReceiptViewer 
        v-if="selectedReceipt" 
        :booking="selectedReceipt" 
        @close="selectedReceipt = null"
        @approve="onPaymentVerified"
        @reject="onPaymentVerified"
        portal="barber"
      />
    </section>
  </BarberLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import BarberLayout from '../layouts/BarberLayout.vue';
import ReceiptViewer from '../../../core/components/ReceiptViewer.vue';
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
  XMarkIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline';
import { BoltIcon } from '@heroicons/vue/24/solid';
import { useToast } from '../../../core/composables/useToast';

const toast = useToast();

const dashboard = ref({ stats: {}, today_appointments: [] });
const loading = ref(true);
const myStatus = ref('active');
const showNotificationModal = ref(false);
const sendingNotification = ref(false);
const notificationForm = ref({ title: '', message: '' });
const selectedReceipt = ref(null);

const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
};

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
    label: "Today's Bookings",
    value: dashboard.value.stats?.today_bookings ?? 0,
    icon: CalendarDaysIcon,
    iconWrap: 'bg-gold/10 border-gold/20 text-gold',
    iconColor: 'text-gold',
    gradient: 'from-gold/[0.05] to-transparent',
    glowClass: 'bg-gold/20'
  },
  {
    label: 'Upcoming',
    value: dashboard.value.stats?.upcoming_bookings ?? 0,
    icon: ArrowTrendingUpIcon,
    iconWrap: 'bg-blue-400/10 border-blue-400/20 text-blue-400',
    iconColor: 'text-blue-400',
    gradient: 'from-blue-400/[0.05] to-transparent',
    glowClass: 'bg-blue-400/20'
  },
  {
    label: 'Completed',
    value: dashboard.value.stats?.completed_bookings ?? 0,
    icon: UserGroupIcon,
    iconWrap: 'bg-emerald-400/10 border-emerald-400/20 text-emerald-400',
    iconColor: 'text-emerald-400',
    gradient: 'from-emerald-400/[0.05] to-transparent',
    glowClass: 'bg-emerald-400/20'
  },
  {
    label: 'No Shows',
    value: dashboard.value.stats?.no_show_count ?? 0,
    icon: ExclamationTriangleIcon,
    iconWrap: 'bg-red-400/10 border-red-400/20 text-red-400',
    iconColor: 'text-red-400',
    gradient: 'from-red-400/[0.05] to-transparent',
    glowClass: 'bg-red-400/20'
  },
]);

const quickActions = [
  {
    label: 'Open Schedule',
    description: 'Manage working hours',
    to: '/barber/schedule',
    icon: CalendarIcon,
    iconWrap: 'bg-gold/10 border-gold/20',
    iconColor: 'text-gold',
  },
  {
    label: 'Appointments',
    description: 'Review all bookings',
    to: '/barber/appointments',
    icon: ClipboardDocumentCheckIcon,
    iconWrap: 'bg-blue-400/10 border-blue-400/20',
    iconColor: 'text-blue-400',
  },
  {
    label: 'My Profile',
    description: 'Update your details',
    to: '/barber/profile',
    icon: UserIcon,
    iconWrap: 'bg-emerald-400/10 border-emerald-400/20',
    iconColor: 'text-emerald-400',
  },
  {
    label: 'Payments & Account',
    description: 'Verify receipts & bank info',
    to: '/barber/payments',
    icon: ClipboardDocumentCheckIcon,
    iconWrap: 'bg-amber-600/10 border-amber-600/20',
    iconColor: 'text-amber-500',
  },
];

const progressWidth = computed(() => {
  const total = dashboard.value.stats?.today_bookings ?? 0;
  const completed = dashboard.value.stats?.completed_bookings ?? 0;
  if (total === 0) return 0;
  return Math.round((completed / total) * 100);
});

function statusClass(status) {
  const s = (status || '').toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-emerald-400/10 text-emerald-400 border border-emerald-400/20';
  if (s === 'pending') return 'bg-gold/10 text-gold border border-gold/20';
  if (s === 'cancelled' || s === 'no_show') return 'bg-red-400/10 text-red-400 border border-red-400/20';
  return 'bg-white/[0.06] text-ivory/50 border border-white/10';
}

async function loadDashboard() {
  try {
    const response = await barberApi.getBookings();
    const bookings = response.data.data || [];
    
    // Compute dashboard data manually
    const today = new Date().toISOString().split('T')[0];
    
    const todayBookings = bookings.filter(b => b.appointment_date === today);
    const completedBookings = bookings.filter(b => b.status === 'completed');
    const upcomingBookings = bookings.filter(b => b.status === 'confirmed' && b.appointment_date >= today);
    const noShowBookings = bookings.filter(b => b.status === 'no_show');
    
    const pendingPayments = bookings.filter(b => b.status === 'pending' && b.payment_status === 'awaiting_verification');

    dashboard.value = {
      today_appointments: todayBookings.map(b => ({
        id: b.id,
        client_name: b.customer_name,
        appointment_time: b.appointment_time,
        status: b.status,
        service: { name: b.service_name }
      })),
      pending_payments: pendingPayments,
      stats: {
        today_bookings: todayBookings.length,
        completed_bookings: completedBookings.length,
        upcoming_bookings: upcomingBookings.length,
        no_show_count: noShowBookings.length
      }
    };
  } catch (e) {
    // API may not have data yet
  } finally {
    loading.value = false;
  }
}

function viewReceipt(booking) {
  const mapped = {
    ...booking,
    customer: {
      name: booking.customer_name,
      email: booking.customer_email,
      phone: booking.customer_phone
    },
    service: {
      name: booking.service_name,
      price: booking.price,
      duration_minutes: booking.duration_minutes
    },
    barber: {
      name: 'You'
    }
  };
  selectedReceipt.value = mapped;
}

function onPaymentVerified() {
  selectedReceipt.value = null;
  loadDashboard();
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
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
  loadDashboard();
  loadMyStatus();
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-slide-up {
  animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes shimmer {
  from {
    background-position: 0 0;
  }
  to {
    background-position: 40px 0;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>