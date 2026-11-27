import { onUnmounted, ref } from 'vue';

/**
 * Provides clipboard copy and PDF export handlers for official payment receipts.
 * @param {import('vue').Ref<string>} bookingCode - Reactive booking code reference.
 * @param {() => void} onPrintFallback - Fallback callback when HTML target or PDF export fails.
 */
export function useReceiptExport(bookingCode, onPrintFallback) {
  const copied = ref(false);
  const downloading = ref(false);
  let copyResetTimer = null;

  onUnmounted(() => {
    if (copyResetTimer) {
      clearTimeout(copyResetTimer);
      copyResetTimer = null;
    }
  });

  const copyCode = async () => {
    const codeText = String(bookingCode.value);
    let didCopy = false;

    try {
      if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(codeText);
        didCopy = true;
      }
    } catch {
      didCopy = false;
    }

    if (!didCopy && typeof document !== 'undefined') {
      const textarea = document.createElement('textarea');
      textarea.value = codeText;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();
      try {
        didCopy = document.execCommand('copy');
      } catch {
        didCopy = false;
      } finally {
        document.body.removeChild(textarea);
      }
    }

    if (didCopy) {
      copied.value = true;
      if (copyResetTimer) clearTimeout(copyResetTimer);
      copyResetTimer = setTimeout(() => {
        copied.value = false;
        copyResetTimer = null;
      }, 1800);
    }
  };

  const downloadPdf = async () => {
    if (downloading.value || typeof window === 'undefined' || typeof document === 'undefined') return;
    const target = document.getElementById('printable-receipt');
    if (!target) {
      onPrintFallback?.();
      return;
    }

    downloading.value = true;
    try {
      const html2pdfModule = await import('html2pdf.js');
      const html2pdf = html2pdfModule.default || html2pdfModule;
      await html2pdf()
        .set({
          margin: 8,
          filename: `CandyCutz-Receipt-${bookingCode.value}.pdf`,
          image: { type: 'jpeg', quality: 0.98 },
          html2canvas: { scale: 2, backgroundColor: '#0B0C11' },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        })
        .from(target)
        .save();
    } catch {
      onPrintFallback?.();
    } finally {
      downloading.value = false;
    }
  };

  return {
    copied,
    downloading,
    copyCode,
    downloadPdf,
  };
}
