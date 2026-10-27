import { describe, expect, it } from 'vitest';
import { sanitizeBlogContent } from '../sanitizeBlogContent';

describe('sanitizeBlogContent', () => {
  it('removes executable markup and preserves safe formatting', () => {
    const result = sanitizeBlogContent(
      '<p onclick="alert(1)">Safe <strong>formatting</strong><script>alert(2)</script></p>'
      + '<a href="javascript:alert(3)">link</a>'
      + '<a href="https://example.com">safe link</a>'
      + '<img src="https://example.com/image.jpg" onerror="alert(4)">',
    );
    const container = document.createElement('div');
    container.innerHTML = result;

    expect(container.querySelector('p strong')?.textContent).toBe('formatting');
    expect(container.querySelector('script')).toBeNull();
    expect(container.querySelector('[onclick], [onerror]')).toBeNull();
    expect(container.querySelector('a[href^="javascript:"]')).toBeNull();
    expect(container.querySelector('a[href="https://example.com"]')?.textContent).toBe('safe link');
    expect(container.querySelector('img')?.getAttribute('src')).toBe('https://example.com/image.jpg');
  });
});
