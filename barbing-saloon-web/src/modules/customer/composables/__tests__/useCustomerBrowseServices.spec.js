import { describe, it, expect, vi } from 'vitest';
import { matchesService, useCustomerBrowseServices } from '../useCustomerBrowseServices';

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: { data: [] } }),
    post: vi.fn().mockResolvedValue({ data: {} }),
    delete: vi.fn().mockResolvedValue({ data: {} })
  }
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn()
  })
}));

describe('matchesService', () => {
  const service = {
    id: 1,
    name: 'Signature Haircut',
    description: 'Precision clipper styling with razor outline',
    category_name: 'Signature Haircuts',
    barber: { name: 'David Beckham' }
  };

  it('matches exact case-insensitive keywords', () => {
    expect(matchesService(service, 'haircut')).toBe(true);
    expect(matchesService(service, 'HAIRCUT')).toBe(true);
    expect(matchesService(service, 'signature')).toBe(true);
  });

  it('matches plural search queries to singular service names (e.g. haircuts -> haircut)', () => {
    expect(matchesService(service, 'haircuts')).toBe(true);
  });

  it('matches spaced search terms without spaces (e.g. hair cut -> haircut)', () => {
    expect(matchesService(service, 'hair cut')).toBe(true);
  });

  it('matches description, category, and barber name', () => {
    expect(matchesService(service, 'clipper')).toBe(true);
    expect(matchesService(service, 'razor')).toBe(true);
    expect(matchesService(service, 'Beckham')).toBe(true);
    expect(matchesService(service, 'david')).toBe(true);
  });

  it('returns false for completely unrelated terms', () => {
    expect(matchesService(service, 'manicure')).toBe(false);
    expect(matchesService(service, 'coloring')).toBe(false);
  });
});

describe('useCustomerBrowseServices filtering', () => {
  it('filters services correctly and provides cross-category fallback when searching', () => {
    const {
      services,
      activeCategory,
      searchQuery,
      filteredServices,
      executeSearch
    } = useCustomerBrowseServices();

    services.value = [
      { id: 1, name: 'Signature Haircut', category_name: 'Signature Haircuts', price: 5000 },
      { id: 2, name: 'Beard Trim & Shape', category_name: 'Beard & Shave Lounge', price: 3000 },
      { id: 3, name: 'Junior Haircut', category_name: 'Junior & Student Cuts', price: 3500 },
      { id: 4, name: 'Royal VIP Treatment', category_name: 'Royal VIP Treatments', price: 15000 }
    ];

    // Initial state: All
    expect(filteredServices.value.length).toBe(4);

    // Plural search 'haircuts' matches 2 haircuts
    searchQuery.value = 'haircuts';
    expect(filteredServices.value.length).toBe(2);
    expect(filteredServices.value.map(s => s.name)).toEqual(['Signature Haircut', 'Junior Haircut']);

    // When in Beard category but searching for 'haircuts', gracefully fall back so user gets results
    activeCategory.value = 'Beard & Shave Lounge';
    expect(filteredServices.value.length).toBe(2);

    // If searching for beard while in Beard category, matches Beard
    searchQuery.value = 'beard trims';
    expect(filteredServices.value.length).toBe(1);
    expect(filteredServices.value[0].name).toBe('Beard Trim & Shape');

    // Executing search resets category if 0 category matches
    activeCategory.value = 'Beard & Shave Lounge';
    executeSearch('vip');
    expect(activeCategory.value).toBe('All');
    expect(filteredServices.value.length).toBe(1);
    expect(filteredServices.value[0].name).toBe('Royal VIP Treatment');
  });
});
