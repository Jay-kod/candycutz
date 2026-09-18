import { createRouter, createWebHistory } from 'vue-router';
import authRoutes from '../portals/auth/routes';
import publicRoutes from '../portals/public/routes';
import customerRoutes from '../portals/customer/routes';
import barberRoutes from '../portals/barber/routes';
import adminRoutes from '../portals/admin/routes';
import superadminRoutes from '../portals/superadmin/routes';

const routes = [
  ...publicRoutes,
  ...customerRoutes,
  ...barberRoutes,
  ...adminRoutes,
  ...superadminRoutes,
  ...authRoutes,
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;