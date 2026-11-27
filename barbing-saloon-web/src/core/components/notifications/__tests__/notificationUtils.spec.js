import { describe, it, expect } from 'vitest';
import {
  extractNotificationMeta,
  matchesSearchQuery,
  matchesTimeFilter,
  matchesTypeFilter,
  resolveNotificationTarget,
} from '../notificationUtils';

describe('notificationUtils enhancements', () => {
  it('matches booking, payment, review, and system filters accurately', () => {
    expect(matchesTypeFilter({ type: 'booking_completed' }, 'booking')).toBe(true);
    expect(matchesTypeFilter({ type: 'barber_assigned' }, 'booking')).toBe(true);
    expect(matchesTypeFilter({ type: 'payment_verified' }, 'payment')).toBe(true);
    expect(matchesTypeFilter({ type: 'refund_completed' }, 'payment')).toBe(true);
    expect(matchesTypeFilter({ type: 'review_approved' }, 'review')).toBe(true);
    expect(matchesTypeFilter({ type: 'password_changed' }, 'system')).toBe(true);
    expect(matchesTypeFilter({ type: 'announcement' }, 'system')).toBe(true);
    expect(matchesTypeFilter({ type: 'payment_verified' }, 'booking')).toBe(false);
  });

  it('filters by time periods accurately', () => {
    const fixedNow = new Date(2026, 10, 25, 14, 0, 0);
    const todayNotif = { created_at: new Date(2026, 10, 25, 10, 0, 0).toISOString() };
    const yesterdayNotif = { created_at: new Date(2026, 10, 24, 10, 0, 0).toISOString() };
    const earlierMonthNotif = { created_at: new Date(2026, 10, 3, 10, 0, 0).toISOString() };

    expect(matchesTimeFilter(todayNotif, 'today', fixedNow)).toBe(true);
    expect(matchesTimeFilter(yesterdayNotif, 'today', fixedNow)).toBe(false);
    expect(matchesTimeFilter(yesterdayNotif, 'yesterday', fixedNow)).toBe(true);
    expect(matchesTimeFilter(earlierMonthNotif, 'month', fixedNow)).toBe(true);
  });

  it('extracts structured metadata from object or JSON string payloads', () => {
    const fromObject = extractNotificationMeta({
      data: {
        booking_reference: 'CC-2026-88',
        verification_code: 'XK92M4',
        amount: '5,000.00',
        barber_name: 'Marcus Vance',
      },
    });
    expect(fromObject.bookingReference).toBe('CC-2026-88');
    expect(fromObject.verificationCode).toBe('XK92M4');
    expect(fromObject.amount).toBe('5,000.00');
    expect(fromObject.barberName).toBe('Marcus Vance');

    const fromJson = extractNotificationMeta({
      data: JSON.stringify({ booking_reference: 'CC-JSON-1', verification_code: 'AB12CD' }),
    });
    expect(fromJson.bookingReference).toBe('CC-JSON-1');
    expect(fromJson.verificationCode).toBe('AB12CD');

    const invalid = extractNotificationMeta({ data: '{bad-json' });
    expect(invalid.bookingReference).toBeNull();
  });

  it('matches search queries across title, message, reference, and verification code', () => {
    const notif = {
      title: 'Payment Confirmed',
      message: 'Your session payment was verified.',
      type: 'payment_verified',
      data: {
        booking_reference: 'CC-REF-77',
        verification_code: 'CUT-991',
        barber_name: 'Elena',
      },
    };

    expect(matchesSearchQuery(notif, 'cut-991')).toBe(true);
    expect(matchesSearchQuery(notif, 'cc-ref-77')).toBe(true);
    expect(matchesSearchQuery(notif, 'elena')).toBe(true);
    expect(matchesSearchQuery(notif, 'missing-term')).toBe(false);
  });

  it('resolves valid customer, barber, and admin routes and normalizes legacy URLs', () => {
    expect(
      resolveNotificationTarget(
        {
          type: 'booking_completed',
          related_entity_id: 42,
          action_url: '/customer/dashboard/reviews?appointment_id=42',
        },
        '/customer/dashboard/notifications'
      )
    ).toEqual({
      to: '/customer/dashboard/reviews?appointment_id=42',
      label: 'Leave a Review',
    });

    expect(
      resolveNotificationTarget(
        {
          type: 'payment_failed',
          related_entity_id: 19,
          action_url: '/customer/bookings/19',
        },
        '/customer/dashboard/notifications'
      )
    ).toEqual({
      to: '/customer/dashboard/bookings',
      label: 'Retry Payment',
    });

    expect(
      resolveNotificationTarget(
        {
          type: 'booking_created',
          related_entity_id: 12,
          action_url: null,
        },
        '/customer/dashboard/notifications'
      )
    ).toEqual({
      to: '/customer/dashboard/bookings',
      label: 'View Bookings',
    });

    expect(
      resolveNotificationTarget(
        {
          type: 'system_update',
          action_url: 'https://evil.example.com/phish',
        },
        '/customer/dashboard/notifications'
      )
    ).toBeNull();

    expect(
      resolveNotificationTarget(
        {
          type: 'category_status_changed',
          action_url: '/barber/categories',
        },
        '/barber/notifications'
      )
    ).toEqual({
      to: '/barber/service-categories',
      label: 'Open Link',
    });
  });
});
