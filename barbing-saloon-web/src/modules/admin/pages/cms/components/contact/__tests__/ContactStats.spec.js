import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ContactStats from '../ContactStats.vue';

describe('ContactStats.vue', () => {
  it('renders computed contact headline, business channels count, social profiles count, and map status', () => {
    const content = {
      contact_title: 'Reach Our Studio',
      contact_receiver_email: 'inbox@candycutz.com',
      contact_phone: '+234 812 345 6789',
      contact_email: 'support@candycutz.com',
      contact_address: 'Plot 4, Keffi Road',
      contact_hours: 'Mon - Sun: 8am - 9pm',
      contact_map_link: 'https://maps.app.goo.gl/xyz'
    };

    const wrapper = mount(ContactStats, {
      props: {
        content,
        activeSocialCount: 5,
        extractedMapSrc: 'https://www.google.com/maps/embed?pb=123'
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Reach Our Studio');
    expect(text).toContain('Form Inbox Configured');
    expect(text).toContain('4');
    expect(text).toContain('/ 4 active');
    expect(text).toContain('Phone, Email, Address, Hours');
    expect(text).toContain('5');
    expect(text).toContain('connected');
    expect(text).toContain('5/7');
    expect(text).toContain('Interactive Map');
    expect(text).toContain('Directions Link Active');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(ContactStats, {
      props: {
        content: {},
        activeSocialCount: 0,
        extractedMapSrc: ''
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Talk to CandyCutz');
    expect(text).toContain('Default Admin Inbox');
    expect(text).toContain('0');
    expect(text).toContain('No details added');
    expect(text).toContain('No profiles set');
    expect(text).toContain('No Map Code');
    expect(text).toContain('Pending Embed');
  });
});
