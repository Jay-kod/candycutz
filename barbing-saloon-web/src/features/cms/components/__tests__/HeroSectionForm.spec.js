import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import HeroSectionForm from '../HeroSectionForm.vue';
import OverlayPresetsManager from '../OverlayPresetsManager.vue';

describe('HeroSectionForm', () => {
  const createWrapper = (settingsOverrides = {}) => {
    return mount(HeroSectionForm, {
      props: {
        settings: {
          hero_title: 'Title',
          hero_subtitle: 'Subtitle',
          hero_btn1_text: 'Button 1',
          hero_btn2_text: 'Button 2',
          hero_image_opacity: 22,
          hero_overlay_opacity: 45,
          hero_overlay_color: '#D4AF37',
          hero_overlay_presets: JSON.stringify([
            { label: 'Gold', value: '#D4AF37' },
            { label: 'Sapphire', value: '#2563EB' }
          ]),
          ...settingsOverrides
        },
        getStorageUrl: (p) => `/storage/${p}`
      }
    });
  };

  it('renders hero overlay styling and preset buttons', () => {
    const wrapper = createWrapper();
    expect(wrapper.text()).toContain('Hero Overlay Styling');
    expect(wrapper.text()).toContain('Gold');
    expect(wrapper.text()).toContain('Sapphire');
    expect(wrapper.text()).toContain('Edit Presets');
  });

  it('selects a preset color when clicked', async () => {
    const settings = {
      hero_title: 'Title',
      hero_subtitle: 'Subtitle',
      hero_overlay_color: '#D4AF37',
      hero_overlay_presets: JSON.stringify([
        { label: 'Gold', value: '#D4AF37' },
        { label: 'Sapphire', value: '#2563EB' }
      ])
    };
    const wrapper = mount(HeroSectionForm, {
      props: {
        settings,
        getStorageUrl: (p) => `/storage/${p}`
      }
    });

    const sapphireBtn = wrapper.findAll('button').find(b => b.text().includes('Sapphire'));
    expect(sapphireBtn).toBeDefined();
    await sapphireBtn.trigger('click');

    expect(settings.hero_overlay_color).toBe('#2563EB');
  });

  it('toggles OverlayPresetsManager when Edit Presets button is clicked', async () => {
    const wrapper = createWrapper();
    expect(wrapper.findComponent(OverlayPresetsManager).exists()).toBe(false);

    const editBtn = wrapper.findAll('button').find(b => b.text().includes('Edit Presets'));
    expect(editBtn).toBeDefined();
    await editBtn.trigger('click');

    expect(wrapper.findComponent(OverlayPresetsManager).exists()).toBe(true);
    expect(wrapper.text()).toContain('Done');

    // Click Done to close
    const doneBtn = wrapper.findAll('button').find(b => b.text() === 'Done');
    await doneBtn.trigger('click');
    expect(wrapper.findComponent(OverlayPresetsManager).exists()).toBe(false);
  });
});
