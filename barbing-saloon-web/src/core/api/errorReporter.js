import axios from 'axios';
import { API_BASE_URL } from '@/core/utils/url';

const QUEUE_STORAGE_KEY = 'candycutz_client_errors_queue';
const MAX_QUEUE_SIZE = 50;
const BATCH_SIZE = 20;
const MAX_BREADCRUMBS = 20;

// In-memory circular breadcrumb buffer
const breadcrumbs = [];

export function addBreadcrumb(crumb) {
  breadcrumbs.push({
    type: crumb.type || 'log',
    category: crumb.category || 'app',
    message: crumb.message || '',
    data: crumb.data || null,
    timestamp: new Date().toISOString(),
  });

  if (breadcrumbs.length > MAX_BREADCRUMBS) {
    breadcrumbs.shift();
  }
}

export function getBreadcrumbs() {
  return [...breadcrumbs];
}

function getStoredQueue() {
  try {
    if (typeof localStorage === 'undefined') return [];
    const raw = localStorage.getItem(QUEUE_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (e) {
    return [];
  }
}

function saveQueue(queue) {
  try {
    if (typeof localStorage === 'undefined') return;
    localStorage.setItem(QUEUE_STORAGE_KEY, JSON.stringify(queue.slice(0, MAX_QUEUE_SIZE)));
  } catch (e) {
    // LocalStorage write error (quota exceeded, etc.)
  }
}

let isFlushing = false;

export async function flushErrorQueue() {
  if (isFlushing || typeof navigator !== 'undefined' && !navigator.onLine) {
    return;
  }

  const queue = getStoredQueue();
  if (queue.length === 0) {
    return;
  }

  isFlushing = true;
  const batch = queue.slice(0, BATCH_SIZE);

  try {
    const token = typeof localStorage !== 'undefined' ? localStorage.getItem('candycutz_auth_token') : null;
    const headers = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };
    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    await axios.post(
      `${API_BASE_URL}/v1/system/client-errors`,
      { events: batch },
      { headers, timeout: 8000 }
    );

    // Remove successfully transmitted batch from queue
    const remaining = queue.slice(batch.length);
    saveQueue(remaining);
  } catch (err) {
    // If rate-limited (429) or offline, keep in queue for next flush attempt
  } finally {
    isFlushing = false;
  }
}

export function queueClientError(errorPayload) {
  const queue = getStoredQueue();
  queue.push(errorPayload);
  saveQueue(queue);

  // Attempt immediate flush if online
  if (typeof navigator === 'undefined' || navigator.onLine) {
    flushErrorQueue();
  }
}

export function recordClientError(err, context = {}) {
  const eventId = 'evt_' + Math.random().toString(36).substring(2, 10) + Date.now().toString(36);
  const isApiError = err?.name === 'ApiError';

  const payload = {
    event_id: eventId,
    source: 'client_web',
    level: context.level || (isApiError && err.status < 500 ? 'warning' : 'error'),
    name: err?.name || 'Error',
    message: String(err?.message || 'Unknown error').substring(0, 1000),
    stack: (err?.stack || '').substring(0, 8192),
    error_code: isApiError ? err.code : (context.errorCode || 'JS_RUNTIME_ERROR'),
    http_status: isApiError ? err.status : (context.httpStatus || 0),
    request_id: isApiError ? err.requestId : (context.requestId || null),
    route: typeof window !== 'undefined' ? window.location.pathname : '',
    app_version: '1.0.0',
    platform: typeof navigator !== 'undefined' ? `${navigator.platform || ''} ${navigator.userAgent || ''}`.trim() : 'web',
    device: typeof navigator !== 'undefined' && /Mobile|Android|iPhone/i.test(navigator.userAgent) ? 'Mobile' : 'Desktop',
    network_state: typeof navigator !== 'undefined' && navigator.onLine ? 'online' : 'offline',
    occurred_at: new Date().toISOString(),
    breadcrumbs: getBreadcrumbs(),
  };

  queueClientError(payload);
}

// Global window event listeners
if (typeof window !== 'undefined') {
  window.addEventListener('online', () => {
    flushErrorQueue();
  });

  window.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') {
      flushErrorQueue();
    }
  });
}
