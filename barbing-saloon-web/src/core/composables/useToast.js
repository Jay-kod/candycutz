import { ref } from 'vue';

const toasts = ref([]);
let idCounter = 0;

export function useToast() {
  function addToast(type, message, duration = 4000) {
    const id = ++idCounter;
    toasts.value.push({ id, type, message, visible: true });

    setTimeout(() => {
      dismiss(id);
    }, duration);

    return id;
  }

  function dismiss(id) {
    const t = toasts.value.find(t => t.id === id);
    if (t) t.visible = false;
    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    }, 400); // allow exit animation to complete
  }

  const success = (message, duration) => addToast('success', message, duration);
  const error = (message, duration) => addToast('error', message, duration || 5000);
  const warning = (message, duration) => addToast('warning', message, duration);
  const info = (message, duration) => addToast('info', message, duration);

  return { toasts, success, error, warning, info, dismiss };
}
