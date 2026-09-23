import { Platform } from 'react-native';
import axios from 'axios';
import { CONFIG } from '../constants/config';
import { tokenStorage } from '../api/client';

export interface CrashReportPayload {
  error_message: string;
  stack_trace?: string | null;
  component_stack?: string | null;
  app_version?: string;
  platform?: string;
  device_info?: Record<string, any>;
}

let isReporting = false;

/**
 * Sends crash report telemetry to the CandyCutz backend.
 * Uses a standalone axios call to bypass normal client interceptors.
 */
export async function reportCrash(
  error: unknown,
  componentStack?: string | null,
  additionalInfo?: Record<string, any>
): Promise<void> {
  if (isReporting) {
    return;
  }

  isReporting = true;

  try {
    const errorMessage =
      error instanceof Error
        ? error.message
        : typeof error === 'string'
        ? error
        : JSON.stringify(error) || 'Unknown Client Error';

    const stackTrace = error instanceof Error ? error.stack : undefined;

    const payload: CrashReportPayload = {
      error_message: errorMessage,
      stack_trace: stackTrace || null,
      component_stack: componentStack || null,
      app_version: CONFIG.APP_VERSION,
      platform: Platform.OS,
      device_info: {
        platform: Platform.OS,
        version: Platform.Version,
        isTesting: process.env.NODE_ENV === 'test',
        ...additionalInfo,
      },
    };

    const token = await tokenStorage.get().catch(() => null);

    await axios.post(`${CONFIG.API_BASE_URL}/app/crashes`, payload, {
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Client-Type': 'mobile',
        'X-App-Version': CONFIG.APP_VERSION,
        'X-App-Platform': Platform.OS,
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      timeout: 8000,
    });
  } catch (err) {
    // Fail silently so telemetry failure never cascades into more crashes
    if (__DEV__) {
      console.warn('[Telemetry] Failed to report crash to server:', err);
    }
  } finally {
    isReporting = false;
  }
}

/**
 * Initializes global JS exception handling where supported.
 */
export function initGlobalErrorHandler(): void {
  const globalErrorUtils = (global as any).ErrorUtils;
  if (globalErrorUtils && typeof globalErrorUtils.getGlobalHandler === 'function') {
    const defaultHandler = globalErrorUtils.getGlobalHandler();

    globalErrorUtils.setGlobalHandler((error: any, isFatal?: boolean) => {
      reportCrash(error, null, { isFatal: !!isFatal }).finally(() => {
        if (defaultHandler) {
          defaultHandler(error, isFatal);
        }
      });
    });
  }
}
