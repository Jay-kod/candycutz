import client from '@/shared/api/client';

export const superadminApi = {
  dashboard: () => client.get('/super-admin/dashboard'),
  users: () => client.get('/super-admin/users'),
  activateUser: (id) => client.patch(`/super-admin/users/${id}/activate`),
  deactivateUser: (id) => client.patch(`/super-admin/users/${id}/deactivate`),
  resetPassword: (id, password, password_confirmation) => client.post(`/super-admin/users/${id}/reset-password`, { password, password_confirmation }),
  settings: () => client.get('/super-admin/settings'),
  updateSettings: (data) => client.post('/super-admin/settings', data),
  auditLogs: () => client.get('/super-admin/audit-logs'),
};
