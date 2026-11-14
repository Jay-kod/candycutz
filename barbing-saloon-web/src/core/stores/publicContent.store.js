import { defineStore } from 'pinia';
import { publicApi } from '@/shared/api/old_publicApi';

const DEFAULT_TTL_MS = 5 * 60 * 1000; // 5 minutes memory cache

export const usePublicContentStore = defineStore('publicContent', {
  state: () => ({
    settings: null,
    settingsLastFetched: 0,
    settingsLoading: false,

    services: [],
    servicesLastFetched: 0,
    servicesLoading: false,

    categories: [],
    categoriesLastFetched: 0,
    categoriesLoading: false,

    testimonials: [],
    testimonialsLastFetched: 0,
    testimonialsLoading: false,
  }),

  getters: {
    isSettingsFresh: (state) => Date.now() - state.settingsLastFetched < DEFAULT_TTL_MS,
    isServicesFresh: (state) => Date.now() - state.servicesLastFetched < DEFAULT_TTL_MS,
    isCategoriesFresh: (state) => Date.now() - state.categoriesLastFetched < DEFAULT_TTL_MS,
    isTestimonialsFresh: (state) => Date.now() - state.testimonialsLastFetched < DEFAULT_TTL_MS,
  },

  actions: {
    /**
     * Stale-While-Revalidate public settings loader.
     * Returns cached data immediately if available; revalidates in background if stale.
     */
    async getSettings(force = false) {
      if (this.settings && !force) {
        if (!this.isSettingsFresh && !this.settingsLoading) {
          // Stale-While-Revalidate: re-fetch in background without blocking UI
          this.revalidateSettings();
        }
        return this.settings;
      }

      return this.revalidateSettings();
    },

    async revalidateSettings() {
      if (this.settingsLoading) return this.settings;
      this.settingsLoading = true;
      try {
        const response = await publicApi.settings();
        this.settings = response.data?.data || {};
        this.settingsLastFetched = Date.now();
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch settings:', err);
      } finally {
        this.settingsLoading = false;
      }
      return this.settings;
    },

    /**
     * Stale-While-Revalidate services list.
     */
    async getServices(force = false) {
      if (this.services.length > 0 && !force) {
        if (!this.isServicesFresh && !this.servicesLoading) {
          this.revalidateServices();
        }
        return this.services;
      }

      return this.revalidateServices();
    },

    async revalidateServices() {
      if (this.servicesLoading) return this.services;
      this.servicesLoading = true;
      try {
        const response = await publicApi.services();
        const items = response.data?.data || response.data || [];
        this.services = Array.isArray(items) ? items : [];
        this.servicesLastFetched = Date.now();
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch services:', err);
      } finally {
        this.servicesLoading = false;
      }
      return this.services;
    },

    /**
     * Stale-While-Revalidate service categories list.
     */
    async getCategories(force = false) {
      if (this.categories.length > 0 && !force) {
        if (!this.isCategoriesFresh && !this.categoriesLoading) {
          this.revalidateCategories();
        }
        return this.categories;
      }

      return this.revalidateCategories();
    },

    async revalidateCategories() {
      if (this.categoriesLoading) return this.categories;
      this.categoriesLoading = true;
      try {
        const response = await publicApi.serviceCategories();
        const items = response.data?.data || response.data || [];
        this.categories = Array.isArray(items) ? items : [];
        this.categoriesLastFetched = Date.now();
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch categories:', err);
      } finally {
        this.categoriesLoading = false;
      }
      return this.categories;
    },

    /**
     * Stale-While-Revalidate testimonials list.
     */
    async getTestimonials(force = false) {
      if (this.testimonials.length > 0 && !force) {
        if (!this.isTestimonialsFresh && !this.testimonialsLoading) {
          this.revalidateTestimonials();
        }
        return this.testimonials;
      }

      return this.revalidateTestimonials();
    },

    async revalidateTestimonials() {
      if (this.testimonialsLoading) return this.testimonials;
      this.testimonialsLoading = true;
      try {
        const response = await publicApi.testimonials();
        const items = response.data?.data || response.data || [];
        if (Array.isArray(items)) {
          this.testimonials = items
            .filter((item) => item.review || item.comment)
            .slice(0, 12)
            .map((item) => ({
              ...item,
              client_name: item.client_name || item.customer_name || 'CandyCutz client',
              client_avatar: item.client_avatar || item.avatar_url || null,
            }));
          this.testimonialsLastFetched = Date.now();
        }
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch testimonials:', err);
      } finally {
        this.testimonialsLoading = false;
      }
      return this.testimonials;
    },

    /**
     * Invalidate cached keys explicitly
     */
    invalidate(key = 'all') {
      if (key === 'all' || key === 'settings') {
        this.settingsLastFetched = 0;
      }
      if (key === 'all' || key === 'services') {
        this.servicesLastFetched = 0;
      }
      if (key === 'all' || key === 'categories') {
        this.categoriesLastFetched = 0;
      }
      if (key === 'all' || key === 'testimonials') {
        this.testimonialsLastFetched = 0;
      }
    },
  },
});
