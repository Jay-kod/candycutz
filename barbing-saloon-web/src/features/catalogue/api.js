import { api } from '@/shared/api/client';

export const catalogueApi = {
  getServices: () => api.get('/v1/services'),
  getService: (idOrSlug) => api.get(`/v1/services/${idOrSlug}`),
  getCategories: () => api.get('/v1/service-categories'),
  getBarbers: () => api.get('/v1/barbers'),
  getBarber: (id) => api.get(`/v1/barbers/${id}`),
  getServiceZones: () => api.get('/v1/service-zones'),
};
