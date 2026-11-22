import { describe, it, expect, beforeEach, vi } from 'vitest';
import { reactive } from 'vue';
import { useBookingSessionHydration } from '../useBookingSessionHydration';

describe('useBookingSessionHydration', () => {
  const serviceId = 5;

  beforeEach(() => {
    sessionStorage.clear();
    vi.clearAllTimers();
  });

  it('saves form state to sessionStorage when save() is called', () => {
    const form = reactive({
      barber_id: 3,
      service_id: serviceId,
      appointment_date: '2026-11-25',
      appointment_time: '14:30',
      notes: 'Fade cut with sharp beard trim',
    });

    const { save } = useBookingSessionHydration(serviceId, form);
    save();

    const stored = sessionStorage.getItem(`candycutz_booking_session_${serviceId}`);
    expect(stored).not.toBeNull();

    const parsed = JSON.parse(stored);
    expect(parsed.barber_id).toBe(3);
    expect(parsed.appointment_date).toBe('2026-11-25');
    expect(parsed.appointment_time).toBe('14:30');
    expect(parsed.notes).toBe('Fade cut with sharp beard trim');
    expect(typeof parsed.saved_at).toBe('number');
  });

  it('hydrates form state from sessionStorage when valid', () => {
    const savedState = {
      service_id: serviceId,
      barber_id: 2,
      appointment_date: '2026-12-01',
      appointment_time: '11:00',
      notes: 'Previous draft notes',
      saved_at: Date.now(),
    };
    sessionStorage.setItem(`candycutz_booking_session_${serviceId}`, JSON.stringify(savedState));

    const form = reactive({
      barber_id: '',
      service_id: serviceId,
      appointment_date: '',
      appointment_time: '',
      notes: '',
    });

    const { hydrate, isHydrated } = useBookingSessionHydration(serviceId, form);
    const restored = hydrate();

    expect(restored).toBe(true);
    expect(isHydrated.value).toBe(true);
    expect(form.barber_id).toBe(2);
    expect(form.appointment_date).toBe('2026-12-01');
    expect(form.appointment_time).toBe('11:00');
    expect(form.notes).toBe('Previous draft notes');
  });

  it('discards expired session storage data (> TTL)', () => {
    const threeHoursAgo = Date.now() - 3 * 60 * 60 * 1000;
    const expiredState = {
      service_id: serviceId,
      barber_id: 2,
      appointment_date: '2026-12-01',
      appointment_time: '11:00',
      notes: 'Expired notes',
      saved_at: threeHoursAgo,
    };
    sessionStorage.setItem(`candycutz_booking_session_${serviceId}`, JSON.stringify(expiredState));

    const form = reactive({
      barber_id: '',
      service_id: serviceId,
      appointment_date: '',
      appointment_time: '',
      notes: '',
    });

    const { hydrate, isHydrated } = useBookingSessionHydration(serviceId, form, { ttlMs: 2 * 60 * 60 * 1000 });
    const restored = hydrate();

    expect(restored).toBe(false);
    expect(isHydrated.value).toBe(false);
    expect(form.barber_id).toBe('');
    expect(sessionStorage.getItem(`candycutz_booking_session_${serviceId}`)).toBeNull();
  });

  it('clears stored session data upon clear()', () => {
    const savedState = {
      service_id: serviceId,
      barber_id: 2,
      saved_at: Date.now(),
    };
    sessionStorage.setItem(`candycutz_booking_session_${serviceId}`, JSON.stringify(savedState));

    const form = reactive({ barber_id: 2 });
    const { clear, isHydrated } = useBookingSessionHydration(serviceId, form);

    clear();
    expect(sessionStorage.getItem(`candycutz_booking_session_${serviceId}`)).toBeNull();
    expect(isHydrated.value).toBe(false);
  });
});
