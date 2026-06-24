import { ref } from 'vue';

const isOpen = ref(false);
const message = ref('');
const title = ref('');
const confirmButtonText = ref('Confirm');
const resolvePromise = ref(null);

export function useConfirm() {
  const confirm = (optionsOrTitle, messageText) => {
    if (typeof optionsOrTitle === 'string') {
      title.value = optionsOrTitle;
      message.value = messageText || 'Are you sure you want to proceed?';
      confirmButtonText.value = 'Confirm';
    } else {
      const options = optionsOrTitle || {};
      title.value = options.title || 'Confirm Action';
      message.value = options.message || 'Are you sure you want to proceed?';
      confirmButtonText.value = options.confirmText || 'Confirm';
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

  return { isOpen, title, message, confirmButtonText, confirm, agree, cancel };
}
