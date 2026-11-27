import { describe, it, expect, vi, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { usePublicContentStore } from '../publicContent.store';
import { publicApi } from '@/shared/api/old_publicApi';

vi.mock('@/shared/api/old_publicApi', () => ({
  publicApi: {
    settings: vi.fn(),
    services: vi.fn(),
    serviceCategories: vi.fn(),
    testimonials: vi.fn(),
  },
}));

describe('publicContent.store', () => {
  beforeEach(() => {
    window.sessionStorage.clear();
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  it('fetches settings on first call and returns cached on subsequent calls', async () => {
    const store = usePublicContentStore();
    publicApi.settings.mockResolvedValueOnce({
      data: {
        data: { shop_name: 'CandyCutz Test', hero_title: 'Premium Cuts' },
      },
    });

    const first = await store.getSettings();
    expect(first.shop_name).toBe('CandyCutz Test');
    expect(publicApi.settings).toHaveBeenCalledTimes(1);

    // Second call should return cached without triggering API again
    const second = await store.getSettings();
    expect(second.shop_name).toBe('CandyCutz Test');
    expect(publicApi.settings).toHaveBeenCalledTimes(1);
  });

  it('re-fetches settings when forced or invalidated', async () => {
    const store = usePublicContentStore();
    publicApi.settings
      .mockResolvedValueOnce({ data: { data: { shop_name: 'V1' } } })
      .mockResolvedValueOnce({ data: { data: { shop_name: 'V2' } } });

    await store.getSettings();
    expect(store.settings.shop_name).toBe('V1');

    store.invalidate('settings');
    await store.getSettings();
    expect(store.settings.shop_name).toBe('V2');
    expect(publicApi.settings).toHaveBeenCalledTimes(2);
  });

  it('fetches services and caches the array', async () => {
    const store = usePublicContentStore();
    publicApi.services.mockResolvedValueOnce({
      data: {
        data: [{ id: 1, name: 'Skin Fade' }, { id: 2, name: 'Beard Trim' }],
      },
    });

    const services = await store.getServices();
    expect(services).toHaveLength(2);
    expect(services[0].name).toBe('Skin Fade');
    expect(publicApi.services).toHaveBeenCalledTimes(1);

    const cached = await store.getServices();
    expect(cached).toHaveLength(2);
    expect(publicApi.services).toHaveBeenCalledTimes(1);
  });

  it('fetches categories and caches the array', async () => {
    const store = usePublicContentStore();
    publicApi.serviceCategories.mockResolvedValueOnce({
      data: {
        data: [{ id: 10, name: 'Haircuts' }, { id: 20, name: 'Beard' }],
      },
    });

    const categories = await store.getCategories();
    expect(categories).toHaveLength(2);
    expect(categories[0].name).toBe('Haircuts');
    expect(publicApi.serviceCategories).toHaveBeenCalledTimes(1);

    const cached = await store.getCategories();
    expect(cached).toHaveLength(2);
    expect(publicApi.serviceCategories).toHaveBeenCalledTimes(1);
  });

  it('formats testimonials and limits to 12 items', async () => {
    const store = usePublicContentStore();
    const rawItems = Array.from({ length: 15 }, (_, i) => ({
      id: i + 1,
      comment: `Review ${i + 1}`,
      client_name: `Client ${i + 1}`,
    }));

    publicApi.testimonials.mockResolvedValueOnce({
      data: { data: rawItems },
    });

    const testimonials = await store.getTestimonials();
    expect(testimonials).toHaveLength(12);
    expect(testimonials[0].comment).toBe('Review 1');
  });

  it('prevents concurrent duplicate requests when revalidating', async () => {
    const store = usePublicContentStore();
    let resolvePromise;
    const slowPromise = new Promise((resolve) => {
      resolvePromise = resolve;
    });

    publicApi.settings.mockReturnValueOnce(slowPromise);

    const call1 = store.revalidateSettings();
    const call2 = store.revalidateSettings();

    resolvePromise({
      data: { data: { shop_name: 'Deduplicated' } },
    });

    await Promise.all([call1, call2]);
    expect(publicApi.settings).toHaveBeenCalledTimes(1);
  });
});
