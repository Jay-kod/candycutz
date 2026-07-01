<template>
  <Teleport to="body">
    <div class="fixed top-0 left-0 right-0 z-[9999] flex flex-col items-center pointer-events-none px-4 pt-4 gap-3">
      <transition-group name="toast-peel">
        <div
          v-for="t in toasts"
          :key="t.id"
          v-show="t.visible"
          :class="[
            'pointer-events-auto relative w-full max-w-2xl rounded-sm shadow-xl transition-all duration-300 overflow-hidden',
            typeClasses[t.type].bg
          ]"
        >
          <!-- Left Tick Progress Bar -->
          <div class="absolute left-2 top-2.5 bottom-2.5 w-[5px] z-10 bg-black/20 rounded-[3px]">
            <div
              :class="['w-full h-full rounded-[3px]', typeClasses[t.type].bar]"
              :style="{ animation: `toast-progress ${t.type === 'error' ? 5 : 4}s linear forwards` }"
            ></div>
          </div>

          <div class="flex items-center justify-between pl-6 pr-4 py-3.5 w-full relative z-20">
            <div class="flex items-center gap-4 min-w-0">
              <!-- Icon -->
              <div class="shrink-0 flex items-center justify-center text-white/90">
                <CheckCircleIcon v-if="t.type === 'success'" class="h-[22px] w-[22px]" />
                <XCircleIcon v-else-if="t.type === 'error'" class="h-[22px] w-[22px]" />
                <ExclamationCircleIcon v-else-if="t.type === 'warning'" class="h-[22px] w-[22px]" />
                <InformationCircleIcon v-else class="h-[22px] w-[22px]" />
              </div>
              
              <!-- Content -->
              <div class="flex-1 min-w-0">
                <p class="text-[15px] font-bold text-[#f8f9fa] leading-tight mb-0.5 tracking-wide">{{ typeLabels[t.type] }}</p>
                <p class="text-[14px] text-[#adb5bd] leading-tight pr-2">{{ t.message }}</p>
              </div>
            </div>

            <!-- Close Button -->
            <button 
              type="button"
              @click.stop="dismiss(t.id)" 
              :class="[
                'shrink-0 text-[13px] font-semibold px-4 py-1.5 rounded transition-colors pointer-events-auto cursor-pointer relative z-50',
                typeClasses[t.type].btn
              ]"
            >
              {{ t.type === 'info' ? 'Dismiss' : 'Close' }}
            </button>
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
  XCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/solid';

const { toasts, dismiss } = useToast();

const typeClasses = {
  success: {
    bg: 'bg-[#0f3521]',
    bar: 'bg-[#10b87f]',
    btn: 'bg-[#165a37] text-[#7df5c2] hover:bg-[#1a6b41]'
  },
  error: {
    bg: 'bg-[#4b1515]',
    bar: 'bg-[#ef4343]',
    btn: 'bg-[#7b2222] text-[#f79b9b] hover:bg-[#8f2828]'
  },
  warning: {
    bg: 'bg-[#472b07]',
    bar: 'bg-[#f59e0a]',
    btn: 'bg-[#76470f] text-[#f9c878] hover:bg-[#8c5412]'
  },
  info: {
    bg: 'bg-[#192444]',
    bar: 'bg-[#2462e9]',
    btn: 'bg-[#2b3f79] text-[#9db2f5] hover:bg-[#324a8f]'
  }
};

const typeLabels = {
  success: 'Success!',
  error: 'Error!',
  warning: 'Warning!',
  info: 'Heads up!',
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
  from { height: 100%; }
  to { height: 0%; }
}
</style>
