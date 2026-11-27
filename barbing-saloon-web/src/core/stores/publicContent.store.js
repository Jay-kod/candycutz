import { defineStore } from 'pinia';
import { publicApi } from '@/shared/api/old_publicApi';

const DEFAULT_TTL_MS = 5 * 60 * 1000; // 5 minutes memory cache
const SWR_STORAGE_KEY = 'candycutz_public_swr_v1';

function readPersistedSwr() {
  if (typeof window === 'undefined' || !window.sessionStorage) return {};
  try {
    const raw = window.sessionStorage.getItem(SWR_STORAGE_KEY);
    return raw ? JSON.parse(raw) : {};
  } catch {
    return {};
  }
}

function writePersistedSwr(state) {
  if (typeof window === 'undefined' || !window.sessionStorage) return;
  try {
    window.sessionStorage.setItem(
      SWR_STORAGE_KEY,
      JSON.stringify({
        settings: state.settings,
        settingsLastFetched: state.settingsLastFetched,
        services: state.services,
        servicesLastFetched: state.servicesLastFetched,
        categories: state.categories,
        categoriesLastFetched: state.categoriesLastFetched,
        testimonials: state.testimonials,
        testimonialsLastFetched: state.testimonialsLastFetched,
        gallery: state.gallery,
        galleryLastFetched: state.galleryLastFetched,
        galleryCategories: state.galleryCategories,
        galleryCategoriesLastFetched: state.galleryCategoriesLastFetched,
      })
    );
  } catch {
    // Ignore storage quota errors cleanly
  }
}

export const usePublicContentStore = defineStore('publicContent', {
  state: () => {
    const cached = readPersistedSwr();
    return {
      settings: cached.settings || null,
      settingsLastFetched: cached.settingsLastFetched || 0,
      settingsLoading: false,

      services: Array.isArray(cached.services) ? cached.services : [],
      servicesLastFetched: cached.servicesLastFetched || 0,
      servicesLoading: false,

      categories: Array.isArray(cached.categories) ? cached.categories : [],
      categoriesLastFetched: cached.categoriesLastFetched || 0,
      categoriesLoading: false,

      testimonials: Array.isArray(cached.testimonials) ? cached.testimonials : [],
      testimonialsLastFetched: cached.testimonialsLastFetched || 0,
      testimonialsLoading: false,

      gallery: Array.isArray(cached.gallery) ? cached.gallery : [],
      galleryLastFetched: cached.galleryLastFetched || 0,
      galleryLoading: false,

      galleryCategories: Array.isArray(cached.galleryCategories) ? cached.galleryCategories : [],
      galleryCategoriesLastFetched: cached.galleryCategoriesLastFetched || 0,
      galleryCategoriesLoading: false,
    };
  },

  getters: {
    isSettingsFresh: (state) => Date.now() - state.settingsLastFetched < DEFAULT_TTL_MS,
    isServicesFresh: (state) => Date.now() - state.servicesLastFetched < DEFAULT_TTL_MS,
    isCategoriesFresh: (state) => Date.now() - state.categoriesLastFetched < DEFAULT_TTL_MS,
    isTestimonialsFresh: (state) => Date.now() - state.testimonialsLastFetched < DEFAULT_TTL_MS,
    isGalleryFresh: (state) => Date.now() - state.galleryLastFetched < DEFAULT_TTL_MS,
    isGalleryCategoriesFresh: (state) => Date.now() - state.galleryCategoriesLastFetched < DEFAULT_TTL_MS,
  },

  actions: {
    clearSettingsCache() {
      this.settings = null;
      this.settingsLastFetched = 0;
      writePersistedSwr(this);
    },

    async getSettings(force = false) {
      if (this.settings && !force) {
        if (!this.isSettingsFresh && !this.settingsLoading) {
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
        writePersistedSwr(this);
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch settings:', err);
      } finally {
        this.settingsLoading = false;
      }
      return this.settings;
    },

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
        writePersistedSwr(this);
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch services:', err);
      } finally {
        this.servicesLoading = false;
      }
      return this.services;
    },

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
        writePersistedSwr(this);
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch categories:', err);
      } finally {
        this.categoriesLoading = false;
      }
      return this.categories;
    },

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
          writePersistedSwr(this);
        }
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch testimonials:', err);
      } finally {
        this.testimonialsLoading = false;
      }
      return this.testimonials;
    },

    async getGallery(force = false) {
      if (this.gallery.length > 0 && !force) {
        if (!this.isGalleryFresh && !this.galleryLoading) {
          this.revalidateGallery();
        }
        return this.gallery;
      }

      return this.revalidateGallery();
    },

    async revalidateGallery() {
      if (this.galleryLoading) return this.gallery;
      this.galleryLoading = true;
      try {
        const response = await publicApi.gallery();
        const items = response.data?.data?.items || response.data?.data || response.data || [];
        this.gallery = Array.isArray(items) ? items : [];
        this.galleryLastFetched = Date.now();
        writePersistedSwr(this);
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch gallery:', err);
      } finally {
        this.galleryLoading = false;
      }
      return this.gallery;
    },

    async getGalleryCategories(force = false) {
      if (this.galleryCategories.length > 0 && !force) {
        if (!this.isGalleryCategoriesFresh && !this.galleryCategoriesLoading) {
          this.revalidateGalleryCategories();
        }
        return this.galleryCategories;
      }

      return this.revalidateGalleryCategories();
    },

    async revalidateGalleryCategories() {
      if (this.galleryCategoriesLoading) return this.galleryCategories;
      this.galleryCategoriesLoading = true;
      try {
        const response = await publicApi.galleryCategories();
        const items = response.data?.data || response.data || [];
        this.galleryCategories = Array.isArray(items) ? items : [];
        this.galleryCategoriesLastFetched = Date.now();
        writePersistedSwr(this);
      } catch (err) {
        console.error('[PublicContentStore] Failed to fetch gallery categories:', err);
      } finally {
        this.galleryCategoriesLoading = false;
      }
      return this.galleryCategories;
    },

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
      if (key === 'all' || key === 'gallery') {
        this.galleryLastFetched = 0;
        this.galleryCategoriesLastFetched = 0;
      }
      if (key === 'all' || key === 'galleryCategories') {
        this.galleryCategoriesLastFetched = 0;
      }
      writePersistedSwr(this);
    },
  },
});
