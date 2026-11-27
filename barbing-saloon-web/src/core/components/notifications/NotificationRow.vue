<template>
  <article
    class="group relative rounded-xl border p-3.5 xs:p-4 sm:p-5 transition-colors duration-200"
    :class="
      !notif.is_read
        ? 'border-[var(--color-accent)]/35 bg-[var(--color-accent)]/[0.04] hover:bg-[var(--color-accent)]/[0.07]'
        : 'border-[var(--color-border)] bg-[var(--color-bg-secondary)] hover:bg-[var(--color-bg-tertiary)]/50'
    "
  >
    <!-- Subtle unread left indicator bar -->
    <div
      v-if="!notif.is_read"
      class="absolute left-1.5 top-3.5 bottom-3.5 w-1 rounded-full bg-[var(--color-accent)]"
      aria-hidden="true"
    ></div>

    <!-- Top Header Row: Compact Icon + Meta Strip + Title -->
    <div class="flex items-start gap-3" :class="{ 'pl-2': !notif.is_read }">
      <div
        class="relative h-10 w-10 shrink-0 rounded-lg border flex items-center justify-center"
        :class="
          !notif.is_read
            ? getBgColorForType(notif.type)
            : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-muted)]'
        "
      >
        <component
          :is="getIconForType(notif.type)"
          class="w-5 h-5"
          :class="!notif.is_read ? getIconColorForType(notif.type) : 'text-[var(--color-text-muted)]'"
        />
        <span
          v-if="!notif.is_read"
          class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-[var(--color-accent)] ring-2 ring-[var(--color-bg-secondary)]"
          title="Unread"
        ></span>
      </div>

      <div class="flex-1 min-w-0">
        <!-- Category / Priority / Timestamp Strip -->
        <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span
              class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border leading-none"
              :class="getBgColorForType(notif.type)"
            >
              {{ getLabelForType(notif.type) }}
            </span>

            <span
              v-if="priorityBadge"
              class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border leading-none"
              :class="priorityBadge.classes"
            >
              {{ priorityBadge.label }}
            </span>
          </div>

          <span
            class="text-[11px] font-medium text-[var(--color-text-muted)] tabular-nums shrink-0 inline-flex items-center gap-1"
            :title="formatFullDate(notif.created_at)"
          >
            <ClockIcon class="w-3.5 h-3.5 opacity-60" />
            <span>{{ formatRelativeTime(notif.created_at) }}</span>
            <span class="hidden xs:inline opacity-60">&middot;</span>
            <span class="hidden xs:inline">{{ formatTimeOnly(notif.created_at) }}</span>
          </span>
        </div>

        <!-- Card Title -->
        <button
          type="button"
          @click="$emit('select', notif)"
          class="text-left w-full group/title focus:outline-none focus-visible:underline"
        >
          <h3
            class="font-display text-base sm:text-lg font-semibold leading-snug break-words transition-colors"
            :class="
              !notif.is_read
                ? 'text-[var(--color-text-primary)] group-hover/title:text-[var(--color-accent)]'
                : 'text-[var(--color-text-secondary)] group-hover/title:text-[var(--color-text-primary)]'
            "
          >
            {{ notif.title }}
          </h3>
        </button>
      </div>
    </div>

    <!-- Full-Width Body on Mobile, Aligned on Desktop -->
    <div class="mt-2.5 sm:pl-[52px]" :class="{ 'pl-2 sm:pl-[60px]': !notif.is_read }">
      <p
        @click="$emit('select', notif)"
        class="cursor-pointer text-sm leading-relaxed text-left-forced break-words"
        :class="!notif.is_read ? 'text-[var(--color-text-primary)]' : 'text-[var(--color-text-secondary)]'"
      >
        {{ notif.message }}
      </p>

      <!-- Quick Metadata & 1-Tap Copy Chips -->
      <div v-if="hasMetaChips" class="mt-2.5 flex flex-wrap items-center gap-1.5">
        <button
          v-if="meta.verificationCode"
          type="button"
          @click.stop="copyValue(meta.verificationCode, 'Booking code')"
          class="inline-flex items-center gap-1.5 rounded-md border border-[var(--color-accent)]/30 bg-[var(--color-accent)]/10 px-2 py-1 text-[11px] font-mono font-bold text-[var(--color-accent)] hover:bg-[var(--color-accent)]/20 transition-colors"
          title="Copy booking code"
        >
          <span>Code: {{ meta.verificationCode }}</span>
          <DocumentDuplicateIcon class="w-3.5 h-3.5 shrink-0" />
        </button>

        <span
          v-if="meta.bookingReference"
          class="inline-flex items-center rounded-md border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-2 py-1 text-[11px] font-mono font-medium text-[var(--color-text-secondary)]"
        >
          Ref: {{ meta.bookingReference }}
        </span>

        <span
          v-if="meta.amount"
          class="inline-flex items-center rounded-md border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-2 py-1 text-[11px] font-semibold tabular-nums text-[var(--color-text-primary)]"
        >
          &#8358;{{ meta.amount }}
        </span>

        <span
          v-if="meta.barberName"
          class="inline-flex items-center rounded-md border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-2 py-1 text-[11px] font-medium text-[var(--color-text-secondary)]"
        >
          {{ meta.barberName }}
        </span>
      </div>
    </div>

    <!-- Action Bar (Always Accessible on Touch & Keyboard) -->
    <div
      class="mt-3.5 pt-3 border-t border-[var(--color-border-subtle)] sm:pl-[52px] flex flex-wrap items-center gap-2"
      :class="{ 'pl-2 sm:pl-[60px]': !notif.is_read }"
    >
      <button
        v-if="target"
        type="button"
        @click.stop="navigateToTarget"
        class="min-h-[36px] px-3 py-1.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] text-xs font-semibold text-[var(--color-text-primary)] inline-flex items-center gap-1.5 transition-colors"
      >
        <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5 shrink-0" />
        <span>{{ target.label }}</span>
      </button>

      <button
        v-else
        type="button"
        @click.stop="$emit('select', notif)"
        class="min-h-[36px] px-3 py-1.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] text-xs font-semibold text-[var(--color-text-primary)] inline-flex items-center gap-1.5 transition-colors"
      >
        <EyeIcon class="w-3.5 h-3.5 shrink-0" />
        <span>Read more</span>
      </button>

      <button
        v-if="!notif.is_read"
        type="button"
        @click.stop="$emit('read', notif.id)"
        class="min-h-[36px] px-3 py-1.5 rounded-lg border border-[var(--color-accent)]/25 bg-[var(--color-accent)]/10 text-[var(--color-accent)] hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] text-xs font-semibold inline-flex items-center gap-1.5 transition-colors"
      >
        <CheckIcon class="w-3.5 h-3.5 shrink-0" />
        <span>Mark read</span>
      </button>

      <button
        type="button"
        @click.stop="$emit('delete', notif.id)"
        class="ml-auto min-h-[36px] px-3 py-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white hover:border-rose-500 text-xs font-semibold inline-flex items-center gap-1.5 transition-colors"
      >
        <TrashIcon class="w-3.5 h-3.5 shrink-0" />
        <span>Delete</span>
      </button>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
  ArrowTopRightOnSquareIcon,
  CheckIcon,
  ClockIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';
import {
  extractNotificationMeta,
  formatFullDate,
  formatRelativeTime,
  formatTimeOnly,
  getBgColorForType,
  getIconColorForType,
  getIconForType,
  getLabelForType,
  resolveNotificationTarget,
} from './notificationUtils';

const router = useRouter();
const route = useRoute();

const props = defineProps({
  notif: { type: Object, required: true },
});

const emit = defineEmits(['read', 'delete', 'select', 'copy']);

const meta = computed(() => extractNotificationMeta(props.notif));

const hasMetaChips = computed(() => {
  return Boolean(
    meta.value.verificationCode ||
      meta.value.bookingReference ||
      meta.value.amount ||
      meta.value.barberName
  );
});

const priorityBadge = computed(() => {
  const p = (props.notif?.priority || '').toLowerCase();
  if (p === 'urgent') {
    return {
      label: 'Urgent',
      classes: 'bg-rose-500/15 border-rose-500/30 text-rose-600 dark:text-rose-400',
    };
  }
  if (p === 'high') {
    return {
      label: 'Important',
      classes: 'bg-amber-500/15 border-amber-500/30 text-amber-600 dark:text-amber-400',
    };
  }
  return null;
});

const target = computed(() => resolveNotificationTarget(props.notif, route?.path || ''));

const navigateToTarget = () => {
  if (!props.notif.is_read) {
    emit('read', props.notif.id);
  }
  if (target.value?.to && router) {
    router.push(target.value.to);
  }
};

const copyValue = (value, label) => {
  emit('copy', { value, label });
};
</script>
