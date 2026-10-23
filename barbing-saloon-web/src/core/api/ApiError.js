/**
 * Standardized API Error for CandyCutz Web Client.
 * Represents all server, network, and validation errors uniformly.
 */
export class ApiError extends Error {
  /**
   * @param {Object} params
   * @param {string} [params.message='An unexpected error occurred.']
   * @param {string} [params.code='SERVER_ERROR']
   * @param {number} [params.status=500]
   * @param {string|null} [params.requestId=null]
   * @param {Record<string, any>} [params.details={}]
   * @param {boolean} [params.retryable=false]
   * @param {string} [params.action='none'] - none | retry | wait | fix_input | logout | reauth | update_app | contact_support
   * @param {number|null} [params.retryAfter=null]
   * @param {boolean} [params.isNetwork=false]
   * @param {boolean} [params.isTimeout=false]
   */
  constructor({
    message = 'An unexpected error occurred.',
    code = 'SERVER_ERROR',
    status = 500,
    requestId = null,
    details = {},
    retryable = false,
    action = 'none',
    retryAfter = null,
    isNetwork = false,
    isTimeout = false,
  } = {}) {
    super(message);
    this.name = 'ApiError';
    this.code = code;
    this.status = status;
    this.requestId = requestId;
    this.details = details || {};
    this.retryable = Boolean(retryable);
    this.action = action || 'none';
    this.retryAfter = retryAfter ? Number(retryAfter) : null;
    this.isNetwork = Boolean(isNetwork);
    this.isTimeout = Boolean(isTimeout);

    // Maintain V8 stack trace
    if (Error.captureStackTrace) {
      Error.captureStackTrace(this, ApiError);
    }
  }

  /**
   * Helper to check if specific field validation errors exist
   * @returns {Record<string, string|string[]>}
   */
  get fieldErrors() {
    return this.details?.field_errors || {};
  }
}
