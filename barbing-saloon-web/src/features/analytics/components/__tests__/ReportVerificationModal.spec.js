import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportVerificationModal from '../ReportVerificationModal.vue';

describe('ReportVerificationModal Component', () => {
  const mockRecord = {
    report_id: 'REF-CCZ-20261031-VERIFY1',
    action: 'report_pdf_download',
    action_label: 'PDF Document Download',
    range: '30d',
    range_label: '30 Days',
    title: 'Executive Operations Dossier',
    user_name: 'Administrator',
    user_role: 'admin',
    ip_address: '10.0.0.1',
    checksum: 'SHA256:1122334455667788',
    created_at: '2026-10-31T12:00:00Z',
    formatted_date: 'Oct 31, 2026 • 12:00 PM',
    verified: true
  };

  it('renders certificate details, checksum, and issuer metadata when show is true', () => {
    const wrapper = mount(ReportVerificationModal, {
      props: {
        show: true,
        record: mockRecord
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });

    expect(wrapper.text()).toContain('Audit Verification Certificate');
    expect(wrapper.text()).toContain('REF-CCZ-20261031-VERIFY1');
    expect(wrapper.text()).toContain('SHA256:1122334455667788');
    expect(wrapper.text()).toContain('Administrator');
    expect(wrapper.text()).toContain('10.0.0.1');
    expect(wrapper.text()).toContain('CandyCutz Luxury Grooming Flagship');
    expect(wrapper.text()).toContain('GENUINE');
  });

  it('emits close event when close button is clicked', async () => {
    const wrapper = mount(ReportVerificationModal, {
      props: {
        show: true,
        record: mockRecord
      },
      global: {
        stubs: {
          teleport: true
        }
      }
    });

    const closeBtn = wrapper.findAll('button').find(b => b.text().includes('Close'));
    expect(closeBtn).toBeDefined();
    await closeBtn.trigger('click');

    expect(wrapper.emitted('close')).toBeTruthy();
  });
});

