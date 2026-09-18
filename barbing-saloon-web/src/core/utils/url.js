/**
 * Authoritative URL & Asset Resolution Utility for CandyCutz Web.
 * Guarantees zero path collisions, prevents double /api/v1 prefixes,
 * and robustly resolves local and production backend URLs.
 */

const rawApiUrl = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000').trim().replace(/\/+$/, '');

// Extract host origin root by stripping any trailing /api/v1, /api, or /v1
export const API_ROOT = rawApiUrl.replace(/\/(api\/v1|api|v1)$/i, '') || (typeof window !== 'undefined' ? window.location.origin : 'http://localhost:8000');

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

  // Already absolute or data URI
  if (/^(https?:|\/\/|data:)/i.test(path)) {
    return path;
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
