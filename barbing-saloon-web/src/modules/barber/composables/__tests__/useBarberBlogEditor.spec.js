import { describe, it, expect, vi } from 'vitest';
import { useBarberBlogEditor } from '../useBarberBlogEditor';

vi.mock('vue-router', () => ({
  useRoute: () => ({ params: { id: '1' } }),
  useRouter: () => ({ push: vi.fn() }),
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
  }),
}));

describe('useBarberBlogEditor.js', () => {
  it('computes word count, character count and reading time', () => {
    const editor = useBarberBlogEditor();

    editor.form.content = 'One two three four five six seven eight nine ten.';
    expect(editor.wordCount.value).toBe(10);
    expect(editor.readingTime.value).toBe('1 min read');
    expect(editor.charCount.value).toBe(49);
  });

  it('computes slug from title if slug is empty', () => {
    const editor = useBarberBlogEditor();

    editor.form.slug = '';
    editor.form.title = '5 Best Barber Fade Tips For 2026!';
    expect(editor.computedSlug.value).toBe('5-best-barber-fade-tips-for-2026');

    editor.form.slug = 'custom-permalink-slug';
    expect(editor.computedSlug.value).toBe('custom-permalink-slug');
  });

  it('handles image removal correctly', () => {
    const editor = useBarberBlogEditor();

    editor.previewImage.value = 'blob:test';
    editor.form.featured_image = 'https://example.com/pic.jpg';
    editor.selectedFile.value = new File([''], 'test.png');

    editor.removeImage();
    expect(editor.previewImage.value).toBe('');
    expect(editor.form.featured_image).toBe('');
    expect(editor.selectedFile.value).toBeNull();
  });
});

