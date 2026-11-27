<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h2 class="text-xs uppercase tracking-wider font-semibold text-[var(--color-text-muted)]">
          Platform Health Signals
        </h2>
        <span class="inline-flex items-center rounded-md border border-[var(--color-border)] px-2 py-0.5 text-[10px] font-semibold text-[var(--color-text-muted)]">
          6 system signals
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
        class="group flex flex-col justify-between rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-3.5 transition-colors hover:border-rose-500/40"
        :class="getCardBorderClass(paystackState)"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="text-emerald-500">
              <CreditCardIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="getStatusDotClass(paystackState)"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="getStatusTextColor(paystackState)">
                {{ paystackState }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Paystack Gateway</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ getBreakerLabel(paystackState) }}
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          {{ paystackFailures === null ? 'Error count unavailable' : `${paystackFailures} consecutive error${paystackFailures === 1 ? '' : 's'}` }}
        </p>
      </RouterLink>

      <!-- 2. Resend / Mail Breaker -->
      <RouterLink
        to="/superadmin/resend"
        class="group flex flex-col justify-between rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-3.5 transition-colors hover:border-rose-500/40"
        :class="getCardBorderClass(resendState)"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="text-blue-500">
              <EnvelopeIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="getStatusDotClass(resendState)"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="getStatusTextColor(resendState)">
                {{ resendState }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Email Dispatcher</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ getBreakerLabel(resendState) }}
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          Resend / Transactional
        </p>
      </RouterLink>

      <!-- 3. Sweeper Cron Heartbeat -->
      <RouterLink
        to="/superadmin/health"
        class="group flex flex-col justify-between rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-3.5 transition-colors hover:border-rose-500/40"
        :class="sweeperStatus === 'optimal' || sweeperStatus === 'operational' ? 'border-[var(--color-border)]' : 'border-amber-500/30'"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="text-amber-500">
              <ClockIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="getStatusDotClass(sweeperStatus)"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="sweeperStatus === 'optimal' || sweeperStatus === 'operational' ? 'text-emerald-500' : 'text-amber-500'">
                {{ sweeperStatus }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Draft Sweeper</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ sweeperCancelled === null ? '—' : sweeperCancelled }} Holds Expired
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          {{ sweeperLastRunText }}
        </p>
      </RouterLink>

      <!-- 4. Database Query Engine -->
      <RouterLink
        to="/superadmin/health"
        class="group flex flex-col justify-between rounded-xl border bg-[var(--color-bg-secondary)] p-3.5 transition-colors hover:border-rose-500/40"
        :class="dbStatus === 'degraded' ? 'border-rose-500/40' : 'border-[var(--color-border)]'"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="text-purple-500">
              <CircleStackIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="getStatusDotClass(dbStatus)"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="getStatusTextColor(dbStatus)">
                {{ dbStatus === 'operational' ? 'Connected' : (dbStatus === 'degraded' ? 'Degraded' : 'Unknown') }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Database Ping</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ dbLatency }}<span v-if="dbLatency !== '—'"> ms</span>
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          Database response time
        </p>
      </RouterLink>

      <!-- 5. Security Perimeter -->
      <RouterLink
        to="/superadmin/system-logs"
        class="group flex flex-col justify-between rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-3.5 transition-colors hover:border-rose-500/40"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="text-emerald-500">
              <ShieldCheckIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full" :class="securityStatus === 'operational' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="securityStatus === 'operational' ? 'text-emerald-500' : 'text-amber-500'">
                {{ securityStatus === 'operational' ? 'Monitoring' : (securityStatus === 'unknown' ? 'Unknown' : 'Review') }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Security Shield</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ probesBlocked === null ? '—' : probesBlocked }} Blocked
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          Blocked probe attempts
        </p>
      </RouterLink>

      <!-- 6. Incident & Dead-Letter Vault -->
      <RouterLink
        to="/superadmin/system-errors"
        class="group flex flex-col justify-between rounded-xl border bg-[var(--color-bg-secondary)] p-3.5 transition-colors hover:border-rose-500/40"
        :class="unresolvedIncidents > 0 ? 'border-rose-500/30' : 'border-[var(--color-border)]'"
      >
        <div>
          <div class="flex items-center justify-between mb-2">
            <div
              :class="!hasIncidentData ? 'text-[var(--color-text-muted)]' : (unresolvedIncidents > 0 ? 'text-rose-500' : 'text-emerald-500')"
            >
              <ExclamationTriangleIcon class="h-4 w-4" />
            </div>
            <div class="flex items-center gap-1.5">
              <span
                class="h-2 w-2 rounded-full"
                :class="!hasIncidentData ? 'bg-[var(--color-text-muted)]' : (unresolvedIncidents === 0 ? 'bg-emerald-500' : 'bg-rose-500')"
              ></span>
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="!hasIncidentData ? 'text-[var(--color-text-muted)]' : (unresolvedIncidents === 0 ? 'text-emerald-500' : 'text-rose-500')">
                {{ !hasIncidentData ? 'Unknown' : (unresolvedIncidents === 0 ? 'Clean' : 'Alert') }}
              </span>
            </div>
          </div>
          <p class="text-[11px] font-medium text-[var(--color-text-muted)]">Incident Vault</p>
          <p class="text-sm font-bold text-[var(--color-text-primary)] mt-0.5">
            {{ hasIncidentData ? unresolvedIncidents : '—' }} Unresolved
          </p>
        </div>
        <p class="text-[10px] text-[var(--color-text-muted)] mt-2 font-mono truncate">
          {{ failedJobsCount === null ? '—' : failedJobsCount }} failed queue job{{ failedJobsCount === 1 ? '' : 's' }}
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

const paystackState = computed(() => props.subsystems?.paystack?.state || 'unknown');
const paystackFailures = computed(() => {
  const count = props.subsystems?.paystack?.failures_count;
  return count == null ? null : Number(count);
});

const resendState = computed(() => props.subsystems?.resend?.state || 'unknown');

const sweeperStatus = computed(() => props.subsystems?.sweeper?.status || 'idle');
const sweeperCancelled = computed(() => {
  const count = props.subsystems?.sweeper?.cancelled_count;
  return count == null ? null : Number(count);
});
const sweeperLastRunText = computed(() => {
  const lastRun = props.subsystems?.sweeper?.last_run;
  if (!lastRun) return 'No sweeper runs recorded';
  const timestamp = new Date(lastRun);
  if (Number.isNaN(timestamp.getTime())) return 'Timestamp unavailable';
  return `Last run: ${new Intl.DateTimeFormat('en-NG', { timeStyle: 'short' }).format(timestamp)}`;
});

const dbStatus = computed(() => props.subsystems?.database?.status || 'unknown');
const dbLatency = computed(() => {
  const latency = props.subsystems?.database?.latency_ms;
  const numericLatency = Number(latency);
  return latency == null || !Number.isFinite(numericLatency) ? '—' : numericLatency.toFixed(1);
});

const probesBlocked = computed(() => {
  const count = props.subsystems?.security?.probes_blocked;
  return count == null ? null : Number(count);
});
const securityStatus = computed(() => props.subsystems?.security?.status || 'unknown');

const unresolvedIncidents = computed(() => {
  const count = props.subsystems?.incidents?.unresolved_count;
  return count == null ? null : Number(count);
});
const failedJobsCount = computed(() => {
  const count = props.subsystems?.incidents?.failed_jobs_count;
  return count == null ? null : Number(count);
});
const hasIncidentData = computed(() => unresolvedIncidents.value !== null && failedJobsCount.value !== null);

const getStatusTextColor = (state) => {
  if (state === 'closed' || state === 'operational') return 'text-emerald-500';
  if (state === 'half_open') return 'text-amber-500';
  if (state === 'open' || state === 'degraded') return 'text-rose-500';
  return 'text-[var(--color-text-muted)]';
};

const getStatusDotClass = (state) => {
  if (state === 'closed' || state === 'operational' || state === 'optimal') return 'bg-emerald-500';
  if (state === 'half_open' || state === 'idle') return 'bg-amber-500';
  if (state === 'open' || state === 'degraded') return 'bg-rose-500';
  return 'bg-[var(--color-text-muted)]';
};

const getBreakerLabel = (state) => {
  if (state === 'closed') return 'Operational';
  if (state === 'half_open') return 'Probing recovery';
  if (state === 'open') return 'Circuit breaker open';
  return 'Status unavailable';
};

const getCardBorderClass = (state) => {
  if (state === 'closed') return 'border-[var(--color-border)]';
  if (state === 'half_open') return 'border-amber-500/30';
  if (state === 'open') return 'border-rose-500/40';
  return 'border-[var(--color-border)]';
};
</script>
