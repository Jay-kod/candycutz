import { describe, expect, it, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import AdminGalleryCategoriesPage from '../AdminGalleryCategoriesPage.vue';
import { catalogueApi } from '@/features/catalogue/api';

const toastSuccess = vi.fn();
const toastError = vi.fn();
const confirmMock = vi.fn();
const invalidateMock = vi.fn();

vi.mock('@/features/catalogue/api', () => ({
  catalogueApi: {
    getGalleryCategories: vi.fn(),
    createGalleryCategory: vi.fn(),
    updateGalleryCategory: vi.fn(),
    deleteGalleryCategory: vi.fn(),
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({ success: toastSuccess, error: toastError }),
}));

vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({ confirm: confirmMock }),
}));

vi.mock('@/core/stores/publicContent.store', () => ({
  usePublicContentStore: () => ({ invalidate: invalidateMock }),
}));

describe('AdminGalleryCategoriesPage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    window.scrollTo = vi.fn();
    catalogueApi.getGalleryCategories.mockResolvedValue({
      data: {
        data: [
          {
            id: 1,
            name: 'Haircuts',
            slug: 'haircut',
            icon: 'scissors',
            description: 'Precision cuts',
            display_order: 1,
            items_count: 3,
          },
        ],
      },
    });
  });

  it('loads categories, creates a new gallery category with selected icon, and invalidates cache', async () => {
    catalogueApi.createGalleryCategory.mockResolvedValue({ data: { data: { id: 2 } } });

    const wrapper = mount(AdminGalleryCategoriesPage, {
      global: {
        stubs: {
          AdminLayout: { template: '<div><slot /></div>' },
          RouterLink: { template: '<a><slot /></a>' },
          Teleport: true,
        },
      },
    });

    await flushPromises();

    expect(wrapper.text()).toContain('Haircuts');
    expect(wrapper.text()).toContain('3 photos');

    await wrapper.find('input[placeholder*="Haircuts"]').setValue('Beard Art');
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(catalogueApi.createGalleryCategory).toHaveBeenCalledWith(
      expect.objectContaining({
        name: 'Beard Art',
        slug: 'beard-art',
        icon: 'camera',
      })
    );
    expect(invalidateMock).toHaveBeenCalledWith('galleryCategories');
    expect(invalidateMock).toHaveBeenCalledWith('gallery');
    expect(toastSuccess).toHaveBeenCalled();
  });

  it('edits and deletes a gallery category', async () => {
    catalogueApi.updateGalleryCategory.mockResolvedValue({ data: { data: { id: 1 } } });
    catalogueApi.deleteGalleryCategory.mockResolvedValue({ data: { data: null } });
    confirmMock.mockResolvedValue(true);

    const wrapper = mount(AdminGalleryCategoriesPage, {
      global: {
        stubs: {
          AdminLayout: { template: '<div><slot /></div>' },
          RouterLink: { template: '<a><slot /></a>' },
          Teleport: true,
        },
      },
    });

    await flushPromises();

    // Click Edit
    const editBtn = wrapper.findAll('button').find((b) => b.text().trim() === 'Edit');
    await editBtn.trigger('click');
    await wrapper.find('input[placeholder*="Haircuts"]').setValue('Signature Haircuts');
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(catalogueApi.updateGalleryCategory).toHaveBeenCalledWith(
      1,
      expect.objectContaining({
        name: 'Signature Haircuts',
        slug: 'signature-haircuts',
      })
    );

    // Click Delete
    const delBtn = wrapper.findAll('button').find((b) => b.text().trim() === 'Delete');
    await delBtn.trigger('click');
    await flushPromises();

    expect(confirmMock).toHaveBeenCalled();
    expect(catalogueApi.deleteGalleryCategory).toHaveBeenCalledWith(1);
  });
});
