import { ref, watch } from 'vue';

const STORAGE_PREFIX = 'candycutz_booking_session_';
const DEFAULT_TTL_MS = 2 * 60 * 60 * 1000; // 2 hours

/**
 * Manages persisting and hydrating booking form state across browser refreshes,
 * tab closures, and cellular network dropouts.
 *
 * @param {string|number} serviceId
 * @param {Object} form - Reactive form object { barber_id, service_id, appointment_date, appointment_time, notes }
 * @param {Object} [options]
 * @param {number} [options.ttlMs]
 */
export function useBookingSessionHydration(serviceId, form, options = {}) {
  const ttlMs = options.ttlMs || DEFAULT_TTL_MS;
  const storageKey = `${STORAGE_PREFIX}${serviceId}`;
  const isHydrated = ref(false);
  const hydratedAt = ref(null);

  /**
   * Safe sessionStorage accessor supporting SSR and restricted iframe contexts.
   */
  function getStorage() {
    if (typeof window !== 'undefined' && window.sessionStorage) {
      try {
        return window.sessionStorage;
      } catch {
        return null;
      }
    }
    return null;
  }

  /**
   * Save current form values to sessionStorage.
   */
  function save() {
    const storage = getStorage();
    if (!storage || !serviceId) return;

    // Do not save if form is completely empty
    if (!form.barber_id && !form.appointment_date && !form.appointment_time && !form.notes) {
      return;
    }

    try {
      const payload = {
        service_id: serviceId,
        barber_id: form.barber_id || '',
        appointment_date: form.appointment_date || '',
        appointment_time: form.appointment_time || '',
        notes: form.notes || '',
        saved_at: Date.now(),
      };
      storage.setItem(storageKey, JSON.stringify(payload));
    } catch {
      // Ignore quota exceeded or storage disabled
    }
  }

  /**
   * Hydrate form values from sessionStorage if valid and not expired.
   *
   * @returns {boolean} True if data was restored
   */
  function hydrate() {
    const storage = getStorage();
    if (!storage || !serviceId) return false;

    try {
      const raw = storage.getItem(storageKey);
      if (!raw) return false;

      const data = JSON.parse(raw);
      if (!data || typeof data !== 'object') return false;

      // Check TTL expiration
      const savedAt = Number(data.saved_at) || 0;
      if (Date.now() - savedAt > ttlMs) {
        clear();
        return false;
      }

      // Ensure saved appointment date is not in the past
      if (data.appointment_date) {
        const todayStr = new Date().toISOString().split('T')[0];
        if (data.appointment_date < todayStr) {
          clear();
          return false;
        }
      }

      // Restore non-empty values
      if (data.barber_id) form.barber_id = Number(data.barber_id);
      if (data.appointment_date) form.appointment_date = data.appointment_date;
      if (data.appointment_time) form.appointment_time = data.appointment_time;
      if (data.notes) form.notes = data.notes;

      isHydrated.value = true;
      hydratedAt.value = savedAt;
      return true;
    } catch {
      return false;
    }
  }

  /**
   * Clear saved booking session for this service.
   */
  function clear() {
    const storage = getStorage();
    if (!storage || !serviceId) return;

    try {
      storage.removeItem(storageKey);
      isHydrated.value = false;
      hydratedAt.value = null;
    } catch {
      // Ignore
    }
  }

  // Auto-save on form mutations
  let saveDebounceTimer = null;
  watch(
    () => [form.barber_id, form.appointment_date, form.appointment_time, form.notes],
    () => {
      if (saveDebounceTimer) clearTimeout(saveDebounceTimer);
      saveDebounceTimer = setTimeout(() => {
        save();
      }, 300);
    },
    { deep: true }
  );

  return {
    isHydrated,
    hydratedAt,
    save,
    hydrate,
    clear,
  };
}
