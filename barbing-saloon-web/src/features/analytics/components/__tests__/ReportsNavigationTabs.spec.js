import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsNavigationTabs from '../ReportsNavigationTabs.vue';

describe('ReportsNavigationTabs Component', () => {
  it('renders both Executive Dossier and Export & Audit History tabs with correct state', () => {
    const wrapper = mount(ReportsNavigationTabs, {
      props: {
        activeTab: 'dossier',
        historyCount: 7,
        lastRefreshed: '14:30'
      }
    });

    expect(wrapper.text()).toContain('Executive Dossier');
    expect(wrapper.text()).toContain('Export & Audit History');
    expect(wrapper.text()).toContain('7');
    expect(wrapper.text()).toContain('Reconciled 14:30');

    const dossierBtn = wrapper.find('#tab-btn-dossier');
    expect(dossierBtn.classes()).toContain('bg-gradient-to-r');
  });

  it('emits update:activeTab event when clicked', async () => {
    const wrapper = mount(ReportsNavigationTabs, {
      props: {
        activeTab: 'dossier',
        historyCount: 3
      }
    });

    const historyBtn = wrapper.find('#tab-btn-history');
    await historyBtn.trigger('click');

    expect(wrapper.emitted('update:activeTab')).toBeTruthy();
    expect(wrapper.emitted('update:activeTab')[0]).toEqual(['history']);
  });
});

