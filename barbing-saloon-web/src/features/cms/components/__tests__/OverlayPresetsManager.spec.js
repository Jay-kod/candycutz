import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import OverlayPresetsManager from '../OverlayPresetsManager.vue';
import { DEFAULT_OVERLAY_PRESETS } from '@/core/utils/heroOverlay';

describe('OverlayPresetsManager', () => {
  it('renders default presets when mounted with empty modelValue', () => {
    const wrapper = mount(OverlayPresetsManager, {
      props: {
        modelValue: [],
        activeColor: '#D4AF37'
      }
    });

    expect(wrapper.text()).toContain('Preset Palette Manager');
    const inputs = wrapper.findAll('input[type="text"]');
    expect(inputs.length).toBeGreaterThanOrEqual(14); // label + hex per preset
    expect(inputs[0].element.value).toBe('Luxury Gold');
    expect(inputs[1].element.value).toBe('#D4AF37');
  });

  it('renders custom provided presets', () => {
    const custom = [
      { label: 'Neon Cyber', value: '#00FFCC' },
      { label: 'Midnight Void', value: '#050505' }
    ];
    const wrapper = mount(OverlayPresetsManager, {
      props: {
        modelValue: custom,
        activeColor: '#00FFCC'
      }
    });

    const inputs = wrapper.findAll('input[type="text"]');
    expect(inputs[0].element.value).toBe('Neon Cyber');
    expect(inputs[2].element.value).toBe('Midnight Void');
    expect(wrapper.text()).toContain('Active');
  });

  it('adds a new preset when Add Preset button is clicked', async () => {
    const wrapper = mount(OverlayPresetsManager, {
      props: {
        modelValue: [{ label: 'Tone 1', value: '#D4AF37' }],
        activeColor: '#D4AF37'
      }
    });

    const addBtn = wrapper.findAll('button').find(b => b.text().includes('Add Preset Color'));
    expect(addBtn).toBeDefined();
    await addBtn.trigger('click');

    const emitted = wrapper.emitted('update:modelValue');
    expect(emitted).toBeTruthy();
    const latest = emitted[emitted.length - 1][0];
    expect(latest.length).toBe(2);
    expect(latest[1].label).toContain('Custom Tone');
  });

  it('removes a preset when delete button is clicked and length > 1', async () => {
    const wrapper = mount(OverlayPresetsManager, {
      props: {
        modelValue: [
          { label: 'Color A', value: '#AAAAAA' },
          { label: 'Color B', value: '#BBBBBB' }
        ],
        activeColor: '#AAAAAA'
      }
    });

    const trashBtns = wrapper.findAll('button[title="Delete this preset"]');
    expect(trashBtns.length).toBe(2);
    await trashBtns[0].trigger('click');

    const emitted = wrapper.emitted('update:modelValue');
    expect(emitted).toBeTruthy();
    const latest = emitted[emitted.length - 1][0];
    expect(latest.length).toBe(1);
    expect(latest[0].label).toBe('Color B');
  });

  it('disables delete when only 1 preset remains', () => {
    const wrapper = mount(OverlayPresetsManager, {
      props: {
        modelValue: [{ label: 'Only One', value: '#D4AF37' }],
        activeColor: '#D4AF37'
      }
    });

    const deleteBtn = wrapper.find('button[disabled]');
    expect(deleteBtn.exists()).toBe(true);
  });

  it('resets to factory defaults when Reset Defaults button is clicked', async () => {
    const wrapper = mount(OverlayPresetsManager, {
      props: {
        modelValue: [{ label: 'Custom Only', value: '#123456' }],
        activeColor: '#123456'
      }
    });

    const resetBtn = wrapper.findAll('button').find(b => b.text().includes('Reset Defaults'));
    await resetBtn.trigger('click');

    const emitted = wrapper.emitted('update:modelValue');
    expect(emitted).toBeTruthy();
    const latest = emitted[emitted.length - 1][0];
    expect(latest.length).toBe(DEFAULT_OVERLAY_PRESETS.length);
  });
});
