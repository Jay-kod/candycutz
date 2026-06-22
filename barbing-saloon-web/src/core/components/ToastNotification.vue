<template>
  <Teleport to="body">
    <div class="fixed top-0 left-0 right-0 z-[9999] flex flex-col items-center pointer-events-none px-4 pt-4 gap-3">
      <transition-group name="toast-peel">
        <div
          v-for="t in toasts"
          :key="t.id"
          :class="[
            'pointer-events-auto w-full max-w-md rounded-2xl border px-5 py-4 shadow-2xl backdrop-blur-xl transition-all duration-300 cursor-pointer',
            typeClasses[t.type]
          ]"
          @click="dismiss(t.id)"
        >
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div :class="['mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full', iconBgClasses[t.type]]">
              <CheckCircleIcon v-if="t.type === 'success'" class="h-5 w-5" />
              <ExclamationCircleIcon v-else-if="t.type === 'error'" class="h-5 w-5" />
              <ExclamationTriangleIcon v-else-if="t.type === 'warning'" class="h-5 w-5" />
              <InformationCircleIcon v-else class="h-5 w-5" />
            </div>
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold uppercase tracking-widest opacity-70">{{ typeLabels[t.type] }}</p>
              <p class="mt-0.5 text-sm font-medium leading-snug">{{ t.message }}</p>
            </div>
            <!-- Close -->
            <button @click.stop="dismiss(t.id)" class="shrink-0 rounded-lg p-1 opacity-50 hover:opacity-100 transition-opacity">
              <XMarkIcon class="h-4 w-4" />
            </button>
          </div>
          <!-- Progress bar -->
          <div class="mt-3 h-0.5 w-full overflow-hidden rounded-full bg-white/10">
            <div
              :class="['h-full rounded-full', progressClasses[t.type]]"
              :style="{ animation: `toast-progress ${t.type === 'error' ? 5 : 4}s linear forwards` }"
            ></div>
          </div>
        </div>
      </transition-group>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '../composables/useToast';
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/solid';

const { toasts, dismiss } = useToast();

const typeClasses = {
  success: 'border-emerald-500/30 bg-emerald-950/80 text-emerald-100',
  error: 'border-red-500/30 bg-red-950/80 text-red-100',
  warning: 'border-amber-500/30 bg-amber-950/80 text-amber-100',
  info: 'border-sky-500/30 bg-sky-950/80 text-sky-100',
};

const iconBgClasses = {
  success: 'bg-emerald-500/20 text-emerald-400',
  error: 'bg-red-500/20 text-red-400',
  warning: 'bg-amber-500/20 text-amber-400',
  info: 'bg-sky-500/20 text-sky-400',
};

const progressClasses = {
  success: 'bg-emerald-400',
  error: 'bg-red-400',
  warning: 'bg-amber-400',
  info: 'bg-sky-400',
};

const typeLabels = {
  success: 'Success',
  error: 'Error',
  warning: 'Warning',
  info: 'Info',
};
</script>

<style scoped>
.toast-peel-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-peel-leave-active {
  transition: all 0.35s cubic-bezier(0.6, -0.28, 0.74, 0.05);
}
.toast-peel-enter-from {
  opacity: 0;
  transform: translateY(-100%) scale(0.9);
}
.toast-peel-leave-to {
  opacity: 0;
  transform: translateY(-40px) scale(0.95);
}
.toast-peel-move {
  transition: transform 0.3s ease;
}

@keyframes toast-progress {
  from { width: 100%; }
  to { width: 0%; }
}
</style>
