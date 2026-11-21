import { beforeEach, describe, expect, it, vi } from 'vitest';
import { useAdminFeatureFlags } from '../useAdminFeatureFlags';

const { apiMock } = vi.hoisted(() => ({
  apiMock: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}));

vi.mock('@/shared/api/client', () => ({ default: apiMock }));
vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({ error: vi.fn(), info: vi.fn(), success: vi.fn() }),
}));
vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({ confirm: vi.fn() }),
}));

describe('useAdminFeatureFlags', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('shows web-targeted flags and keeps existing target metadata unchanged when editing', async () => {
    const appOnlyFlag = { id: 1, key: 'app_only', enabled_for: ['app'], is_active: true };
    const sharedFlag = { id: 2, key: 'shared', enabled_for: ['web', 'app'], is_active: true };
    const webFlag = { id: 3, key: 'web_only', enabled_for: ['web'], is_active: false };
    apiMock.get.mockResolvedValue({ data: { data: [appOnlyFlag, sharedFlag, webFlag] } });

    const featureFlags = useAdminFeatureFlags();
    await featureFlags.fetchFlags();

    expect(featureFlags.stats.value.totalCount).toBe(2);
    expect(featureFlags.filteredFlags.value.map(flag => flag.key)).toEqual(['web_only', 'shared']);

    featureFlags.openEditModal(sharedFlag);
    expect(featureFlags.form.value.enabled_for).toEqual(['web', 'app']);

    featureFlags.openCreateModal();
    expect(featureFlags.form.value.enabled_for).toEqual(['web']);
  });
});
