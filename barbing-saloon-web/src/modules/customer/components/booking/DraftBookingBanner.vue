<template>
  <transition name="fade-slide">
    <div
      v-if="currentDraft && !isDismissed"
      class="relative overflow-hidden rounded-2xl border border-amber-500/30 bg-gradient-to-r from-[#0E1217] via-[#161B22] to-[#0E1217] p-4 sm:p-5 shadow-xl shadow-amber-500/5 mb-6"
    >
      <!-- Top Gold Ambient Highlight Line -->
      <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-amber-500 to-transparent"></div>

      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <!-- Left: Info & Timer -->
        <div class="flex items-start gap-3.5">
          <div class="h-10 w-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0 mt-0.5">
            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>

          <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-[10px] font-bold uppercase tracking-wider">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span>
                <span>Unfinished Booking</span>
              </span>

              <span
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[11px] font-semibold"
                :class="isExpired ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-black/40 text-amber-300 border border-white/[0.08]'"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ countdownText }}</span>
              </span>
            </div>

            <p class="text-xs sm:text-sm font-semibold text-white">
              {{ serviceName }} with {{ barberName }}
            </p>
            <p class="text-[11px] text-white/50">
              {{ appointmentDateText }} • Reference: <span class="font-mono text-amber-400/90">{{ currentDraft.booking_reference }}</span>
            </p>
          </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2.5 w-full md:w-auto justify-end shrink-0 pt-1 md:pt-0">
          <button
            type="button"
            @click="discard"
            :disabled="isDiscarding"
            class="px-3 py-2 rounded-xl border border-white/[0.1] bg-white/[0.03] hover:bg-red-500/10 hover:border-red-500/30 text-white/60 hover:text-red-400 text-xs font-semibold transition-all cursor-pointer disabled:opacity-50"
          >
            <span v-if="isDiscarding" class="inline-flex items-center gap-1.5">
              <span class="h-3 w-3 animate-spin rounded-full border-2 border-red-400 border-t-transparent"></span>
              Discarding...
            </span>
            <span v-else>Discard Draft</span>
          </button>

          <button
            type="button"
            @click="resume"
            :disabled="isResuming"
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:brightness-110 text-slate-950 text-xs font-bold shadow-lg shadow-amber-500/20 transition-all cursor-pointer active:scale-95 disabled:opacity-50"
          >
            <span v-if="isResuming" class="inline-flex items-center gap-1.5">
              <span class="h-3 w-3 animate-spin rounded-full border-2 border-slate-950 border-t-transparent"></span>
              Resuming...
            </span>
            <span v-else class="inline-flex items-center gap-1.5">
              <span>Continue to Payment</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </span>
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

const props = defineProps({
  draft: {
    type: Object,
    default: null,
  },
  autoFetch: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['resumed', 'discarded', 'loaded']);
const router = useRouter();
const toast = useToast();

const fetchedDraft = ref(null);
const isDismissed = ref(false);
const isDiscarding = ref(false);
const isResuming = ref(false);
const secondsRemaining = ref(900); // 15 mins default
let countdownInterval = null;

const currentDraft = computed(() => {
  return props.draft || fetchedDraft.value;
});

const isExpired = computed(() => {
  return secondsRemaining.value <= 0;
});

const countdownText = computed(() => {
  if (secondsRemaining.value <= 0) {
    return 'Hold Expired';
  }
  const mins = Math.floor(secondsRemaining.value / 60);
  const secs = secondsRemaining.value % 60;
  return `${mins}:${secs.toString().padStart(2, '0')} left`;
});

const serviceName = computed(() => {
  return currentDraft.value?.service?.name || 'Selected Grooming Service';
});

const barberName = computed(() => {
  return currentDraft.value?.barber?.name || 'Master Stylist';
});

const appointmentDateText = computed(() => {
  const date = currentDraft.value?.appointment_date;
  const time = currentDraft.value?.appointment_time;
  if (!date) return 'Upcoming appointment';
  return `${date} at ${time || 'scheduled time'}`;
});

function calculateSecondsLeft(draftObj) {
  if (!draftObj) return 0;
  if (typeof draftObj.draft_time_remaining_seconds === 'number') {
    return Math.max(0, draftObj.draft_time_remaining_seconds);
  }
  if (draftObj.draft_expires_at) {
    const diff = Math.floor((new Date(draftObj.draft_expires_at).getTime() - Date.now()) / 1000);
    return Math.max(0, diff);
  }
  return 900;
}

let targetExpiryMs = null;

function syncTimer() {
  if (!currentDraft.value) return;
  if (currentDraft.value.draft_expires_at) {
    const expiresMs = new Date(currentDraft.value.draft_expires_at).getTime();
    secondsRemaining.value = Math.max(0, Math.floor((expiresMs - Date.now()) / 1000));
  } else if (targetExpiryMs) {
    secondsRemaining.value = Math.max(0, Math.floor((targetExpiryMs - Date.now()) / 1000));
  }
  if (secondsRemaining.value <= 0 && countdownInterval) {
    clearInterval(countdownInterval);
  }
}

function startTimer() {
  clearInterval(countdownInterval);
  if (!currentDraft.value) return;

  const initialSeconds = calculateSecondsLeft(currentDraft.value);
  targetExpiryMs = currentDraft.value.draft_expires_at
    ? new Date(currentDraft.value.draft_expires_at).getTime()
    : Date.now() + (initialSeconds * 1000);

  syncTimer();
  countdownInterval = setInterval(() => {
    syncTimer();
  }, 1000);
}

function handleVisibilityChange() {
  if (typeof document !== 'undefined' && !document.hidden) {
    syncTimer();
  }
}

async function fetchActiveDraft() {
  if (props.draft) {
    startTimer();
    return;
  }
  if (!props.autoFetch) return;

  try {
    const res = await client.get('/customer/drafts');
    const items = res.data?.data || res.data?.items || res.data || [];
    if (Array.isArray(items) && items.length > 0) {
      fetchedDraft.value = items[0];
      emit('loaded', items[0]);
      startTimer();
    } else {
      fetchedDraft.value = null;
    }
  } catch (err) {
    // Graceful fallback if unauthenticated or no drafts
    fetchedDraft.value = null;
  }
}

async function resume() {
  if (!currentDraft.value) return;
  isResuming.value = true;
  const draftId = currentDraft.value.id;

  try {
    // Call resume to extend hold TTL and verify slot
    await client.post(`/appointments/drafts/${draftId}/resume`).catch(() => {
      // Fallback endpoint if needed
      return client.post(`/customer/bookings/${draftId}/resume`);
    });
    emit('resumed', currentDraft.value);
    router.push(`/customer/dashboard/payment/${draftId}`);
  } catch (err) {
    const errMsg = err.response?.data?.message || 'Slot expired or unavailable. Please choose a new time.';
    toast.error(errMsg);
    // If slot collision on resume, redirect to book page
    if (err.response?.status === 409 || err.response?.status === 422) {
      router.push('/customer/dashboard/services');
    } else {
      router.push(`/customer/dashboard/payment/${draftId}`);
    }
  } finally {
    isResuming.value = false;
  }
}

async function discard() {
  if (!currentDraft.value) return;
  const draftId = currentDraft.value.id;
  isDiscarding.value = true;

  try {
    await client.delete(`/customer/drafts/${draftId}`).catch(() => {
      return client.delete(`/appointments/drafts/${draftId}`);
    });
    toast.success('Draft booking discarded. Your reserved slot is now free.');
    const discarded = currentDraft.value;
    fetchedDraft.value = null;
    isDismissed.value = true;
    emit('discarded', discarded);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to discard draft booking.');
  } finally {
    isDiscarding.value = false;
  }
}

watch(
  () => props.draft,
  (newVal) => {
    if (newVal) {
      startTimer();
    }
  },
  { immediate: true }
);

onMounted(() => {
  fetchActiveDraft();
  if (typeof window !== 'undefined') {
    window.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('focus', syncTimer);
  }
});

onUnmounted(() => {
  clearInterval(countdownInterval);
  if (typeof window !== 'undefined') {
    window.removeEventListener('visibilitychange', handleVisibilityChange);
    window.removeEventListener('focus', syncTimer);
  }
});
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
