import client, { api, ApiError, setupApiClient, generateRequestId } from '@/core/api/client';
import { handleApiError } from '@/core/api/handleApiError';
import { recordClientError, addBreadcrumb } from '@/core/api/errorReporter';

export {
  client,
  api,
  ApiError,
  handleApiError,
  recordClientError,
  addBreadcrumb,
  setupApiClient,
  generateRequestId,
};

export default client;
