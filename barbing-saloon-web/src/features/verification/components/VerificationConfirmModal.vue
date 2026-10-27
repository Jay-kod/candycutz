<template>
  <BaseModal :isOpen="isOpen" @close="closeModal">
    <template #title>Manual Verification Override</template>
    <template #content>
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 mb-3 text-emerald-400">
          <ShieldCheckIcon class="h-8 w-8" />
        </div>
        <p class="text-xs sm:text-sm text-white/70 max-w-sm mx-auto">
          You are about to manually verify this appointment. This overrides barber validation and immediately transitions status to <strong class="text-emerald-400">Completed</strong>.
        </p>
      </div>

      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-4 mb-6">
        <div class="grid grid-cols-2 gap-3.5 text-xs">
          <div>
            <p class="text-[10px] uppercase tracking-wider text-white/40 font-semibold mb-0.5">Customer</p>
            <p class="text-white font-medium truncate">{{ item?.customer_name || 'Walk-in Client' }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider text-white/40 font-semibold mb-0.5">Barber</p>
            <p class="text-white font-medium truncate">{{ item?.barber_name || 'Unassigned' }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider text-white/40 font-semibold mb-0.5">Service</p>
            <p class="text-white font-medium truncate">{{ item?.service_name || 'Custom Cut' }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wider text-white/40 font-semibold mb-0.5">Security Code</p>
            <p class="font-mono text-base font-bold text-emerald-400 tracking-wider">{{ item?.verification_code }}</p>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3">
        <button
          type="button"
          @click="closeModal"
          class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-white/70 hover:bg-white/10 hover:text-white transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="confirm"
          :disabled="isVerifying"
          class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-emerald-500/25 transition-all hover:bg-emerald-600 disabled:opacity-50"
        >
          <div v-if="isVerifying" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
          <ShieldCheckIcon v-else class="h-4 w-4" />
          <span>{{ isVerifying ? 'Verifying...' : 'Verify Now' }}</span>
        </button>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ShieldCheckIcon } from '@heroicons/vue/24/outline';
import BaseModal from '@/core/components/BaseModal.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  item: {
    type: Object,
    default: null
  },
  isVerifying: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'confirm']);

function closeModal() {
  emit('close');
}

function confirm() {
  if (props.item) {
    emit('confirm', props.item.id);
  }
}
</script>
