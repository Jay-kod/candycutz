/**
 * Authoritative URL & Asset Resolution Utility for CandyCutz Web.
 * Guarantees zero path collisions, prevents double /api/v1 prefixes,
 * and robustly resolves local and production backend URLs.
 */

const rawApiUrl = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000').trim().replace(/\/+$/, '');

function resolveApiRoot() {
  if (rawApiUrl.startsWith('http://') || rawApiUrl.startsWith('https://')) {
    return rawApiUrl.replace(/\/(api\/v1|api|v1)$/i, '');
  }

  if (typeof window !== 'undefined') {
    // If running under Apache subfolder environment (e.g. /1/candycutz/barbing-saloon-web/...)
    if (window.location.pathname.includes('/1/candycutz/')) {
      return `${window.location.origin}/1/candycutz/barbing-saloon-api/public`;
    }
    return window.location.origin;
  }

  return 'http://localhost:8000';
}

// Extract host origin root
export const API_ROOT = resolveApiRoot();

// Canonical API endpoints
export const API_BASE_URL = `${API_ROOT}/api`;
export const API_V1_BASE_URL = `${API_ROOT}/api/v1`;
export const STORAGE_BASE_URL = `${API_ROOT}/storage`;

/**
 * Resolves an image or media path to an absolute, accessible URL.
 * 
 * @param {string|null|undefined} path Relative or absolute media path
 * @param {string} [fallback=''] Optional fallback if path is empty
 * @returns {string} Fully qualified media URL
 */
export function getStorageUrl(path, fallback = '') {
  if (!path) {
    return fallback;
  }

  // Normalize erroneous /storage/images/ paths (e.g. from legacy seeds or resource serializers)
  if (typeof path === 'string' && path.includes('/storage/images/')) {
    path = path.replace('/storage/images/', '/images/');
  }

  // Keep backend media on the current API origin. This handles API responses
  // generated from APP_URL when the frontend is opened on another local host.
  if (/^(https?:|\/\/|data:)/i.test(path)) {
    if (!/^data:/i.test(path)) {
      const backendMedia = path.match(
        /^(?:https?:)?\/\/[^/]+(\/(?:storage|api\/v1\/private-media)\/[^?#]*)([?#].*)?$/i
      );
      if (backendMedia) {
        return `${API_ROOT}${backendMedia[1]}${backendMedia[2] || ''}`;
      }
    }
    return path;
  }

  // If already starts with /images/ or images/ (static frontend asset in public/images)
  if (path.startsWith('/images/') || path.startsWith('images/')) {
    return path.startsWith('/') ? path : `/${path}`;
  }

  // If already starts with /storage/ or storage/
  if (path.startsWith('/storage/')) {
    return `${API_ROOT}${path}`;
  }
  if (path.startsWith('storage/')) {
    return `${API_ROOT}/${path}`;
  }

  const cleanPath = path.replace(/^\/+/, '');
  return `${STORAGE_BASE_URL}/${cleanPath}`;
}
