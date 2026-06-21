import client from '../../../core/api/axios';

export const superadminApi = {
  dashboard: () => client.get('/super-admin/dashboard'),
  users: () => client.get('/super-admin/users'),
  activateUser: (id) => client.patch(`/super-admin/users/${id}/activate`),
  deactivateUser: (id) => client.patch(`/super-admin/users/${id}/deactivate`),
  settings: () => client.get('/super-admin/settings'),
  updateSettings: (data) => client.post('/super-admin/settings', data),
  auditLogs: () => client.get('/super-admin/audit-logs'),
};
