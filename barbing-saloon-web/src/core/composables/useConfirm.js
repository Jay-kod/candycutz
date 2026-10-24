import { ref } from 'vue';

const isOpen = ref(false);
const message = ref('');
const title = ref('');
const confirmButtonText = ref('Confirm');
const cancelButtonText = ref('Cancel');
const variant = ref('danger'); // 'danger' | 'warning' | 'info' | 'primary'
const isResolved = ref(false);
const isProcessing = ref(false);
const resolutionType = ref('deleted'); // 'deleted' | 'saved' | 'cancelled' | 'confirmed'
const resolutionTitle = ref('');
const resolutionMessage = ref('');
const autoDismissDelay = ref(1300);
const resolvePromise = ref(null);
const actionCallback = ref(null);

export function useConfirm() {
  const confirm = (optionsOrTitle, messageText) => {
    isResolved.value = false;
    isProcessing.value = false;
    actionCallback.value = null;

    if (typeof optionsOrTitle === 'string') {
      title.value = optionsOrTitle;
      message.value = messageText || 'Are you sure you want to proceed?';
      confirmButtonText.value = 'Confirm';
      cancelButtonText.value = 'Cancel';
      variant.value = 'danger';
      resolutionType.value = 'deleted';
      resolutionTitle.value = '';
      resolutionMessage.value = '';
      autoDismissDelay.value = 1300;
    } else {
      const options = optionsOrTitle || {};
      title.value = options.title || 'Confirm Action';
      message.value = options.message || 'Are you sure you want to proceed?';
      confirmButtonText.value = options.confirmText || options.confirmButtonText || 'Confirm';
      cancelButtonText.value = options.cancelText || options.cancelButtonText || 'Cancel';
      variant.value = options.variant || options.type || (options.destructive ? 'danger' : 'primary');
      resolutionType.value =
        options.resolutionType ||
        (variant.value === 'danger' || options.destructive ? 'deleted' : 'saved');
      resolutionTitle.value = options.resolutionTitle || '';
      resolutionMessage.value = options.resolutionMessage || '';
      autoDismissDelay.value = options.autoDismissDelay || 1300;
      if (typeof options.onConfirm === 'function') {
        actionCallback.value = options.onConfirm;
      }
    }
    if (dismissTimer) {
      clearTimeout(dismissTimer);
      dismissTimer = null;
    }
    isOpen.value = true;

    return new Promise((resolve) => {
      resolvePromise.value = resolve;
    });
  };

  const closeImmediately = () => {
    if (dismissTimer) {
      clearTimeout(dismissTimer);
      dismissTimer = null;
    }
    isOpen.value = false;
    isResolved.value = false;
    isProcessing.value = false;
    if (resolvePromise.value) {
      const resolver = resolvePromise.value;
      resolvePromise.value = null;
      resolver(true);
    }
  };

  const agree = async () => {
    if (isProcessing.value) return;
    isProcessing.value = true;

    try {
      if (actionCallback.value) {
        await actionCallback.value();
      }
      // Enter resolved animation state
      isResolved.value = true;
      dismissTimer = setTimeout(() => {
        closeImmediately();
      }, autoDismissDelay.value);
    } catch (err) {
      if (dismissTimer) {
        clearTimeout(dismissTimer);
        dismissTimer = null;
      }
      isProcessing.value = false;
      isResolved.value = false;
      throw err;
    }
  };

  const cancel = () => {
    if (dismissTimer) {
      clearTimeout(dismissTimer);
      dismissTimer = null;
    }
    isOpen.value = false;
    isResolved.value = false;
    isProcessing.value = false;
    if (resolvePromise.value) {
      const resolver = resolvePromise.value;
      resolvePromise.value = null;
      resolver(false);
    }
  };

  return {
    isOpen,
    title,
    message,
    confirmButtonText,
    cancelButtonText,
    variant,
    isResolved,
    isProcessing,
    resolutionType,
    resolutionTitle,
    resolutionMessage,
    autoDismissDelay,
    confirm,
    agree,
    cancel,
    closeImmediately,
  };
}
