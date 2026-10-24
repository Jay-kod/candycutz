import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CmsHomeStats from '../CmsHomeStats.vue';

describe('CmsHomeStats.vue', () => {
  it('renders computed stats from provided settings', () => {
    const settings = {
      hero_title: 'Master Cuts',
      hero_subtitle: 'Sharper standard.',
      hero_btn1_text: 'Book VIP',
      hero_btn2_text: 'Our Story',
      hero_image_opacity: 35,
      stats_years: '12+',
      stats_barbers: '8+',
      stats_services: '25+',
      stats_clients: '10k+',
      about_teaser_title: 'Legacy',
      about_teaser_subtitle: 'Artistry in Grooming',
      portal_teaser_title: 'VIP Access'
    };

    const wrapper = mount(CmsHomeStats, {
      props: {
        settings,
        currentHeroImage: 'images/hero.jpg',
        currentAboutImage: 'images/about.jpg'
      }
    });

    const text = wrapper.text();
    expect(text).toContain('35%');
    expect(text).toContain('Book VIP • Our Story');
    expect(text).toContain('12+ yrs • 8+ barbers • 10k+ clients');
    expect(text).toContain('Artistry in Grooming');
    expect(text).toContain('VIP Access');
    expect(text).toContain('Custom Asset');
  });

  it('provides safe fallbacks when settings are empty or undefined', () => {
    const wrapper = mount(CmsHomeStats, {
      props: {
        settings: {},
        currentHeroImage: null,
        currentAboutImage: null
      }
    });

    const text = wrapper.text();
    expect(text).toContain('22%');
    expect(text).toContain('Default Asset');
    expect(text).toContain('10+ yrs • 5+ barbers • 5k+ clients');
    expect(text).toContain('Philosophy');
    expect(text).toContain('Customer Portal');
  });
});
