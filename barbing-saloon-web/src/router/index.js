import { createRouter, createWebHistory } from 'vue-router';
import authRoutes from '../modules/auth/routes';
import publicRoutes from '../modules/public/routes';
import customerRoutes from '../modules/customer/routes';
import barberRoutes from '../modules/barber/routes';
import adminRoutes from '../modules/admin/routes';
import superadminRoutes from '../modules/superadmin/routes';

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