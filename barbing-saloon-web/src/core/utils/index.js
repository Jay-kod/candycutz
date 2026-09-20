export const noop = () => {};

/**
 * Format kobo (minor currency units) to naira (major currency units) with proper formatting
 * @param {number} kobo - Amount in kobo (e.g., 500000 = ₦5,000.00)
 * @param {object} options - Formatting options
 * @param {string} options.currency - Currency code (default: 'NGN')
 * @param {string} options.locale - Locale for formatting (default: 'en-NG')
 * @param {boolean} options.showSymbol - Whether to show currency symbol (default: true)
 * @param {number} options.minimumFractionDigits - Minimum fraction digits (default: 2)
 * @param {number} options.maximumFractionDigits - Maximum fraction digits (default: 2)
 * @returns {string} Formatted currency string
 */
export function formatCurrency(kobo, options = {}) {
  const {
    currency = 'NGN',
    locale = 'en-NG',
    showSymbol = true,
    minimumFractionDigits = 2,
    maximumFractionDigits = 2,
  } = options;

  if (typeof kobo !== 'number' || isNaN(kobo)) {
    return showSymbol ? '₦0.00' : '0.00';
  }

  // Convert kobo to naira
  const naira = kobo / 100;

  // Use Intl.NumberFormat for proper currency formatting
  try {
    const formatter = new Intl.NumberFormat(locale, {
      style: showSymbol ? 'currency' : 'decimal',
      currency: showSymbol ? currency : undefined,
      minimumFractionDigits,
      maximumFractionDigits,
    });

    return formatter.format(naira);
  } catch (e) {
    // Fallback for environments without full Intl support
    const formatted = naira.toLocaleString(locale, {
      minimumFractionDigits,
      maximumFractionDigits,
    });
    return showSymbol ? `₦${formatted}` : formatted;
  }
}

/**
 * Format kobo to naira without currency symbol
 * @param {number} kobo - Amount in kobo
 * @returns {string} Formatted number string (e.g., "5,000.00")
 */
export function formatNaira(kobo) {
  return formatCurrency(kobo, { showSymbol: false });
}

/**
 * Convert naira to kobo
 * @param {number} naira - Amount in naira
 * @returns {number} Amount in kobo
 */
export function nairaToKobo(naira) {
  return Math.round(naira * 100);
}

/**
 * Convert kobo to naira
 * @param {number} kobo - Amount in kobo
 * @returns {number} Amount in naira
 */
export function koboToNaira(kobo) {
  return kobo / 100;
}

export const url = (() => {
  // ... existing url utility
})();