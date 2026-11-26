import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import axios from 'axios';
import ConnectionActionNotice from '../ConnectionActionNotice.vue';
import { useConnectionStatus } from '../../composables/useConnectionStatus';

vi.mock('axios');

describe('ConnectionActionNotice', () => {
  let wrapper = null;

  beforeEach(() => {
    vi.clearAllMocks();
    useConnectionStatus().resetState();
  });

  afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    useConnectionStatus().resetState();
  });

  it('shows a neutral inline notice without technical details after an action failure', () => {
    useConnectionStatus().recordActionFailure();
    wrapper = mount(ConnectionActionNotice);

    expect(wrapper.find('[role="status"]').exists()).toBe(true);
    expect(wrapper.text()).toContain("We couldn't confirm that request.");
    expect(wrapper.text()).not.toContain('database');
    expect(wrapper.text()).not.toContain('API');
    expect(wrapper.text()).not.toContain('8000');
  });

  it('checks service health without automatically repeating the failed request', async () => {
    useConnectionStatus().recordActionFailure();
    axios.get.mockResolvedValueOnce({
      status: 200,
      data: {
        success: true,
        data: { services: { database: 'connected' } },
      },
    });
    wrapper = mount(ConnectionActionNotice);

    await wrapper.find('button').trigger('click');

    expect(axios.get).toHaveBeenCalledOnce();
    expect(wrapper.text()).toContain('Requests are never repeated automatically.');
  });

  it('can be dismissed', async () => {
    useConnectionStatus().recordActionFailure();
    wrapper = mount(ConnectionActionNotice);

    await wrapper.find('button[aria-label="Dismiss connection notice"]').trigger('click');

    expect(wrapper.find('[role="status"]').exists()).toBe(false);
  });
});
