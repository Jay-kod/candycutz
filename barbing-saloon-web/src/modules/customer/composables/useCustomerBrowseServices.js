import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

// Helper to extract clean category name
export function getCategoryName(service) {
  if (!service) return 'General';
  if (service.category_name) return service.category_name;
  if (typeof service.category === 'string') return service.category;
  if (service.category?.name) return service.category.name;
  return 'General';
}

/**
 * Intelligent fuzzy & tokenized service search:
 * - Direct substring and space-insensitive matching ("hair cut" matches "haircut")
 * - Morphological stemming (plural to singular: "haircuts" -> "haircut", "treatments" -> "treatment", "trims" -> "trim")
 * - Matches across title, description, category, and barber name
 */
export function matchesService(service, query) {
  if (!query || !query.trim()) return true;

  const q = query.toLowerCase().trim();
  const qNoSpaces = q.replace(/[\s\-_]+/g, '');

  const name = (service.name || '').toLowerCase();
  const desc = (service.description || '').toLowerCase();
  const cat = getCategoryName(service).toLowerCase();
  const barber = (service.barber?.name || service.barber?.user?.name || '').toLowerCase();

  const combined = `${name} ${desc} ${cat} ${barber}`;
  const combinedNoSpaces = combined.replace(/[\s\-_]+/g, '');

  // 1. Direct or spaceless match
  if (combined.includes(q) || (qNoSpaces.length > 2 && combinedNoSpaces.includes(qNoSpaces))) {
    return true;
  }

  // 2. Tokenized search with light stemming
  const tokens = q.split(/\s+/).filter(t => t.length > 0);
  const stem = (word) => {
    if (word.length <= 3) return word;
    if (word.endsWith('ies')) return word.slice(0, -3) + 'y';
    if (word.endsWith('es')) return word.slice(0, -2);
    if (word.endsWith('s') && !word.endsWith('ss')) return word.slice(0, -1);
    if (word.endsWith('ing')) return word.slice(0, -3);
    return word;
  };

  return tokens.every(token => {
    const tokenStem = stem(token);
    return combined.includes(token) || combined.includes(tokenStem);
  });
}

export function useCustomerBrowseServices() {
  const toast = useToast();

  const services = ref([]);
  const wishlist = ref([]);
  const pageTitle = ref('Our Services');
  const pageSubtitle = ref('Explore our curated menu of master barbering and luxury grooming services.');
  
  const activeCategory = ref('All');
  const searchQuery = ref('');
  const sortOrder = ref('featured');
  const isLoading = ref(true);
  const isTogglingWishlist = ref(null);

  // Dynamic available categories list with counts
  const availableCategories = computed(() => {
    const counts = {};
    services.value.forEach(s => {
      const cat = getCategoryName(s);
      counts[cat] = (counts[cat] || 0) + 1;
    });

    const categoriesList = Object.keys(counts).sort().map(name => ({
      name,
      count: counts[name]
    }));

    return [
      { name: 'All', count: services.value.length },
      ...categoriesList
    ];
  });

  // Filtered and sorted services
  const filteredServices = computed(() => {
    let list = [...services.value];

    // 1. Search query filter
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.trim();
      const matchingServices = list.filter(s => matchesService(s, q));

      if (activeCategory.value !== 'All') {
        const categoryMatches = matchingServices.filter(s => getCategoryName(s) === activeCategory.value);
        // If results match within selected category, show them;
        // otherwise gracefully fallback to all matching services across categories
        list = categoryMatches.length > 0 ? categoryMatches : matchingServices;
      } else {
        list = matchingServices;
      }
    } else if (activeCategory.value !== 'All') {
      list = list.filter(s => getCategoryName(s) === activeCategory.value);
    }

    // 2. Sorting
    if (sortOrder.value === 'price-low') {
      list.sort((a, b) => (Number(a.price) || 0) - (Number(b.price) || 0));
    } else if (sortOrder.value === 'price-high') {
      list.sort((a, b) => (Number(b.price) || 0) - (Number(a.price) || 0));
    } else if (sortOrder.value === 'duration-short') {
      list.sort((a, b) => (Number(a.duration_minutes) || 0) - (Number(b.duration_minutes) || 0));
    } else if (sortOrder.value === 'duration-long') {
      list.sort((a, b) => (Number(b.duration_minutes) || 0) - (Number(a.duration_minutes) || 0));
    } else if (sortOrder.value === 'name-az') {
      list.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    }

    return list;
  });

  async function loadData() {
    isLoading.value = true;
    try {
      const [settingsRes, servicesRes, wishlistRes] = await Promise.all([
        client.get('/settings').catch(() => ({ data: { data: {} } })),
        client.get('/services'),
        client.get('/customer/wishlist').catch(() => ({ data: { data: [] } }))
      ]);

      const settings = settingsRes.data?.data || settingsRes.data;
      if (settings) {
        if (settings.services_title) pageTitle.value = settings.services_title;
        if (settings.services_subtitle) pageSubtitle.value = settings.services_subtitle;
      }

      const servicesData = servicesRes.data?.data || servicesRes.data || [];
      services.value = Array.isArray(servicesData) ? servicesData : [];

      const rawWishlist = wishlistRes.data?.data || wishlistRes.data || [];
      const wishlistArray = Array.isArray(rawWishlist) ? rawWishlist : [];
      wishlist.value = wishlistArray
        .filter(item => item.item_type === 'service')
        .map(item => Number(item.item_id));
    } catch (err) {
      toast.error('Failed to load services catalogue');
    } finally {
      isLoading.value = false;
    }
  }

  function changeCategory(categoryName) {
    activeCategory.value = categoryName;
  }

  function executeSearch(query) {
    if (typeof query === 'string') {
      searchQuery.value = query;
    }
    // If searching while in a category that has 0 matches, auto-switch to All for visibility
    if (searchQuery.value.trim() && activeCategory.value !== 'All') {
      const categoryMatches = services.value.filter(
        s => getCategoryName(s) === activeCategory.value && matchesService(s, searchQuery.value)
      );
      if (categoryMatches.length === 0) {
        activeCategory.value = 'All';
      }
    }
  }

  function resetFilters() {
    activeCategory.value = 'All';
    searchQuery.value = '';
    sortOrder.value = 'featured';
  }

  async function toggleWishlist(serviceId) {
    const id = Number(serviceId);
    const targetService = services.value.find(s => Number(s.id) === id);
    const serviceName = targetService?.name || 'Service';
    const isCurrentlyWishlisted = wishlist.value.includes(id);

    isTogglingWishlist.value = id;

    if (isCurrentlyWishlisted) {
      // Optimistic removal
      wishlist.value = wishlist.value.filter(itemId => itemId !== id);
      try {
        await client.delete(`/customer/wishlist/item?type=service&id=${id}`);
        toast.info(`"${serviceName}" removed from your wishlist`);
      } catch (err) {
        // Revert on failure
        wishlist.value.push(id);
        toast.error('Failed to update wishlist');
      } finally {
        isTogglingWishlist.value = null;
      }
    } else {
      // Optimistic addition
      wishlist.value.push(id);
      try {
        await client.post('/customer/wishlist', {
          item_type: 'service',
          item_id: id
        });
        toast.success(`"${serviceName}" added to your wishlist`);
      } catch (err) {
        // Revert on failure
        wishlist.value.filter(itemId => itemId !== id);
        toast.error('Failed to add to wishlist');
      } finally {
        isTogglingWishlist.value = null;
      }
    }
  }

  return {
    services,
    wishlist,
    pageTitle,
    pageSubtitle,
    activeCategory,
    searchQuery,
    sortOrder,
    isLoading,
    isTogglingWishlist,
    getCategoryName,
    availableCategories,
    filteredServices,
    loadData,
    changeCategory,
    executeSearch,
    resetFilters,
    toggleWishlist
  };
}
