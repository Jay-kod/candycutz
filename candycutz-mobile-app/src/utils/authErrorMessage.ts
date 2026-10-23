import { getErrorMessage } from './errorCopy';

/**
 * Backward-compatible wrapper delegating to honest error copy resolver.
 */
export function resolveAuthErrorMessage(error: any, fallback?: string): string {
  return getErrorMessage(error, fallback);
}
