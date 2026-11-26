import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import axios from 'axios';
import SystemConnectionBanner from '../SystemConnectionBanner.vue';
import { useConnectionStatus } from '../../composables/useConnectionStatus';

vi.mock('axios');

const authStoreState = vi.hoisted(() => ({ store: { isAdmin: true, isSuperAdmin: false } }));
vi.mock('@/modules/auth/store/auth.store', () => ({
  useAuthStore: () => authStoreState.store,
}));

describe('SystemConnectionBanner Component', () => {
  let wrapper = null;

  beforeEach(() => {
    authStoreState.store = { isAdmin: true, isSuperAdmin: false };
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

  it('does not render banner elements when connected', () => {
    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    const alert = wrapper.find('[role="alert"]');
    expect(alert.exists()).toBe(false);
  });

  it('renders disconnected alert banner with database status and retry button', () => {
    const { markDisconnected } = useConnectionStatus();
    markDisconnected('database', 'MySQL connection lost');

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    const alert = wrapper.find('[role="alert"]');
    expect(alert.exists()).toBe(true);
    expect(wrapper.text()).toContain('DB INACTIVE');
    expect(wrapper.text()).toContain('MySQL connection lost');
    expect(wrapper.text()).toContain('Retry Now');
  });

  it('renders server unreachable banner when disconnectType is server', () => {
    const { markDisconnected } = useConnectionStatus();
    markDisconnected('server', 'API gateway offline');

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    expect(wrapper.text()).toContain('SERVER OFFLINE');
    expect(wrapper.text()).toContain('API gateway offline');
  });

  it.each(['customer', 'barber'])('hides technical diagnostics from %s users', (role) => {
    authStoreState.store = { isAdmin: false, isSuperAdmin: false, role };
    useConnectionStatus().markDisconnected('server', 'API gateway offline');

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    expect(wrapper.text()).not.toContain('API gateway offline');
    expect(wrapper.text()).not.toContain('SERVER OFFLINE');
  });

  it('triggers retry probe when retry button is clicked', async () => {
    const { markDisconnected } = useConnectionStatus();
    markDisconnected('database');

    axios.get.mockResolvedValueOnce({
      status: 200,
      data: {
        success: true,
        data: {
          status: 'healthy',
          services: { database: 'connected' },
        },
      },
    });

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    const retryBtn = wrapper.find('button[title*="Probe MySQL"]');
    expect(retryBtn.exists()).toBe(true);

    await retryBtn.trigger('click');
    expect(axios.get).toHaveBeenCalled();
  });

  it('renders minimized pill when isMinimized is true', () => {
    const { markDisconnected, minimize } = useConnectionStatus();
    markDisconnected('database');
    minimize();

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    const alert = wrapper.find('[role="alert"]');
    expect(alert.exists()).toBe(false);

    const minimizedPill = wrapper.find('button[title*="Database is offline"]');
    expect(minimizedPill.exists()).toBe(true);
    expect(minimizedPill.text()).toContain('DB Inactive');
  });

  it('labels a minimized server failure separately from a database outage', () => {
    const { markDisconnected, minimize } = useConnectionStatus();
    markDisconnected('server', 'API gateway offline');
    minimize();

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    const minimizedPill = wrapper.find('button[title*="Backend server is unreachable"]');
    expect(minimizedPill.exists()).toBe(true);
    expect(minimizedPill.text()).toContain('Server Offline');
    expect(minimizedPill.text()).not.toContain('DB Inactive');
  });

  it('renders active recovery banner when markConnected is called after disconnection', () => {
    const { markDisconnected, markConnected } = useConnectionStatus();
    markDisconnected('database');
    markConnected('Database is active and connected!');

    wrapper = mount(SystemConnectionBanner, {
      global: {
        stubs: {
          teleport: true,
          transition: false,
        },
      },
    });

    const alert = wrapper.find('[role="alert"]');
    expect(alert.exists()).toBe(true);
    expect(wrapper.text()).toContain('DB ACTIVE');
    expect(wrapper.text()).toContain('Database is active and connected!');
  });
});
