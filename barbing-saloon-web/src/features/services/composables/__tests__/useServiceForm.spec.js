import { beforeEach, describe, expect, it, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
  route: { path: '/admin/services/1/edit', params: { id: '1' } },
  router: { push: vi.fn() },
  client: { get: vi.fn(), post: vi.fn() },
  toast: { error: vi.fn(), success: vi.fn() },
}));

vi.mock('vue-router', () => ({
  useRoute: () => mocks.route,
  useRouter: () => mocks.router,
}));

vi.mock('@/shared/api/client', () => ({
  default: mocks.client,
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => mocks.toast,
}));

import { useServiceForm } from '../useServiceForm';

describe('useServiceForm', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mocks.route.path = '/admin/services/1/edit';
    mocks.route.params = { id: '1' };
  });

  it('loads an edited service by ID and preserves its active state', async () => {
    mocks.client.get.mockImplementation((path) => {
      if (path === '/service-categories') {
        return Promise.resolve({ data: { data: [{ id: 7, name: 'Haircuts' }] } });
      }

      if (path === '/services/1') {
        return Promise.resolve({
          data: {
            data: {
              id: 1,
              name: 'Classic Fade',
              price: 25,
              category_id: 7,
              is_active: false,
            },
          },
        });
      }

      return Promise.reject(new Error(`Unexpected request: ${path}`));
    });

    const serviceForm = useServiceForm();
    await serviceForm.loadData();

    expect(mocks.client.get).toHaveBeenCalledWith('/service-categories');
    expect(mocks.client.get).toHaveBeenCalledWith('/services/1');
    expect(mocks.client.get).not.toHaveBeenCalledWith('/v1/admin/services');
    expect(serviceForm.form.value.name).toBe('Classic Fade');
    expect(serviceForm.form.value.category_id).toBe(7);
    expect(serviceForm.form.value.is_available).toBe(false);
    expect(serviceForm.loadingData.value).toBe(false);
    expect(mocks.toast.error).not.toHaveBeenCalled();
  });

  it('submits changes to the API before navigating away', async () => {
    mocks.client.post.mockResolvedValue({ data: { message: 'Service updated' } });

    const serviceForm = useServiceForm();
    serviceForm.form.value.name = 'Classic Fade';
    serviceForm.form.value.price = 25;
    serviceForm.form.value.is_available = false;

    await serviceForm.submitForm();

    expect(mocks.client.post).toHaveBeenCalledTimes(1);
    const [path, payload] = mocks.client.post.mock.calls[0];
    expect(path).toBe('/services/1');
    expect(payload.get('_method')).toBe('PUT');
    expect(payload.get('is_active')).toBe('0');
    expect(mocks.toast.success).toHaveBeenCalledWith('Service updated');
    expect(mocks.router.push).toHaveBeenCalledWith('/admin/services');
  });
});
