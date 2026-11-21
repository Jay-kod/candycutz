import { useBarberReviews } from '../useBarberReviews';

vi.mock('@/shared/api/client', () => ({
  client: {
    get: vi.fn(),
  },
  default: {
    get: vi.fn(),
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
  }),
}));

describe('useBarberReviews composable', () => {
  it('correctly computes stats from review list', () => {
    const { reviews, stats, totalCount, approvedCount, pendingCount, fiveStarCount } = useBarberReviews();

    reviews.value = [
      { id: 1, rating: 5, is_approved: true, review: 'Fantastic fade cut', service_id: 10 },
      { id: 2, rating: 5, is_approved: true, review: 'Best barber in town', service_id: 10 },
      { id: 3, rating: 4, is_approved: false, review: 'Good styling', service_id: 20 },
    ];

    expect(totalCount.value).toBe(3);
    expect(approvedCount.value).toBe(2);
    expect(pendingCount.value).toBe(1);
    expect(fiveStarCount.value).toBe(2);
    expect(stats.value.averageRating).toBe('4.7');
    expect(stats.value.satisfactionRate).toBe(100);
  });

  it('filters reviews by service, rating, status, and search query', () => {
    const {
      reviews,
      filteredTestimonials,
      search,
      activeServiceFilter,
      activeRatingFilter,
      activeStatusFilter,
      resetFilters,
      hasActiveFilters,
    } = useBarberReviews();

    reviews.value = [
      {
        id: 1,
        client_name: 'John Doe',
        rating: 5,
        is_approved: true,
        review: 'Clean fade',
        service_id: 10,
        service: { id: 10, name: 'Skin Fade' },
      },
      {
        id: 2,
        client_name: 'Jane Smith',
        rating: 4,
        is_approved: false,
        review: 'Beard line-up was crisp',
        service_id: 20,
        service: { id: 20, name: 'Beard Trim' },
      },
    ];

    expect(filteredTestimonials.value.length).toBe(2);
    expect(hasActiveFilters.value).toBe(false);

    // Filter by search
    search.value = 'john';
    expect(filteredTestimonials.value.length).toBe(1);
    expect(filteredTestimonials.value[0].id).toBe(1);
    expect(hasActiveFilters.value).toBe(true);

    // Reset
    resetFilters();
    expect(search.value).toBe('');
    expect(filteredTestimonials.value.length).toBe(2);

    // Filter by status
    activeStatusFilter.value = 'pending';
    expect(filteredTestimonials.value.length).toBe(1);
    expect(filteredTestimonials.value[0].id).toBe(2);

    // Filter by rating
    activeStatusFilter.value = 'all';
    activeRatingFilter.value = 5;
    expect(filteredTestimonials.value.length).toBe(1);
    expect(filteredTestimonials.value[0].id).toBe(1);

    // Filter by service
    activeRatingFilter.value = 'all';
    activeServiceFilter.value = 20;
    expect(filteredTestimonials.value.length).toBe(1);
    expect(filteredTestimonials.value[0].id).toBe(2);
  });

  it('formats dates consistently', () => {
    const { formatDate } = useBarberReviews();
    expect(formatDate(null)).toBe('');
    expect(formatDate('2026-10-25T10:00:00Z')).toContain('2026');
  });
});

