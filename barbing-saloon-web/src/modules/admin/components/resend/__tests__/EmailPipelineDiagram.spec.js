import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import EmailPipelineDiagram from '../EmailPipelineDiagram.vue';

describe('EmailPipelineDiagram.vue', () => {
  it('renders transactional email architecture flow by default', () => {
    const wrapper = mount(EmailPipelineDiagram);

    expect(wrapper.text()).toContain('Email & OTP Dispatch Pipelines');
    expect(wrapper.text()).toContain('Transactional Flow');
    expect(wrapper.text()).toContain('OTP Verification Flow');
    expect(wrapper.text()).toContain('Transactional Email Delivery Pipeline');
    expect(wrapper.text()).toContain('Client Event Trigger');
    expect(wrapper.text()).toContain('Transactional Integrity Lock');
    expect(wrapper.text()).toContain('Background Job Queuing');
    expect(wrapper.text()).toContain('Luxury HTML Template Compilation');
    expect(wrapper.text()).toContain('Resend API Dispatch');
    expect(wrapper.text()).toContain('Delivered to Customer VIP Inbox');
  });

  it('switches to OTP verification flow when clicked', async () => {
    const wrapper = mount(EmailPipelineDiagram);

    const otpTab = wrapper.findAll('button').find(b => b.text().includes('OTP Verification Flow'));
    expect(otpTab).toBeDefined();
    await otpTab.trigger('click');

    expect(wrapper.text()).toContain('High-Security OTP Verification Pipeline');
    expect(wrapper.text()).toContain('Verification Request');
    expect(wrapper.text()).toContain('Rate-Limiter Protection Gate');
    expect(wrapper.text()).toContain('Cryptographic 6-Digit OTP Generation');
    expect(wrapper.text()).toContain('Atomic Redis Cache Storage');
    expect(wrapper.text()).toContain('Immediate Priority Resend Dispatch');
    expect(wrapper.text()).toContain('Dual Verification Gate');
  });
});
