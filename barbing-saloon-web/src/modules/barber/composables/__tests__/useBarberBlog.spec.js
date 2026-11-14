import { describe, it, expect, vi } from 'vitest';
import { useBarberBlog } from '../useBarberBlog';

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
  }),
}));

vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({
    confirm: vi.fn().mockResolvedValue(true),
  }),
}));

describe('useBarberBlog.js', () => {
  it('computes stats correctly', () => {
    const blog = useBarberBlog();
    blog.posts.value = [
      { id: 1, title: 'Fade 1', is_published: 1, loves_count: 5 },
      { id: 2, title: 'Fade 2', is_published: 0, status: 'draft', loves_count: 0 },
      { id: 3, title: 'Fade 3', is_published: 1, loves_count: 10 },
    ];

    expect(blog.stats.value.totalCount).toBe(3);
    expect(blog.stats.value.publishedCount).toBe(2);
    expect(blog.stats.value.draftsCount).toBe(1);
    expect(blog.stats.value.totalLoves).toBe(15);
    expect(blog.stats.value.publishRate).toBe(67);
  });

  it('filters posts by status and search query', () => {
    const blog = useBarberBlog();
    blog.posts.value = [
      { id: 1, title: 'Razor Sharp Edges', excerpt: 'How to line up', is_published: 1 },
      { id: 2, title: 'Beard Grooming Guide', excerpt: 'Oil and butter', is_published: 0 },
      { id: 3, title: 'Low Taper Secrets', excerpt: 'Fade steps', is_published: 1 },
    ];

    // Status: published
    blog.statusFilter.value = 'published';
    expect(blog.filteredPosts.value.length).toBe(2);

    // Status: draft
    blog.statusFilter.value = 'draft';
    expect(blog.filteredPosts.value.length).toBe(1);
    expect(blog.filteredPosts.value[0].title).toBe('Beard Grooming Guide');

    // Search query
    blog.statusFilter.value = 'all';
    blog.searchQuery.value = 'taper';
    expect(blog.filteredPosts.value.length).toBe(1);
    expect(blog.filteredPosts.value[0].title).toBe('Low Taper Secrets');
  });

  it('calculates read time based on word count', () => {
    const blog = useBarberBlog();
    const shortText = 'One two three four five.';
    expect(blog.calculateReadTime(shortText)).toBe('1 min read');

    const longWords = Array(450).fill('word').join(' ');
    expect(blog.calculateReadTime(longWords)).toBe('3 min read');
  });
});

