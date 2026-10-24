import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PrivacyStats from '../PrivacyStats.vue';

describe('PrivacyStats.vue', () => {
  it('renders computed policy title, clauses count, word count, reading time, and store compliance', () => {
    const content = {
      privacy_title: 'Client Data Governance Codex'
    };

    const sections = [
      { heading: '1. Introduction', body: 'This is the intro section.' },
      { heading: '2. Data Collected', body: 'Personal details and payment records.' },
      { heading: '3. Erasure Compliance', body: 'Full account deactivation in compliance with guidelines.' }
    ];

    const wrapper = mount(PrivacyStats, {
      props: {
        content,
        sections,
        totalWords: 850,
        estimatedReadTime: 5,
        hasDeactivationClause: true
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Client Data Governance Codex');
    expect(text).toContain('Public Legal Notice Active');
    expect(text).toContain('3');
    expect(text).toContain('articles');
    expect(text).toContain('850');
    expect(text).toContain('words');
    expect(text).toContain('~5 min reading time');
    expect(text).toContain('Comprehensive');
    expect(text).toContain('App Store Ready');
    expect(text).toContain('Apple 5.1.1(v) & Play Store');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(PrivacyStats, {
      props: {
        content: {},
        sections: [],
        totalWords: 0,
        estimatedReadTime: 1,
        hasDeactivationClause: false
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Privacy Policy');
    expect(text).toContain('0');
    expect(text).toContain('articles');
    expect(text).toContain('0');
    expect(text).toContain('words');
    expect(text).toContain('~1 min reading time');
    expect(text).toContain('Concise');
    expect(text).toContain('Review Needed');
    expect(text).toContain('Add Account Erasure');
  });
});

