<template>
  <BaseModal :isOpen="isOpen" @close="closeModal">
    <template #title>Manual Verification</template>
    <template #content>
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 mb-4">
          <ShieldCheckIcon class="h-8 w-8 text-emerald-400" />
        </div>
        <p class="text-sm text-white/70">
          You are about to manually verify this appointment. This will bypass the barber verification and mark the appointment as <strong class="text-emerald-400">completed</strong>.
        </p>
      </div>

      <div class="bg-black/30 border border-white/5 rounded-2xl p-5 mb-6">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Customer</p>
            <p class="text-white font-semibold">{{ item?.customer_name || 'Walk-in' }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Barber</p>
            <p class="text-white font-semibold">{{ item?.barber_name || 'Unassigned' }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Service</p>
            <p class="text-white font-semibold">{{ item?.service_name }}</p>
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-widest text-white/30 font-bold mb-1">Verification Code</p>
            <p class="font-mono text-lg text-emerald-400 tracking-widest font-bold">{{ item?.verification_code }}</p>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <BaseButton variant="secondary" @click="closeModal">Cancel</BaseButton>
        <BaseButton @click="confirm" :isLoading="isVerifying" class="!bg-emerald-500 hover:!bg-emerald-600 !text-white !border-none !shadow-[0_0_20px_rgba(16,185,129,0.3)]">
          <ShieldCheckIcon class="h-4 w-4 mr-1" />
          Verify Now
        </BaseButton>
      </div>
    </template>
  </BaseModal>
</template>

<script setup>
import { ShieldCheckIcon } from '@heroicons/vue/24/outline';
import BaseModal from '@/core/components/BaseModal.vue';
import BaseButton from '@/core/components/BaseButton.vue';

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
