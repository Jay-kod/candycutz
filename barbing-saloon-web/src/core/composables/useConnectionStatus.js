import { ref, computed } from 'vue';
import axios from 'axios';
import { API_V1_BASE_URL } from '@/core/utils/url';

// Shared singleton state across all consumers
const connectionState = ref('connected'); // 'connected' | 'disconnected' | 'recovered'
const disconnectType = ref(null); // 'database' | 'server'
const errorMessage = ref('');
const recoveryMessage = ref('');
const isProbing = ref(false);
const isMinimized = ref(false);
const hasActionFailure = ref(false);
const lastCheckedAt = ref(null);
const latencyMs = ref(null);

let probeTimer = null;
let recoveryTimer = null;

export function useConnectionStatus() {
  const isConnected = computed(() => connectionState.value === 'connected');
  const isDisconnected = computed(() => connectionState.value === 'disconnected');
  const isRecovered = computed(() => connectionState.value === 'recovered');
  const isBannerVisible = computed(() => isDisconnected.value || isRecovered.value);

  function stopProbe() {
    if (probeTimer) {
      clearInterval(probeTimer);
      probeTimer = null;
    }
  }

  function startProbe() {
    if (probeTimer) return;
    probeTimer = setInterval(() => {
      checkHealth();
    }, 5000);
  }

  function markDisconnected(type = 'database', customMessage = null) {
    if (recoveryTimer) {
      clearTimeout(recoveryTimer);
      recoveryTimer = null;
    }

    disconnectType.value = type;
    errorMessage.value =
      customMessage ||
      (type === 'database'
        ? 'Database connection is inactive. MySQL service is offline or unreachable.'
        : 'Unable to reach the backend API server. Please check your connection.');

    if (connectionState.value !== 'disconnected') {
      connectionState.value = 'disconnected';
      isMinimized.value = false;
      startProbe();
    }
  }

  function reportNetworkFailure() {
    if (connectionState.value === 'disconnected') {
      return;
    }
    // Verify connection via health probe before alarming user with a false disconnection alert
    checkHealth();
  }

  function recordActionFailure() {
    hasActionFailure.value = true;
  }

  function clearActionFailure() {
    hasActionFailure.value = false;
  }

  function markConnected(customMessage = null) {
    if (connectionState.value === 'disconnected') {
      stopProbe();
      connectionState.value = 'recovered';
      recoveryMessage.value =
        customMessage ||
        (disconnectType.value === 'database'
          ? 'Database connection restored! MySQL is active and operational.'
          : 'Backend server connection restored!');

      if (recoveryTimer) clearTimeout(recoveryTimer);
      recoveryTimer = setTimeout(() => {
        connectionState.value = 'connected';
        errorMessage.value = '';
        recoveryMessage.value = '';
        disconnectType.value = null;
        isMinimized.value = false;
        recoveryTimer = null;
      }, 4000);
    } else if (connectionState.value !== 'recovered') {
      connectionState.value = 'connected';
      errorMessage.value = '';
      disconnectType.value = null;
    }
  }

  async function checkHealth() {
    if (isProbing.value) return false;
    isProbing.value = true;
    const start = Date.now();

    try {
      const response = await axios.get(`${API_V1_BASE_URL}/health`, {
        timeout: 8000,
        headers: {
          Accept: 'application/json',
          'X-Client': 'web',
        },
      });

      latencyMs.value = Date.now() - start;
      lastCheckedAt.value = new Date();

      const dbStatus = response.data?.data?.services?.database;
      if (response.status === 200 && dbStatus === 'connected') {
        markConnected();
        return true;
      } else {
        markDisconnected('database', 'Database connection is inactive. MySQL service is offline.');
        return false;
      }
    } catch (err) {
      latencyMs.value = Date.now() - start;
      lastCheckedAt.value = new Date();

      const response = err.response;
      if (response && response.status === 503 && response.data?.data?.services?.database === 'disconnected') {
        markDisconnected('database', 'Database connection is inactive. MySQL service is offline.');
      } else if (response) {
        const isDbErr =
          response.data?.error?.code === 'DB_UNAVAILABLE' ||
          (response.status === 503 && response.data?.data?.services?.database === 'disconnected') ||
          /(?:SQLSTATE\[HY000\]\s*\[2002\]|SQLSTATE\[HY000\]:\s*General error:\s*2006|ECONNREFUSED|actively refused|No connection could be made)/i.test(
            response.data?.error?.message || response.data?.message || ''
          );
        if (isDbErr) {
          markDisconnected('database', response.data?.error?.message || response.data?.message || 'Database unavailable.');
        } else {
          markDisconnected('server', 'Backend server returned an error.');
        }
      } else {
        markDisconnected('server', 'Unable to reach backend server.');
      }
      return false;
    } finally {
      isProbing.value = false;
    }
  }

  function minimize() {
    isMinimized.value = true;
  }

  function expand() {
    isMinimized.value = false;
  }

  function retry() {
    return checkHealth(false);
  }

  function resetState() {
    stopProbe();
    if (recoveryTimer) {
      clearTimeout(recoveryTimer);
      recoveryTimer = null;
    }
    connectionState.value = 'connected';
    disconnectType.value = null;
    errorMessage.value = '';
    recoveryMessage.value = '';
    isProbing.value = false;
    isMinimized.value = false;
    hasActionFailure.value = false;
    lastCheckedAt.value = null;
    latencyMs.value = null;
  }

  return {
    connectionState,
    disconnectType,
    errorMessage,
    recoveryMessage,
    isProbing,
    isMinimized,
    hasActionFailure,
    lastCheckedAt,
    latencyMs,
    isConnected,
    isDisconnected,
    isRecovered,
    isBannerVisible,
    markDisconnected,
    reportNetworkFailure,
    recordActionFailure,
    clearActionFailure,
    markConnected,
    checkHealth,
    retry,
    minimize,
    expand,
    resetState,
  };
}

