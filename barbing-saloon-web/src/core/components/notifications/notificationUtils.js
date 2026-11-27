import {
  BellIcon,
  CalendarDaysIcon,
  UserIcon,
  HeartIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  StarIcon,
  ClockIcon,
  TagIcon,
  CreditCardIcon,
  Cog6ToothIcon,
  MegaphoneIcon,
  ShieldExclamationIcon,
  SparklesIcon,
} from '@heroicons/vue/24/outline';

export const timeFilters = [
  { value: 'all', label: 'All Time', icon: ClockIcon },
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
];

export const typeFilters = [
  { value: 'all', label: 'All Types', icon: TagIcon },
  {
    value: 'booking',
    label: 'Bookings',
    color: 'text-[var(--color-accent)]',
    bg: 'bg-[var(--color-accent)]/10 border-[var(--color-accent)]/30',
  },
  {
    value: 'payment',
    label: 'Payments',
    color: 'text-cyan-600 dark:text-cyan-400',
    bg: 'bg-cyan-500/10 border-cyan-500/30',
  },
  {
    value: 'review',
    label: 'Reviews',
    color: 'text-amber-600 dark:text-amber-400',
    bg: 'bg-amber-500/10 border-amber-500/30',
  },
  {
    value: 'system',
    label: 'System & Alerts',
    color: 'text-blue-600 dark:text-blue-400',
    bg: 'bg-blue-500/10 border-blue-500/30',
  },
  {
    value: 'wishlist_update',
    label: 'Wishlist',
    color: 'text-rose-600 dark:text-rose-400',
    bg: 'bg-rose-500/10 border-rose-500/30',
  },
  {
    value: 'blog_update',
    label: 'Blog',
    color: 'text-emerald-600 dark:text-emerald-400',
    bg: 'bg-emerald-500/10 border-emerald-500/30',
  },
  {
    value: 'category',
    label: 'Category Requests',
    color: 'text-indigo-600 dark:text-indigo-400',
    bg: 'bg-indigo-500/10 border-indigo-500/30',
    staffOnly: true,
  },
  {
    value: 'feedback',
    label: 'Feedback',
    color: 'text-sky-600 dark:text-sky-400',
    bg: 'bg-sky-500/10 border-sky-500/30',
    staffOnly: true,
  },
];

export const formatRelativeTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return '';

  const now = new Date();
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
  if (diffInSeconds < 60) return 'Just now';
  const diffInMinutes = Math.floor(diffInSeconds / 60);
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
  const diffInHours = Math.floor(diffInMinutes / 60);
  if (diffInHours < 24) return `${diffInHours}h ago`;
  const diffInDays = Math.floor(diffInHours / 24);
  if (diffInDays === 1) return 'Yesterday';
  if (diffInDays < 7) return `${diffInDays}d ago`;
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

export const formatTimeOnly = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
};

export const formatFullDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return '';
  return (
    date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) +
    ' at ' +
    date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  );
};

export const getIconForType = (type) => {
  const t = (type || '').toLowerCase();
  if (t.startsWith('booking') || t === 'barber_assigned') return CalendarDaysIcon;
  if (t.startsWith('payment') || t.startsWith('refund')) return CreditCardIcon;
  if (t.startsWith('category')) return TagIcon;
  if (t.startsWith('feedback')) return ChatBubbleLeftRightIcon;
  if (t === 'password_changed' || t === 'security_alert') return ShieldExclamationIcon;
  if (t === 'announcement' || t === 'system_announcement' || t === 'broadcast') return MegaphoneIcon;
  if (t === 'promotion' || t === 'promotional' || t === 'account_created') return SparklesIcon;
  switch (t) {
    case 'system_update':
      return UserIcon;
    case 'wishlist_update':
      return HeartIcon;
    case 'blog_update':
      return DocumentTextIcon;
    case 'general_update':
      return Cog6ToothIcon;
    case 'review':
    case 'review_submitted':
      return ChatBubbleLeftRightIcon;
    case 'review_approved':
      return StarIcon;
    default:
      return BellIcon;
  }
};

export const getLabelForType = (type) => {
  const t = (type || '').toLowerCase();
  switch (t) {
    case 'booking':
      return 'Booking';
    case 'booking_created':
      return 'New Booking';
    case 'booking_approved':
      return 'Approved';
    case 'booking_cancelled':
      return 'Cancelled';
    case 'booking_completed':
      return 'Completed';
    case 'booking_no_show':
      return 'No-Show';
    case 'booking_checked_in':
      return 'Checked In';
    case 'booking_in_progress':
      return 'In Progress';
    case 'booking_reminder':
      return 'Reminder';
    case 'barber_assigned':
      return 'Barber Assigned';
    case 'payment':
      return 'Payment';
    case 'payment_received':
      return 'Payment Receipt';
    case 'payment_verified':
      return 'Confirmed';
    case 'payment_failed':
      return 'Failed';
    case 'refund_initiated':
      return 'Refund Pending';
    case 'refund_completed':
      return 'Refunded';
    case 'account_created':
      return 'Welcome';
    case 'password_changed':
    case 'security_alert':
      return 'Security';
    case 'announcement':
    case 'system_announcement':
    case 'broadcast':
      return 'Announcement';
    case 'promotion':
    case 'promotional':
      return 'Offer';
    case 'system_update':
      return 'System';
    case 'wishlist_update':
      return 'Wishlist';
    case 'blog_update':
      return 'Blog';
    case 'general_update':
      return 'General';
    case 'review':
    case 'review_submitted':
      return 'Review';
    case 'review_approved':
      return 'Approved';
    case 'category_requested':
      return 'Category Request';
    case 'category_status_changed':
      return 'Category Update';
    case 'feedback_submitted':
      return 'Feedback';
    default:
      return 'Notification';
  }
};

export const getIconColorForType = (type) => {
  const t = (type || '').toLowerCase();
  if (
    t === 'booking_cancelled' ||
    t === 'booking_no_show' ||
    t === 'payment_failed' ||
    t === 'security_alert' ||
    t === 'password_changed'
  ) {
    return 'text-[var(--color-danger)]';
  }
  if (
    t === 'booking_approved' ||
    t === 'booking_completed' ||
    t === 'payment_verified' ||
    t === 'review_approved' ||
    t === 'refund_completed'
  ) {
    return 'text-emerald-600 dark:text-emerald-400';
  }
  if (t.startsWith('booking') || t === 'barber_assigned') {
    return 'text-[var(--color-accent)]';
  }
  if (t.startsWith('payment') || t.startsWith('refund')) {
    return 'text-cyan-600 dark:text-cyan-400';
  }
  if (t.startsWith('category')) {
    return 'text-indigo-600 dark:text-indigo-400';
  }
  if (t.startsWith('feedback')) {
    return 'text-sky-600 dark:text-sky-400';
  }
  switch (t) {
    case 'system_update':
    case 'announcement':
    case 'system_announcement':
    case 'broadcast':
    case 'account_created':
      return 'text-blue-600 dark:text-blue-400';
    case 'wishlist_update':
      return 'text-rose-600 dark:text-rose-400';
    case 'blog_update':
      return 'text-emerald-600 dark:text-emerald-400';
    case 'general_update':
      return 'text-purple-600 dark:text-purple-400';
    case 'review':
    case 'review_submitted':
    case 'promotion':
    case 'promotional':
      return 'text-amber-600 dark:text-amber-400';
    default:
      return 'text-[var(--color-text-secondary)]';
  }
};

export const getBgColorForType = (type) => {
  const t = (type || '').toLowerCase();
  if (
    t === 'booking_cancelled' ||
    t === 'booking_no_show' ||
    t === 'payment_failed' ||
    t === 'security_alert' ||
    t === 'password_changed'
  ) {
    return 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400';
  }
  if (
    t === 'booking_approved' ||
    t === 'booking_completed' ||
    t === 'payment_verified' ||
    t === 'review_approved' ||
    t === 'refund_completed'
  ) {
    return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400';
  }
  if (t.startsWith('booking') || t === 'barber_assigned') {
    return 'bg-[var(--color-accent)]/10 border-[var(--color-accent)]/20 text-[var(--color-accent)]';
  }
  if (t.startsWith('payment') || t.startsWith('refund')) {
    return 'bg-cyan-500/10 border-cyan-500/20 text-cyan-600 dark:text-cyan-400';
  }
  if (t.startsWith('category')) {
    return 'bg-indigo-500/10 border-indigo-500/20 text-indigo-600 dark:text-indigo-400';
  }
  if (t.startsWith('feedback')) {
    return 'bg-sky-500/10 border-sky-500/20 text-sky-600 dark:text-sky-400';
  }
  switch (t) {
    case 'system_update':
    case 'announcement':
    case 'system_announcement':
    case 'broadcast':
    case 'account_created':
      return 'bg-blue-500/10 border-blue-500/20 text-blue-600 dark:text-blue-400';
    case 'wishlist_update':
      return 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400';
    case 'blog_update':
      return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400';
    case 'general_update':
      return 'bg-purple-500/10 border-purple-500/20 text-purple-600 dark:text-purple-400';
    case 'review':
    case 'review_submitted':
    case 'promotion':
    case 'promotional':
      return 'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400';
    default:
      return 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-secondary)]';
  }
};

export const matchesTypeFilter = (notif, filterValue) => {
  if (!filterValue || filterValue === 'all') return true;
  const t = (notif?.type || '').toLowerCase();
  const cat = (notif?.category || '').toLowerCase();

  switch (filterValue) {
    case 'booking':
      return t === 'booking' || t.startsWith('booking_') || t === 'barber_assigned' || cat === 'booking';
    case 'payment':
      return t === 'payment' || t.startsWith('payment_') || t.startsWith('refund_') || cat === 'payment';
    case 'review':
      return t === 'review' || t.startsWith('review_') || cat === 'review';
    case 'system':
    case 'system_update':
      return (
        t === 'system_update' ||
        t.startsWith('system_') ||
        t === 'announcement' ||
        t === 'broadcast' ||
        t === 'promotion' ||
        t === 'promotional' ||
        t === 'account_created' ||
        t === 'password_changed' ||
        t === 'security_alert' ||
        t === 'general_update' ||
        cat === 'security' ||
        cat === 'announcement'
      );
    case 'category':
      return t === 'category' || t.startsWith('category_');
    case 'feedback':
      return t === 'feedback' || t.startsWith('feedback_');
    default:
      return t === filterValue;
  }
};

export const matchesTimeFilter = (notif, filterValue, now = new Date()) => {
  if (!filterValue || filterValue === 'all') return true;
  if (!notif?.created_at) return false;
  const d = new Date(notif.created_at);
  if (Number.isNaN(d.getTime())) return false;

  const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const startOfYesterday = new Date(startOfToday);
  startOfYesterday.setDate(startOfYesterday.getDate() - 1);

  switch (filterValue) {
    case 'today':
      return d >= startOfToday;
    case 'yesterday':
      return d >= startOfYesterday && d < startOfToday;
    case 'week': {
      const startOfWeek = new Date(startOfToday);
      startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
      return d >= startOfWeek;
    }
    case 'month': {
      const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
      return d >= startOfMonth;
    }
    default:
      return true;
  }
};

export const extractNotificationMeta = (notif) => {
  let raw = notif?.data;
  if (typeof raw === 'string') {
    try {
      raw = JSON.parse(raw);
    } catch {
      raw = null;
    }
  }
  const data = raw && typeof raw === 'object' ? raw : {};

  return {
    bookingReference: data.booking_reference || null,
    verificationCode: data.verification_code || null,
    amount: data.amount ? String(data.amount) : null,
    barberName: data.barber_name || null,
    serviceName: data.service_name || null,
    paymentReference: data.payment_reference || null,
    appointmentDate: data.appointment_date || null,
    appointmentTime: data.appointment_time || null,
    reason: data.reason || null,
  };
};

export const matchesSearchQuery = (notif, query) => {
  const q = (query || '').trim().toLowerCase();
  if (!q) return true;

  const meta = extractNotificationMeta(notif);
  const haystack = [
    notif?.title,
    notif?.message,
    getLabelForType(notif?.type),
    meta.bookingReference,
    meta.verificationCode,
    meta.barberName,
    meta.serviceName,
    meta.paymentReference,
  ]
    .filter(Boolean)
    .join(' ')
    .toLowerCase();

  return haystack.includes(q);
};

function normalizeInternalActionUrl(actionUrl, currentPath) {
  if (!actionUrl || typeof actionUrl !== 'string') return null;
  const trimmed = actionUrl.trim();
  // Only allow safe internal relative routes
  if (!trimmed.startsWith('/') || trimmed.startsWith('//')) return null;

  const [rawPath, query] = trimmed.split('?');
  const suffix = query ? `?${query}` : '';

  if (currentPath.startsWith('/barber')) {
    if (rawPath.startsWith('/barber/appointments')) return `/barber/appointments${suffix}`;
    if (rawPath === '/barber/categories') return `/barber/service-categories${suffix}`;
    if (rawPath === '/account/profile') return '/barber/profile';
    return `${rawPath}${suffix}`;
  }

  if (currentPath.startsWith('/admin')) {
    if (rawPath.startsWith('/admin/appointments')) return `/admin/appointments${suffix}`;
    if (rawPath === '/admin/categories') return `/admin/service-categories${suffix}`;
    if (rawPath === '/account/profile') return '/admin/settings';
    return `${rawPath}${suffix}`;
  }

  // Customer portal route normalization (fixes legacy /customer/bookings/:id, /customer/reviews, /account/profile)
  if (/^\/customer\/bookings(\/\d+)?$/.test(rawPath) || /^\/customer\/dashboard\/appointments(\/\d+)?$/.test(rawPath)) {
    return `/customer/dashboard/bookings${suffix}`;
  }
  if (rawPath === '/customer/reviews') {
    return `/customer/dashboard/reviews${suffix}`;
  }
  if (rawPath === '/customer/wishlist') {
    return `/customer/dashboard/wishlist${suffix}`;
  }
  if (rawPath === '/account/profile') {
    return '/customer/dashboard/profile';
  }
  if (rawPath === '/booking') {
    return '/customer/dashboard/services';
  }

  return `${rawPath}${suffix}`;
}

export const groupNotificationsByDate = (items, now = new Date()) => {
  const groups = [];
  let currentLabel = '';
  const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const startOfYesterday = new Date(startOfToday);
  startOfYesterday.setDate(startOfYesterday.getDate() - 1);
  const startOfWeek = new Date(startOfToday);
  startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());

  for (const n of items || []) {
    const d = new Date(n.created_at);
    let label;
    if (Number.isNaN(d.getTime())) {
      label = 'Recent';
    } else if (d >= startOfToday) {
      label = 'Today';
    } else if (d >= startOfYesterday) {
      label = 'Yesterday';
    } else if (d >= startOfWeek) {
      label = 'Earlier This Week';
    } else {
      label = d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    }

    if (label !== currentLabel) {
      currentLabel = label;
      groups.push({ label, items: [] });
    }
    groups[groups.length - 1].items.push(n);
  }
  return groups;
};

export const resolveNotificationTarget = (notif, currentPath = '') => {
  const t = (notif?.type || '').toLowerCase();
  const id = notif?.related_entity_id;
  const normalizedUrl = normalizeInternalActionUrl(notif?.action_url, currentPath);

  const labelFor = () => {
    if (t === 'booking_completed') return 'Leave a Review';
    if (t === 'payment_failed') return 'Retry Payment';
    if (t === 'review_approved' || t.startsWith('review')) return 'View Reviews';
    if (t.startsWith('payment') || t.startsWith('refund')) return 'View Bookings';
    if (t.startsWith('booking') || t === 'barber_assigned') return 'View Details';
    if (t === 'password_changed' || t === 'security_alert') return 'Account Security';
    if (t === 'wishlist_update') return 'View Wishlist';
    if (t === 'blog_update') return 'Read Blog';
    return 'Open Link';
  };

  if (normalizedUrl) {
    return { to: normalizedUrl, label: labelFor() };
  }

  if (currentPath.startsWith('/admin')) {
    if (t.includes('booking') || t.includes('appointment') || t.includes('payment') || t.includes('refund')) {
      return { to: '/admin/appointments', label: 'View Appointments' };
    }
    if (t.includes('review') || t.includes('testimonial')) {
      return { to: '/admin/testimonials', label: 'Moderate Reviews' };
    }
    if (t.includes('category')) {
      return { to: '/admin/service-categories', label: 'Service Categories' };
    }
    if (t.includes('feedback')) {
      return { to: '/admin/feedback', label: 'View Feedback' };
    }
    return null;
  }

  if (currentPath.startsWith('/barber')) {
    if (t.includes('booking') || t.includes('appointment') || t === 'barber_assigned') {
      return { to: '/barber/appointments', label: 'View Appointments' };
    }
    if (t.includes('payment')) {
      return { to: '/barber/payments', label: 'Verify Payments' };
    }
    if (t.includes('review')) {
      return { to: '/barber/reviews', label: 'View Reviews' };
    }
    if (t.includes('category')) {
      return { to: '/barber/service-categories', label: 'Service Categories' };
    }
    if (t === 'password_changed' || t === 'security_alert') {
      return { to: '/barber/profile', label: 'Account Security' };
    }
    return null;
  }

  // Customer portal defaults
  if (t === 'booking_completed') {
    return {
      to: id ? `/customer/dashboard/reviews?appointment_id=${id}` : '/customer/dashboard/reviews',
      label: 'Leave a Review',
    };
  }
  if (t === 'payment_failed') {
    return {
      to: id ? `/customer/dashboard/payment/${id}` : '/customer/dashboard/bookings',
      label: 'Retry Payment',
    };
  }
  if (t.includes('booking') || t.includes('appointment') || t === 'barber_assigned' || t.startsWith('payment') || t.startsWith('refund')) {
    return { to: '/customer/dashboard/bookings', label: 'View Bookings' };
  }
  if (t.includes('review')) {
    return { to: '/customer/dashboard/reviews', label: 'View Reviews' };
  }
  if (t === 'wishlist_update') {
    return { to: '/customer/dashboard/wishlist', label: 'View Wishlist' };
  }
  if (t === 'blog_update') {
    return { to: '/customer/dashboard/blog', label: 'Read Blog' };
  }
  if (t === 'password_changed' || t === 'security_alert' || t === 'account_created') {
    return { to: '/customer/dashboard/profile', label: 'Account Security' };
  }

  return null;
};
