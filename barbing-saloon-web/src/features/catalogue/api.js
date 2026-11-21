import { api } from '@/shared/api/client';

export const catalogueApi = {
  getServices: () => api.get('/v1/services'),
  getService: (idOrSlug) => api.get(`/v1/services/${idOrSlug}`),
  getCategories: () => api.get('/v1/service-categories'),
  createCategory: (payload) => api.post('/v1/service-categories', payload),
  updateCategory: (id, payload) => api.put(`/v1/service-categories/${id}`, payload),
  getCategoryRequests: (params) => api.get('/v1/service-category-requests', { params }),
  requestCategory: (payload) => api.post('/v1/service-category-requests', payload),
  approveCategoryRequest: (id) => api.patch(`/v1/service-category-requests/${id}/approve`),
  rejectCategoryRequest: (id, payload) => api.patch(`/v1/service-category-requests/${id}/reject`, payload),
  getBarbers: () => api.get('/v1/barbers'),
  getBarber: (id) => api.get(`/v1/barbers/${id}`),
  getServiceZones: () => api.get('/v1/service-zones'),
};
