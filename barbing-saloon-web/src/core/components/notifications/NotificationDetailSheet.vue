<template>
  <IosModalSheet :show="Boolean(notif)" @close="$emit('close')">
    <template #header>
      <div v-if="notif" class="flex items-center justify-between gap-3 w-full">
        <div class="flex items-center gap-2.5 min-w-0">
          <div
            class="h-9 w-9 shrink-0 rounded-lg border flex items-center justify-center"
            :class="getBgColorForType(notif.type)"
          >
            <component
              :is="getIconForType(notif.type)"
              class="w-4 h-4"
              :class="getIconColorForType(notif.type)"
            />
          </div>
          <div class="flex items-center gap-1.5 flex-wrap min-w-0">
            <span
              class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border"
              :class="getBgColorForType(notif.type)"
            >
              {{ getLabelForType(notif.type) }}
            </span>
            <span
              class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-[var(--color-text-muted)]"
            >
              {{ notif.is_read ? 'Read' : 'Unread' }}
            </span>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="min-h-[36px] min-w-[36px] rounded-lg bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] flex items-center justify-center text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] transition-colors shrink-0"
          aria-label="Close notification details"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </template>

    <div v-if="notif" class="space-y-4 py-2">
      <div>
        <h3 class="font-display text-xl font-bold text-[var(--color-text-primary)] leading-snug">
          {{ notif.title }}
        </h3>
        <p class="mt-1 text-xs text-[var(--color-text-muted)] tabular-nums text-left-forced">
          {{ formatFullDate(notif.created_at) }} &middot; {{ formatRelativeTime(notif.created_at) }}
        </p>
      </div>

      <p class="text-sm leading-relaxed text-[var(--color-text-primary)] text-left-forced">
        {{ notif.message }}
      </p>

      <!-- Structured Booking / Payment Metadata -->
      <div
        v-if="hasMetadata"
        class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 p-3.5 space-y-2.5 text-xs"
      >
        <div v-if="meta.verificationCode" class="flex items-center justify-between gap-2">
          <span class="text-[var(--color-text-secondary)]">Booking Code</span>
          <button
            type="button"
            @click="$emit('copy', { value: meta.verificationCode, label: 'Booking code' })"
            class="inline-flex items-center gap-1.5 rounded-md border border-[var(--color-accent)]/30 bg-[var(--color-accent)]/10 px-2.5 py-1 font-mono font-bold text-[var(--color-accent)] hover:bg-[var(--color-accent)]/20 transition-colors"
          >
            <span>{{ meta.verificationCode }}</span>
            <DocumentDuplicateIcon class="w-3.5 h-3.5" />
          </button>
        </div>

        <div v-if="meta.bookingReference" class="flex items-center justify-between gap-2">
          <span class="text-[var(--color-text-secondary)]">Reference</span>
          <span class="font-mono font-semibold text-[var(--color-text-primary)]">
            {{ meta.bookingReference }}
          </span>
        </div>

        <div v-if="meta.serviceName" class="flex items-center justify-between gap-2">
          <span class="text-[var(--color-text-secondary)]">Service</span>
          <span class="font-semibold text-[var(--color-text-primary)] text-right">
            {{ meta.serviceName }}
          </span>
        </div>

        <div v-if="meta.barberName" class="flex items-center justify-between gap-2">
          <span class="text-[var(--color-text-secondary)]">Barber</span>
          <span class="font-semibold text-[var(--color-text-primary)] text-right">
            {{ meta.barberName }}
          </span>
        </div>

        <div v-if="meta.amount" class="flex items-center justify-between gap-2">
          <span class="text-[var(--color-text-secondary)]">Amount</span>
          <span class="font-bold tabular-nums text-[var(--color-text-primary)]">
            &#8358;{{ meta.amount }}
          </span>
        </div>

        <div v-if="meta.appointmentDate" class="flex items-center justify-between gap-2">
          <span class="text-[var(--color-text-secondary)]">Scheduled</span>
          <span class="font-semibold tabular-nums text-[var(--color-text-primary)]">
            {{ meta.appointmentDate }}
            <template v-if="meta.appointmentTime"> at {{ meta.appointmentTime }}</template>
          </span>
        </div>

        <div v-if="meta.reason" class="pt-2 border-t border-[var(--color-border)] text-left-forced">
          <span class="block text-[11px] uppercase tracking-wider text-[var(--color-text-muted)] mb-0.5">
            Reason
          </span>
          <span class="text-rose-600 dark:text-rose-400 font-medium">{{ meta.reason }}</span>
        </div>
      </div>
    </div>

    <template v-if="notif" #footer>
      <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-2.5">
        <button
          type="button"
          @click="handleDelete"
          class="min-h-[44px] px-4 rounded-lg border border-rose-500/25 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white text-sm font-semibold inline-flex items-center justify-center gap-1.5 transition-colors"
        >
          <TrashIcon class="w-4 h-4 shrink-0" />
          <span>Delete</span>
        </button>

        <button
          v-if="target"
          type="button"
          @click="handleAction"
          class="min-h-[44px] flex-1 px-4 rounded-lg bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-sm font-bold inline-flex items-center justify-center gap-1.5 hover:brightness-110 transition-all"
        >
          <ArrowTopRightOnSquareIcon class="w-4 h-4 shrink-0" />
          <span>{{ target.label }}</span>
        </button>

        <button
          v-else
          type="button"
          @click="$emit('close')"
          class="min-h-[44px] flex-1 px-4 rounded-lg bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-sm font-bold inline-flex items-center justify-center transition-all"
        >
          Done
        </button>
      </div>
    </template>
  </IosModalSheet>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
  ArrowTopRightOnSquareIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';
import IosModalSheet from '@/shared/ui/ios/IosModalSheet.vue';
import {
  extractNotificationMeta,
  formatFullDate,
  formatRelativeTime,
  getBgColorForType,
  getIconColorForType,
  getIconForType,
  getLabelForType,
  resolveNotificationTarget,
} from './notificationUtils';

const router = useRouter();
const route = useRoute();

const props = defineProps({
  notif: { type: Object, default: null },
});

const emit = defineEmits(['close', 'delete', 'copy']);

const meta = computed(() => extractNotificationMeta(props.notif));

const hasMetadata = computed(() => {
  const m = meta.value;
  return Boolean(
    m.verificationCode ||
      m.bookingReference ||
      m.serviceName ||
      m.barberName ||
      m.amount ||
      m.appointmentDate ||
      m.reason
  );
});

const target = computed(() =>
  props.notif ? resolveNotificationTarget(props.notif, route?.path || '') : null
);

const handleAction = () => {
  const destination = target.value?.to;
  emit('close');
  if (destination && router) {
    router.push(destination);
  }
};

const handleDelete = () => {
  if (!props.notif) return;
  const id = props.notif.id;
  emit('close');
  emit('delete', id);
};
</script>
