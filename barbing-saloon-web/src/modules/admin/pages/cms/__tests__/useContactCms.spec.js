import { describe, expect, it, vi, beforeEach } from 'vitest';
import { nextTick } from 'vue';

const { mockApi, mockToast } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
    post: vi.fn(),
  },
  mockToast: {
    success: vi.fn(),
    error: vi.fn(),
  },
}));

vi.mock('@/shared/api/client', () => ({
  default: mockApi,
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => mockToast,
}));

import { useContactCms } from '../composables/useContactCms';

describe('useContactCms', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('extracts google maps iframe src and coordinates, persists them on save, and restores them on reload', async () => {
    const embedSrc = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5!2d7.891234!3d8.862345';
    const iframeHtml = `<iframe src="${embedSrc}" width="600" height="450"></iframe>`;

    mockApi.get.mockResolvedValueOnce({
      data: {
        data: {
          contact_address: 'Old Address',
          contact_map_iframe: '',
          latitude: '',
          longitude: '',
        },
      },
    });

    const cms = useContactCms();
    await cms.fetchContent();

    cms.content.value.contact_map_iframe = iframeHtml;
    await nextTick();

    expect(cms.extractedMapSrc.value).toBe(embedSrc);
    expect(cms.content.value.latitude).toBe('8.862345');
    expect(cms.content.value.longitude).toBe('7.891234');

    mockApi.post.mockResolvedValueOnce({
      data: {
        data: {
          contact_map_iframe: embedSrc,
          latitude: '8.862345',
          longitude: '7.891234',
        },
      },
    });

    await cms.saveContent(null);

    const [, postedFormData] = mockApi.post.mock.calls[0];
    expect(postedFormData.get('settings[contact_map_iframe]')).toBe(embedSrc);
    expect(postedFormData.get('settings[latitude]')).toBe('8.862345');
    expect(postedFormData.get('settings[longitude]')).toBe('7.891234');
    expect(cms.content.value.contact_map_iframe).toBe(embedSrc);
    expect(mockToast.success).toHaveBeenCalled();
  });
});
