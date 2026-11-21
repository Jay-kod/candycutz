import { mount } from '@vue/test-utils';
import ProfilePersonalCard from '../ProfilePersonalCard.vue';

describe('ProfilePersonalCard.vue', () => {
  it('renders input fields with provided form values', () => {
    const form = {
      name: 'Obo Master Barber',
      email: 'obo@candycutz.com',
      phone: '+234 812 345 6789',
      instagram_url: 'obo_cuts',
      bio: 'Expert cuts and fades.',
      profile_image: null,
      cover_image: null,
    };

    const wrapper = mount(ProfilePersonalCard, {
      props: { form },
    });

    const nameInput = wrapper.find('[data-test="input-name"]');
    expect(nameInput.exists()).toBe(true);
    expect(nameInput.element.value).toBe('Obo Master Barber');

    const emailInput = wrapper.find('[data-test="input-email"]');
    expect(emailInput.exists()).toBe(true);
    expect(emailInput.element.value).toBe('obo@candycutz.com');
  });

  it('renders cover image and view preview buttons when images are provided', async () => {
    const form = {
      name: 'Obo Master Barber',
      email: 'obo@candycutz.com',
      profile_image: 'https://example.com/avatar.jpg',
      cover_image: 'https://example.com/cover.jpg',
    };

    const wrapper = mount(ProfilePersonalCard, {
      props: { form },
    });

    const coverImg = wrapper.find('[data-test="cover-image-preview"]');
    expect(coverImg.exists()).toBe(true);
    expect(coverImg.attributes('src')).toBe('https://example.com/cover.jpg');

    const viewCoverBtn = wrapper.find('[data-test="btn-view-cover-preview"]');
    expect(viewCoverBtn.exists()).toBe(true);

    const viewAvatarBtn = wrapper.find('[data-test="btn-view-avatar-preview"]');
    expect(viewAvatarBtn.exists()).toBe(true);

    // Click view cover opens preview modal
    await viewCoverBtn.trigger('click');
    const modal = wrapper.find('[data-test="dashboard-image-preview-modal"]');
    expect(modal.exists()).toBe(true);

    // Close preview modal
    const closeBtn = wrapper.find('[data-test="btn-close-preview"]');
    await closeBtn.trigger('click');
    expect(wrapper.find('[data-test="dashboard-image-preview-modal"]').exists()).toBe(false);
  });

  it('emits cover-selected event on file input change', async () => {
    const form = {
      name: 'Obo Master Barber',
      cover_image: null,
    };

    const wrapper = mount(ProfilePersonalCard, {
      props: { form },
    });

    const fileInput = wrapper.find('[data-test="input-cover-file"]');
    const file = new File(['cover-content'], 'cover.png', { type: 'image/png' });

    Object.defineProperty(fileInput.element, 'files', {
      value: [file],
    });

    await fileInput.trigger('change');
    expect(wrapper.emitted('cover-selected')).toBeTruthy();
    expect(wrapper.emitted('cover-selected')[0][0]).toBe(file);
  });
});

