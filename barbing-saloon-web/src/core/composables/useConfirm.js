import { ref } from 'vue';

const isOpen = ref(false);
const message = ref('');
const title = ref('');
const confirmButtonText = ref('Confirm');
const cancelButtonText = ref('Cancel');
const variant = ref('danger'); // 'danger' | 'warning' | 'info' | 'primary'
const resolvePromise = ref(null);

export function useConfirm() {
  const confirm = (optionsOrTitle, messageText) => {
    if (typeof optionsOrTitle === 'string') {
      title.value = optionsOrTitle;
      message.value = messageText || 'Are you sure you want to proceed?';
      confirmButtonText.value = 'Confirm';
      cancelButtonText.value = 'Cancel';
      variant.value = 'danger';
    } else {
      const options = optionsOrTitle || {};
      title.value = options.title || 'Confirm Action';
      message.value = options.message || 'Are you sure you want to proceed?';
      confirmButtonText.value = options.confirmText || options.confirmButtonText || 'Confirm';
      cancelButtonText.value = options.cancelText || options.cancelButtonText || 'Cancel';
      variant.value = options.variant || options.type || (options.destructive ? 'danger' : 'primary');
    }
    isOpen.value = true;

    return new Promise((resolve) => {
      resolvePromise.value = resolve;
    });
  };

  const agree = () => {
    isOpen.value = false;
    if (resolvePromise.value) resolvePromise.value(true);
  };

  const cancel = () => {
    isOpen.value = false;
    if (resolvePromise.value) resolvePromise.value(false);
  };

  return {
    isOpen,
    title,
    message,
    confirmButtonText,
    cancelButtonText,
    variant,
    confirm,
    agree,
    cancel,
  };
}
