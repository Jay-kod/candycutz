import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

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

  // Helper to extract clean category name
  function getCategoryName(service) {
    if (!service) return 'General';
    if (service.category_name) return service.category_name;
    if (typeof service.category === 'string') return service.category;
    if (service.category?.name) return service.category.name;
    return 'General';
  }

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

    // 1. Category filter
    if (activeCategory.value !== 'All') {
      list = list.filter(s => getCategoryName(s) === activeCategory.value);
    }

    // 2. Search query filter
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim();
      list = list.filter(s => {
        const nameMatch = s.name?.toLowerCase().includes(q);
        const descMatch = s.description?.toLowerCase().includes(q);
        const catMatch = getCategoryName(s).toLowerCase().includes(q);
        return nameMatch || descMatch || catMatch;
      });
    }

    // 3. Sorting
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
        wishlist.value = wishlist.value.filter(itemId => itemId !== id);
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
    resetFilters,
    toggleWishlist
  };
}

