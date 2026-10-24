import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AboutStats from '../AboutStats.vue';

describe('AboutStats.vue', () => {
  it('renders computed word counts, character counts, and subtitles', () => {
    const content = {
      about_shop_title: 'Our Heritage',
      about_shop_subtitle: 'A Sharper Standard',
      about_shop_text_1: 'Welcome to CandyCutz grooming lounge.',
      about_shop_text_2: 'We offer premier bespoke haircuts and beard detailing.',
      about_team_title: 'Master Barbers Squad'
    };

    const wrapper = mount(AboutStats, {
      props: {
        content,
        currentShopImage: 'images/interior.jpg'
      },
      global: {
        stubs: ['router-link']
      }
    });

    const text = wrapper.text();
    expect(text).toContain('A Sharper Standard');
    expect(text).toContain('Our Heritage');
    expect(text).toContain('Custom Asset');
    expect(text).toContain('Master Barbers Squad');
    
    // Words: 5 + 8 = 13 words
    expect(text).toContain('13');
    expect(text).toContain('words');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(AboutStats, {
      props: {
        content: {},
        currentShopImage: null
      },
      global: {
        stubs: ['router-link']
      }
    });

    const text = wrapper.text();
    expect(text).toContain('A Sharper Standard');
    expect(text).toContain('About Us');
    expect(text).toContain('Default Asset');
    expect(text).toContain('0');
    expect(text).toContain('Meet the team');
  });
});
