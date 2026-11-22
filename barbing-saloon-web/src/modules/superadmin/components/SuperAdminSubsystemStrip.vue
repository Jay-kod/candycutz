<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h2 class="text-xs uppercase tracking-wider font-semibold text-[var(--color-text-muted)]">
          Live Subsystem Telemetry & Circuit Breakers
        </h2>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">
          6 Active Monitors
        </span>
      </div>
      <div class="flex items-center gap-3 text-xs">
        <RouterLink
          to="/superadmin/health"
          class="font-semibold text-rose-500 hover:text-rose-400 transition-colors flex items-center gap-1"
        >
          <span>Health Matrix</span>
          <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
        </RouterLink>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
      <!-- 1. Paystack Gateway Breaker -->
      <RouterLink
        to="/superadmin/health"
        class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 bg-[var(--color-bg-secondary)] hover:border-rose-500/40 hover:shadow-md"
        :class="getCardBorderClass(paystackState)"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 group-hover:scale-105 transition-transform">
              <CreditCardIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="paystackState === 'closed' ? 'bg-emerald-500 animate-pulse' : (paystackState === 'half_open' ? 'bg-amber-500' : 'bg-rose-500')"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="getStatusTextColor(paystackState)">
                {{ paystackState }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Paystack Gateway</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ paystackState === 'closed' ? 'Operational' : (paystackState === 'half_open' ? 'Probing' : 'Tripped') }}
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          {{ paystackFailures }} consecutive error{{ paystackFailures === 1 ? '' : 's' }}
        </p>
      </RouterLink>

      <!-- 2. Resend / Mail Breaker -->
      <RouterLink
        to="/superadmin/resend"
        class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 bg-[var(--color-bg-secondary)] hover:border-rose-500/40 hover:shadow-md"
        :class="getCardBorderClass(resendState)"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-xl bg-blue-500/10 text-blue-500 border border-blue-500/20 group-hover:scale-105 transition-transform">
              <EnvelopeIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="resendState === 'closed' ? 'bg-emerald-500 animate-pulse' : (resendState === 'half_open' ? 'bg-amber-500' : 'bg-rose-500')"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="getStatusTextColor(resendState)">
                {{ resendState }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Email Dispatcher</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ resendState === 'closed' ? 'Operational' : 'Degraded' }}
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          Resend / Transactional
        </p>
      </RouterLink>

      <!-- 3. Sweeper Cron Heartbeat -->
      <RouterLink
        to="/superadmin/health"
        class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 bg-[var(--color-bg-secondary)] hover:border-rose-500/40 hover:shadow-md"
        :class="sweeperStatus === 'optimal' || sweeperStatus === 'operational' ? 'border-[var(--color-border)]' : 'border-amber-500/30'"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 group-hover:scale-105 transition-transform">
              <ClockIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="sweeperStatus === 'optimal' || sweeperStatus === 'operational' ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="sweeperStatus === 'optimal' || sweeperStatus === 'operational' ? 'text-emerald-500' : 'text-amber-500'">
                {{ sweeperStatus }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Draft Sweeper</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ sweeperCancelled }} Holds Expired
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          {{ sweeperLastRunText }}
        </p>
      </RouterLink>

      <!-- 4. Database Query Engine -->
      <RouterLink
        to="/superadmin/health"
        class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 bg-[var(--color-bg-secondary)] hover:border-rose-500/40 hover:shadow-md"
        :class="dbStatus === 'operational' ? 'border-[var(--color-border)]' : 'border-rose-500/40'"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-xl bg-purple-500/10 text-purple-500 border border-purple-500/20 group-hover:scale-105 transition-transform">
              <CircleStackIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="dbStatus === 'operational' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="dbStatus === 'operational' ? 'text-emerald-500' : 'text-rose-500'">
                {{ dbStatus === 'operational' ? 'Connected' : 'Degraded' }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Database Ping</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ dbLatency }} ms
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          Prepared PDO • Strict Mode
        </p>
      </RouterLink>

      <!-- 5. Security Perimeter -->
      <RouterLink
        to="/superadmin/system-logs"
        class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 bg-[var(--color-bg-secondary)] hover:border-rose-500/40 hover:shadow-md border-[var(--color-border)]"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 group-hover:scale-105 transition-transform">
              <ShieldCheckIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-500">
                Shield Active
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Security Shield</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ probesBlocked }} Blocked
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          Scanners & Traversal Neutralized
        </p>
      </RouterLink>

      <!-- 6. Incident & Dead-Letter Vault -->
      <RouterLink
        to="/superadmin/system-errors"
        class="group relative flex flex-col justify-between p-3.5 rounded-2xl border transition-all duration-200 bg-[var(--color-bg-secondary)] hover:border-rose-500/40 hover:shadow-md"
        :class="unresolvedIncidents > 0 ? 'border-rose-500/30' : 'border-[var(--color-border)]'"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div
              class="p-2 rounded-xl border group-hover:scale-105 transition-transform"
              :class="unresolvedIncidents > 0 ? 'bg-rose-500/10 text-rose-500 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'"
            >
              <ExclamationTriangleIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="unresolvedIncidents === 0 ? 'bg-emerald-500' : 'bg-rose-500 animate-pulse'"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="unresolvedIncidents === 0 ? 'text-emerald-500' : 'text-rose-500'">
                {{ unresolvedIncidents === 0 ? 'Clean' : 'Alert' }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Incident Vault</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ unresolvedIncidents }} Unresolved
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          {{ failedJobsCount }} failed queue job{{ failedJobsCount === 1 ? '' : 's' }}
        </p>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  CreditCardIcon,
  EnvelopeIcon,
  ClockIcon,
  CircleStackIcon,
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  subsystems: {
    type: Object,
    default: () => ({}),
  },
});

const paystackState = computed(() => props.subsystems?.paystack?.state || 'closed');
const paystackFailures = computed(() => Number(props.subsystems?.paystack?.failures_count || 0));

const resendState = computed(() => props.subsystems?.resend?.state || 'closed');

const sweeperStatus = computed(() => props.subsystems?.sweeper?.status || 'idle');
const sweeperCancelled = computed(() => Number(props.subsystems?.sweeper?.cancelled_count || 0));
const sweeperLastRunText = computed(() => {
  const lastRun = props.subsystems?.sweeper?.last_run;
  if (!lastRun) return 'No sweeper runs recorded';
  try {
    const d = new Date(lastRun);
    return `Pulse: ${d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`;
  } catch {
    return 'Active';
  }
});

const dbStatus = computed(() => props.subsystems?.database?.status || 'operational');
const dbLatency = computed(() => Number(props.subsystems?.database?.latency_ms || 0).toFixed(1));

const probesBlocked = computed(() => Number(props.subsystems?.security?.probes_blocked || 0));

const unresolvedIncidents = computed(() => Number(props.subsystems?.incidents?.unresolved_count || 0));
const failedJobsCount = computed(() => Number(props.subsystems?.incidents?.failed_jobs_count || 0));

const getStatusTextColor = (state) => {
  if (state === 'closed') return 'text-emerald-500';
  if (state === 'half_open') return 'text-amber-500';
  return 'text-rose-500';
};

const getCardBorderClass = (state) => {
  if (state === 'closed') return 'border-[var(--color-border)]';
  if (state === 'half_open') return 'border-amber-500/30';
  return 'border-rose-500/40';
};
</script>
