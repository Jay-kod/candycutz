import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import TermsStats from '../TermsStats.vue';

describe('TermsStats.vue', () => {
  it('renders computed terms title, clauses count, word count, reading time, and operations scope', () => {
    const content = {
      terms_title: 'Studio Client Master Agreement'
    };

    const sections = [
      { heading: '1. Agreement to Terms', body: 'Service agreements and mutual commitments.' },
      { heading: '2. Booking Rules', body: 'Appointments and cancellations require 24 hours notice.' },
      { heading: '3. Salon Safety', body: 'Sanitation, hygiene, and respectful guest conduct.' }
    ];

    const wrapper = mount(TermsStats, {
      props: {
        content,
        sections,
        totalWords: 680,
        estimatedReadTime: 4,
        hasBookingPolicy: true,
        hasSafetyPolicy: true
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Studio Client Master Agreement');
    expect(text).toContain('Public Legal Contract Active');
    expect(text).toContain('3');
    expect(text).toContain('articles');
    expect(text).toContain('680');
    expect(text).toContain('words');
    expect(text).toContain('~4 min reading time');
    expect(text).toContain('Comprehensive');
    expect(text).toContain('Full Operations Scope');
    expect(text).toContain('Cancellations & Studio Rules');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(TermsStats, {
      props: {
        content: {},
        sections: [],
        totalWords: 0,
        estimatedReadTime: 1,
        hasBookingPolicy: false,
        hasSafetyPolicy: false
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Terms of Service');
    expect(text).toContain('0');
    expect(text).toContain('articles');
    expect(text).toContain('0');
    expect(text).toContain('words');
    expect(text).toContain('~1 min reading time');
    expect(text).toContain('Concise');
    expect(text).toContain('Basic Legal Scope');
    expect(text).toContain('Add Booking Clauses');
  });
});

