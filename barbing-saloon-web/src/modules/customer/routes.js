export default [
  { path: '/customer/dashboard', name: 'customer-dashboard', component: () => import('./pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/bookings', name: 'customer-bookings', component: () => import('./pages/BookingsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/profile', name: 'customer-profile', component: () => import('./pages/ProfilePage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/reviews', name: 'customer-reviews', component: () => import('./pages/ReviewsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/services', name: 'customer-services', component: () => import('./pages/BrowseServicesPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/wishlist', name: 'customer-wishlist', component: () => import('./pages/WishlistPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/gallery', name: 'customer-gallery', component: () => import('./pages/GalleryPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/blog', name: 'customer-blog', component: () => import('./pages/BlogPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/blog/:slug', name: 'customer-blog-post', component: () => import('./pages/BlogPostPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/testimonials', name: 'customer-testimonials', component: () => import('./pages/TestimonialsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
];