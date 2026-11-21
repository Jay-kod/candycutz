import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import axios from 'axios';
import DatabaseStatusBadge from '../DatabaseStatusBadge.vue';
import { useConnectionStatus } from '../../composables/useConnectionStatus';

vi.mock('axios');

describe('DatabaseStatusBadge Component', () => {
  let wrapper = null;

  beforeEach(() => {
    vi.clearAllMocks();
    useConnectionStatus().resetState();
  });

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount();
      wrapper = null;
    }
    useConnectionStatus().resetState();
  });

  it('renders DB: Active badge when connected', () => {
    wrapper = mount(DatabaseStatusBadge);

    expect(wrapper.text()).toContain('DB: Active');
    expect(wrapper.classes()).toContain('border-emerald-500/30');
    expect(wrapper.attributes('title')).toContain('Database is active');
  });

  it('renders DB: Offline badge with red styling when disconnected', () => {
    const { markDisconnected } = useConnectionStatus();
    markDisconnected('database');

    wrapper = mount(DatabaseStatusBadge);

    expect(wrapper.text()).toContain('DB: Offline');
    expect(wrapper.classes()).toContain('border-rose-500/40');
    expect(wrapper.attributes('title')).toContain('Database is offline');
  });

  it('triggers health probe on click when disconnected', async () => {
    const { markDisconnected } = useConnectionStatus();
    markDisconnected('database');

    axios.get.mockResolvedValueOnce({
      status: 200,
      data: {
        success: true,
        data: { services: { database: 'connected' } },
      },
    });

    wrapper = mount(DatabaseStatusBadge);
    await wrapper.trigger('click');

    expect(axios.get).toHaveBeenCalled();
  });

  it('re-verifies health on click when already connected', async () => {
    axios.get.mockResolvedValueOnce({
      status: 200,
      data: {
        success: true,
        data: { services: { database: 'connected' } },
      },
    });

    wrapper = mount(DatabaseStatusBadge);
    await wrapper.trigger('click');

    expect(axios.get).toHaveBeenCalled();
  });
});

