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
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition;
    if (to.hash) return { el: to.hash, behavior: 'smooth' };
    return { top: 0 };
  },
});

export default router;