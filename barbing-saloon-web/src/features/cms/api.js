import { api } from '@/shared/api/client';

export const cmsApi = {
  getGallery: () => api.get('/v1/gallery'),
  getGalleryItem: (id) => api.get(`/v1/gallery/${id}`),
  getTestimonials: () => api.get('/v1/testimonials'),
  getBlogPosts: () => api.get('/v1/blog'),
  getBlogPost: (slug) => api.get(`/v1/blog/${slug}`),
};
