import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import axios from 'axios';
import { useConnectionStatus } from '../useConnectionStatus';

vi.mock('axios');

describe('useConnectionStatus Composable', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    useConnectionStatus().resetState();
  });

  afterEach(() => {
    useConnectionStatus().resetState();
    vi.useRealTimers();
  });

  it('starts in connected state with banner hidden', () => {
    const { isConnected, isDisconnected, isRecovered, isBannerVisible, connectionState } = useConnectionStatus();

    expect(connectionState.value).toBe('connected');
    expect(isConnected.value).toBe(true);
    expect(isDisconnected.value).toBe(false);
    expect(isRecovered.value).toBe(false);
    expect(isBannerVisible.value).toBe(false);
  });

  it('transitions to disconnected state when markDisconnected is called', () => {
    const { isDisconnected, isBannerVisible, disconnectType, errorMessage, markDisconnected } = useConnectionStatus();

    markDisconnected('database', 'MySQL is offline');

    expect(isDisconnected.value).toBe(true);
    expect(isBannerVisible.value).toBe(true);
    expect(disconnectType.value).toBe('database');
    expect(errorMessage.value).toBe('MySQL is offline');
  });

  it('transitions from disconnected to recovered and auto-resets to connected', () => {
    vi.useFakeTimers();
    const {
      isDisconnected,
      isRecovered,
      isConnected,
      isBannerVisible,
      markDisconnected,
      markConnected,
      recoveryMessage,
    } = useConnectionStatus();

    markDisconnected('database');
    expect(isDisconnected.value).toBe(true);

    markConnected('Database restored!');
    expect(isDisconnected.value).toBe(false);
    expect(isRecovered.value).toBe(true);
    expect(isBannerVisible.value).toBe(true);
    expect(recoveryMessage.value).toBe('Database restored!');

    // Fast-forward recovery auto-dismiss timeout (4000ms)
    vi.advanceTimersByTime(4000);

    expect(isRecovered.value).toBe(false);
    expect(isConnected.value).toBe(true);
    expect(isBannerVisible.value).toBe(false);
  });

  it('supports minimizing and expanding the banner', () => {
    const { isMinimized, markDisconnected, minimize, expand } = useConnectionStatus();

    markDisconnected('database');
    expect(isMinimized.value).toBe(false);

    minimize();
    expect(isMinimized.value).toBe(true);

    expand();
    expect(isMinimized.value).toBe(false);
  });

  it('successfully recovers when checkHealth returns healthy database', async () => {
    const { isDisconnected, markDisconnected, checkHealth } = useConnectionStatus();

    markDisconnected('database');
    expect(isDisconnected.value).toBe(true);

    axios.get.mockResolvedValueOnce({
      status: 200,
      data: {
        success: true,
        data: {
          status: 'healthy',
          services: {
            database: 'connected',
          },
        },
      },
    });

    const result = await checkHealth();
    expect(result).toBe(true);
    expect(useConnectionStatus().isRecovered.value).toBe(true);
  });

  it('marks disconnected when checkHealth returns 503 degraded database', async () => {
    const { isDisconnected, disconnectType, checkHealth } = useConnectionStatus();

    axios.get.mockRejectedValueOnce({
      response: {
        status: 503,
        data: {
          success: true,
          data: {
            status: 'degraded',
            services: {
              database: 'disconnected',
            },
          },
        },
      },
    });

    const result = await checkHealth();
    expect(result).toBe(false);
    expect(isDisconnected.value).toBe(true);
    expect(disconnectType.value).toBe('database');
  });

  it('marks server offline when checkHealth encounters a network failure', async () => {
    const { isDisconnected, disconnectType, checkHealth } = useConnectionStatus();

    axios.get.mockRejectedValueOnce(new Error('Network Error'));

    const result = await checkHealth();
    expect(result).toBe(false);
    expect(isDisconnected.value).toBe(true);
    expect(disconnectType.value).toBe('server');
  });
});

