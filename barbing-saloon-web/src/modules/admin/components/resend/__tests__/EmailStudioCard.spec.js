import { describe, it, expect } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import EmailStudioCard from '../EmailStudioCard.vue';

describe('EmailStudioCard.vue', () => {
  const defaultProps = {
    form: {
      mail_from: 'concierge@candycutz.com',
      mail_from_name: 'CandyCutz VIP'
    },
    isConfigured: true,
    testing: false
  };

  it('renders all 5 templates and initial booking confirmation view', () => {
    const wrapper = mount(EmailStudioCard, {
      props: defaultProps
    });

    expect(wrapper.text()).toContain('Select Template To Edit');
    expect(wrapper.text()).toContain('Confirmation');
    expect(wrapper.text()).toContain('24h Reminder');
    expect(wrapper.text()).toContain('Payment Receipt');
    expect(wrapper.text()).toContain('Cancellation');
    expect(wrapper.text()).toContain('OTP & Verification');
    expect(wrapper.text()).toContain('C A N D Y C U T Z');
    expect(wrapper.text()).toContain('Appointment Confirmed');
  });

  it('switches to OTP template and shows 6-digit verification code mockup', async () => {
    const wrapper = mount(EmailStudioCard, {
      props: defaultProps
    });

    const otpBtn = wrapper.findAll('button').find(b => b.text().includes('OTP & Verification'));
    expect(otpBtn).toBeDefined();
    await otpBtn.trigger('click');

    expect(wrapper.text()).toContain('OTP & VERIFICATION');
    expect(wrapper.text()).toContain('Verify Your Email Address');
    expect(wrapper.text()).toContain('6-Digit Verification Code');
    expect(wrapper.text()).toContain('849201');
    expect(wrapper.text()).toContain('Copy Code');
    expect(wrapper.text()).toContain('Expires in');
    expect(wrapper.text()).toContain('Verify Email Address Instantly');
    expect(wrapper.text()).toContain('Security Advisory');

    const copyBtn = wrapper.findAll('button').find(b => b.text().includes('Copy Code'));
    expect(copyBtn).toBeDefined();
    await copyBtn.trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('Copied 6-Digit Code!');
  });

  it('toggles between email mockup view and pictorial pipeline view', async () => {
    const wrapper = mount(EmailStudioCard, {
      props: defaultProps
    });

    const pipelineBtn = wrapper.findAll('button').find(b => b.text().includes('Pictorial Pipeline'));
    expect(pipelineBtn).toBeDefined();
    await pipelineBtn.trigger('click');

    expect(wrapper.text()).toContain('Email & OTP Dispatch Pipelines');
    expect(wrapper.text()).toContain('Transactional Email Delivery Pipeline');

    const mockupBtn = wrapper.findAll('button').find(b => b.text().includes('Live Customer View'));
    expect(mockupBtn).toBeDefined();
    await mockupBtn.trigger('click');

    expect(wrapper.text()).toContain('Live Customer View');
    expect(wrapper.find('.max-w-\\[620px\\]').exists()).toBe(true);
  });

  it('switches templates and updates preview card', async () => {
    const wrapper = mount(EmailStudioCard, {
      props: defaultProps
    });

    const reminderBtn = wrapper.findAll('button').find(b => b.text().includes('24h Reminder'));
    expect(reminderBtn).toBeDefined();
    await reminderBtn.trigger('click');

    expect(wrapper.text()).toContain('24-HOUR REMINDER');
    expect(wrapper.text()).toContain('Upcoming Tomorrow');

    const cancelBtn = wrapper.findAll('button').find(b => b.text().includes('Cancellation'));
    expect(cancelBtn).toBeDefined();
    await cancelBtn.trigger('click');

    expect(wrapper.text()).toContain('BOOKING CANCELLATION');
    expect(wrapper.text()).toContain('Appointment Cancelled');
  });

  it('toggles between desktop and mobile viewport sizes', async () => {
    const wrapper = mount(EmailStudioCard, {
      props: defaultProps
    });

    const mobileBtn = wrapper.findAll('button').find(b => b.text().includes('Mobile (380px)'));
    expect(mobileBtn).toBeDefined();
    await mobileBtn.trigger('click');

    expect(wrapper.find('.max-w-\\[380px\\]').exists()).toBe(true);

    const desktopBtn = wrapper.findAll('button').find(b => b.text().includes('Desktop (600px)'));
    expect(desktopBtn).toBeDefined();
    await desktopBtn.trigger('click');

    expect(wrapper.find('.max-w-\\[620px\\]').exists()).toBe(true);
  });

  it('emits test-dispatch when sending a preview email', async () => {
    const wrapper = mount(EmailStudioCard, {
      props: defaultProps
    });

    const emailInput = wrapper.find('input[type="email"][placeholder="admin@candycutz.com"]');
    expect(emailInput.exists()).toBe(true);
    await emailInput.setValue('test-client@example.com');

    const sendBtn = wrapper.findAll('button').find(b => b.text().includes('Send Test'));
    expect(sendBtn).toBeDefined();
    await sendBtn.trigger('click');

    expect(wrapper.emitted('test-dispatch')).toBeTruthy();
    expect(wrapper.emitted('test-dispatch')[0]).toEqual(['test-client@example.com']);
  });

  it('emits save event and synchronizes copy fields to form when clicking Save Template Copy', async () => {
    const form = {
      mail_from: 'concierge@candycutz.com',
      mail_from_name: 'CandyCutz VIP'
    };

    const wrapper = mount(EmailStudioCard, {
      props: {
        form,
        isConfigured: true,
        saving: false,
        testing: false
      }
    });

    const saveCopyBtn = wrapper.findAll('button').find(b => b.text().includes('Save Template Copy'));
    expect(saveCopyBtn).toBeDefined();
    await saveCopyBtn.trigger('click');

    expect(wrapper.emitted('save')).toBeTruthy();
    expect(form.mail_confirmed_subject).toBe('Booking Confirmed — CandyCutz Luxury Barbing');
    expect(form.mail_show_maps).toBe(true);
  });
});
