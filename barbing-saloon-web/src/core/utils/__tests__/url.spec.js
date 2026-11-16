import { describe, expect, it } from 'vitest';
import { getStorageUrl, getThumbnailUrl, API_ROOT } from '../url';

describe('getStorageUrl', () => {
  it('returns fallback when path is null or undefined or empty', () => {
    expect(getStorageUrl(null, '/fallback.png')).toBe('/fallback.png');
    expect(getStorageUrl(undefined, '/fallback.png')).toBe('/fallback.png');
    expect(getStorageUrl('', '/fallback.png')).toBe('/fallback.png');
  });

  it('preserves external http/https and data urls', () => {
    const unsplash = 'https://images.unsplash.com/photo-1503951914875?w=500';
    expect(getStorageUrl(unsplash)).toBe(unsplash);

    const dataUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAE=';
    expect(getStorageUrl(dataUrl)).toBe(dataUrl);
  });

  it('normalizes legacy /storage/images/ paths to static frontend /images/', () => {
    expect(getStorageUrl('/storage/images/avatar.jpg')).toBe('/images/avatar.jpg');
    expect(getStorageUrl('http://localhost:8000/images/logo.png')).toBe('/images/logo.png');
  });

  it('resolves relative private media paths against API_ROOT', () => {
    const relative = '/api/v1/private-media/users/4/avatar/abc.webp?expires=123&signature=xyz';
    expect(getStorageUrl(relative)).toBe(`${API_ROOT}/api/v1/private-media/users/4/avatar/abc.webp?expires=123&signature=xyz`);

    const noLeadingSlash = 'api/v1/private-media/users/4/avatar/abc.webp?expires=123&signature=xyz';
    expect(getStorageUrl(noLeadingSlash)).toBe(`${API_ROOT}/api/v1/private-media/users/4/avatar/abc.webp?expires=123&signature=xyz`);
  });

  it('rewrites absolute private media URLs to the current API_ROOT', () => {
    const backendUrl = 'http://127.0.0.1:8000/api/v1/private-media/users/2/avatar/test.webp?expires=456&signature=sig123';
    expect(getStorageUrl(backendUrl)).toBe(`${API_ROOT}/api/v1/private-media/users/2/avatar/test.webp?expires=456&signature=sig123`);

    const subfolderUrl = 'http://localhost/1/candycutz/barbing-saloon-api/public/api/v1/private-media/users/2/avatar/test.webp?expires=456&signature=sig123';
    expect(getStorageUrl(subfolderUrl)).toBe(`${API_ROOT}/api/v1/private-media/users/2/avatar/test.webp?expires=456&signature=sig123`);
  });

  it('resolves standard /storage/ paths to API_ROOT', () => {
    expect(getStorageUrl('/storage/services/cut.webp')).toBe(`${API_ROOT}/storage/services/cut.webp`);
    expect(getStorageUrl('storage/services/cut.webp')).toBe(`${API_ROOT}/storage/services/cut.webp`);
    expect(getStorageUrl('services/cut.webp')).toBe(`${API_ROOT}/storage/services/cut.webp`);
  });
});

describe('getThumbnailUrl', () => {
  it('returns fallback for falsy path', () => {
    expect(getThumbnailUrl(null, '/thumb-fallback.png')).toBe('/thumb-fallback.png');
  });

  it('does NOT alter private media URLs to avoid invalidating cryptographic signatures', () => {
    const signedUrl = `${API_ROOT}/api/v1/private-media/users/2/avatar/portrait.webp?expires=123&signature=sig`;
    expect(getThumbnailUrl(signedUrl)).toBe(signedUrl);
  });

  it('adds thumb_ prefix for public storage webp images and retains query parameters', () => {
    const publicImg = 'gallery/photo.webp';
    expect(getThumbnailUrl(publicImg)).toBe(`${API_ROOT}/storage/gallery/thumb_photo.webp`);

    const publicWithQuery = `${API_ROOT}/storage/gallery/photo.webp?v=2`;
    expect(getThumbnailUrl(publicWithQuery)).toBe(`${API_ROOT}/storage/gallery/thumb_photo.webp?v=2`);
  });
});
