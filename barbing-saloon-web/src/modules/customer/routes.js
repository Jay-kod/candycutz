export default [
  { path: '/customer/dashboard', name: 'customer-dashboard', component: () => import('./pages/DashboardPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/bookings', name: 'customer-bookings', component: () => import('./pages/BookingsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/my-codes', name: 'customer-my-codes', component: () => import('./pages/MyCodesPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/profile', name: 'customer-profile', component: () => import('./pages/ProfilePage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/reviews', name: 'customer-reviews', component: () => import('./pages/ReviewsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/services', name: 'customer-services', component: () => import('./pages/BrowseServicesPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/services/:id', name: 'customer-service-details', component: () => import('./pages/ServiceDetailsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/book/:serviceId', name: 'customer-book-service', component: () => import('./pages/BookServicePage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/wishlist', name: 'customer-wishlist', component: () => import('./pages/WishlistPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/gallery', name: 'customer-gallery', component: () => import('./pages/GalleryPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/blog', name: 'customer-blog', component: () => import('./pages/BlogPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/blog/:slug', name: 'customer-blog-post', component: () => import('./pages/BlogPostPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },

  { path: '/customer/dashboard/notifications', name: 'customer-notifications', component: () => import('./pages/NotificationsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/notification-settings', name: 'customer-notification-settings', component: () => import('./pages/NotificationSettingsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/analytics', name: 'customer-analytics', component: () => import('./pages/AnalyticsPage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
  { path: '/customer/dashboard/barber/:id', name: 'customer-barber-profile', component: () => import('./pages/BarberProfilePage.vue'), meta: { requiresAuth: true, roles: ['customer'] } },
];