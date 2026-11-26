import { describe, expect, it, vi, beforeEach } from 'vitest';

const { mockClient } = vi.hoisted(() => ({
  mockClient: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
  },
}));

vi.mock('@/shared/api/client', () => ({
  default: mockClient,
}));

import { noShowApi } from '../api/noShowApi';

describe('noShowApi', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('calls expected admin, barber, and customer endpoints', () => {
    noShowApi.getDashboard({ filter: 'pending_appeals' });
    expect(mockClient.get).toHaveBeenCalledWith('/admin/no-shows', { params: { filter: 'pending_appeals' } });

    noShowApi.updatePolicy({ no_show_delay_minutes: 20 });
    expect(mockClient.post).toHaveBeenCalledWith('/admin/no-shows/policy', { no_show_delay_minutes: 20 });

    noShowApi.forfeitAppointmentDeposit(42, 'Missed slot');
    expect(mockClient.post).toHaveBeenCalledWith('/appointments/42/forfeit-deposit', { reason: 'Missed slot' });

    noShowApi.extendGrace(42, 'Stuck in traffic');
    expect(mockClient.post).toHaveBeenCalledWith('/appointments/42/grace-extension', { reason: 'Stuck in traffic' });

    noShowApi.appealNoShow(9, 'Unwell');
    expect(mockClient.post).toHaveBeenCalledWith('/no-shows/9/appeal', { appeal_reason: 'Unwell' });

    noShowApi.initiateRefund(9, { amount_minor: 500000, reason: 'Approved' });
    expect(mockClient.post).toHaveBeenCalledWith('/admin/no-shows/9/initiate-refund', {
      amount_minor: 500000,
      reason: 'Approved',
    });
  });
});
