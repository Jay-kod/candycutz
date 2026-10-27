import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AccountDeletionStats from '../AccountDeletionStats.vue';

describe('AccountDeletionStats.vue', () => {
  it('renders computed erasure title, pathways count, retention notice word count, and store compliance', () => {
    const content = {
      account_deletion_title: 'Purge Your Profile & Data'
    };

    const wrapper = mount(AccountDeletionStats, {
      props: {
        content,
        retentionNoticeWordCount: 85,
        isCompliantWithAppStore: true
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Purge Your Profile & Data');
    expect(text).toContain('Public Request Page Active');
    expect(text).toContain('2');
    expect(text).toContain('channels');
    expect(text).toContain('85');
    expect(text).toContain('words');
    expect(text).toContain('Configured');
    expect(text).toContain('App Store Ready');
    expect(text).toContain('Apple 5.1.1(v) & Google Play');
  });

  it('provides safe fallbacks for empty or undefined content', () => {
    const wrapper = mount(AccountDeletionStats, {
      props: {
        content: {},
        retentionNoticeWordCount: 0,
        isCompliantWithAppStore: false
      }
    });

    const text = wrapper.text();
    expect(text).toContain('Delete Account');
    expect(text).toContain('0');
    expect(text).toContain('words');
    expect(text).toContain('Brief');
    expect(text).toContain('Review Needed');
    expect(text).toContain('Add Erasure Terms');
  });
});

