import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ServiceGallerySection from '../ServiceGallerySection.vue';

describe('ServiceGallerySection.vue', () => {
  const sampleImages = [
    'http://localhost/images/services/service-1.png',
    'http://localhost/images/gallery/fade-1.png',
    'http://localhost/images/gallery/beard-1.png',
  ];

  it('renders all three images in thumbnails and displays active image', async () => {
    const wrapper = mount(ServiceGallerySection, {
      props: {
        images: sampleImages,
        serviceName: 'The Executive Royal Fade',
        activeImage: 0,
      },
    });

    // Check main carousel image
    const mainImg = wrapper.find('.aspect-\\[16\\/10\\] img, .md\\:aspect-\\[2\\/1\\] img');
    expect(mainImg.exists()).toBe(true);
    expect(mainImg.attributes('src')).toBe(sampleImages[0]);

    // Check that 3 thumbnail buttons exist and have image sources
    const thumbnailImgs = wrapper.findAll('.grid-cols-3 img');
    expect(thumbnailImgs).toHaveLength(3);
    expect(thumbnailImgs[0].attributes('src')).toBe(sampleImages[0]);
    expect(thumbnailImgs[1].attributes('src')).toBe(sampleImages[1]);
    expect(thumbnailImgs[2].attributes('src')).toBe(sampleImages[2]);

    // Clicking second thumbnail emits update:activeImage with 1
    const thumbnailButtons = wrapper.findAll('.grid-cols-3 button');
    await thumbnailButtons[1].trigger('click');
    expect(wrapper.emitted('update:activeImage')).toBeTruthy();
    expect(wrapper.emitted('update:activeImage')[0]).toEqual([1]);
  });

  it('handles empty images gracefully with placeholder', () => {
    const wrapper = mount(ServiceGallerySection, {
      props: {
        images: [],
        serviceName: 'Empty Service',
        activeImage: 0,
      },
    });

    expect(wrapper.text()).toContain('No images available');
  });
});
