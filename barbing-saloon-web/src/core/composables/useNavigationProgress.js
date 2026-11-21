import { ref } from 'vue';

const isNavigating = ref(false);
const progress = ref(0);
const hasError = ref(false);
let progressTimer = null;

export function useNavigationProgress() {
  const start = () => {
    hasError.value = false;
    isNavigating.value = true;
    progress.value = 22;

    if (progressTimer) clearInterval(progressTimer);

    progressTimer = setInterval(() => {
      if (progress.value < 85) {
        const diff = (85 - progress.value) * 0.15;
        progress.value += Math.max(diff, 1.5);
      }
    }, 100);
  };

  const finish = () => {
    if (progressTimer) clearInterval(progressTimer);
    progress.value = 100;
    setTimeout(() => {
      isNavigating.value = false;
      setTimeout(() => {
        progress.value = 0;
        hasError.value = false;
      }, 180);
    }, 150);
  };

  const fail = () => {
    if (progressTimer) clearInterval(progressTimer);
    hasError.value = true;
    progress.value = 100;
    setTimeout(() => {
      isNavigating.value = false;
      setTimeout(() => {
        progress.value = 0;
        hasError.value = false;
      }, 200);
    }, 350);
  };

  return {
    isNavigating,
    progress,
    hasError,
    start,
    finish,
    fail,
  };
}
