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
  Cog6ToothIcon
} from '@heroicons/vue/24/outline'

export const timeFilters = [
  { value: 'all', label: 'All Time', icon: ClockIcon },
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
]

export const typeFilters = [
  { value: 'all', label: 'All Types', icon: TagIcon },
  { value: 'booking', label: 'Bookings', color: 'text-[var(--color-accent)]', bg: 'bg-[var(--color-accent)]/10 border-[var(--color-accent)]/20' },
  { value: 'payment', label: 'Payments', color: 'text-cyan-600 dark:text-cyan-400', bg: 'bg-cyan-500/10 border-cyan-500/20' },
  { value: 'system_update', label: 'System', color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-500/10 border-blue-500/20' },
  { value: 'wishlist_update', label: 'Wishlist', color: 'text-rose-600 dark:text-rose-400', bg: 'bg-rose-500/10 border-rose-500/20' },
  { value: 'blog_update', label: 'Blog', color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-500/10 border-emerald-500/20' },
  { value: 'general_update', label: 'General', color: 'text-purple-600 dark:text-purple-400', bg: 'bg-purple-500/10 border-purple-500/20' },
  { value: 'review', label: 'Reviews', color: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-500/10 border-amber-500/20' },
  { value: 'review_approved', label: 'Approved Reviews', color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-500/10 border-emerald-500/20' },
  { value: 'category', label: 'Category Requests', color: 'text-indigo-600 dark:text-indigo-400', bg: 'bg-indigo-500/10 border-indigo-500/20' },
  { value: 'feedback', label: 'Feedback', color: 'text-sky-600 dark:text-sky-400', bg: 'bg-sky-500/10 border-sky-500/20' },
]

export const formatRelativeTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)
  if (diffInSeconds < 60) return 'Just now'
  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}h ago`
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays === 1) return 'Yesterday'
  if (diffInDays < 7) return `${diffInDays}d ago`
  return date.toLocaleDateString()
}

export const formatFullDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) + ' at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

export const getIconForType = (type) => {
  const t = (type || '').toLowerCase()
  if (t.startsWith('booking')) return CalendarDaysIcon
  if (t.startsWith('payment')) return CreditCardIcon
  if (t.startsWith('category')) return TagIcon
  if (t.startsWith('feedback')) return ChatBubbleLeftRightIcon
  switch (t) {
    case 'system_update': return UserIcon
    case 'wishlist_update': return HeartIcon
    case 'blog_update': return DocumentTextIcon
    case 'general_update': return Cog6ToothIcon
    case 'review':
    case 'review_submitted': return ChatBubbleLeftRightIcon
    case 'review_approved': return StarIcon
    default: return BellIcon
  }
}

export const getLabelForType = (type) => {
  const t = (type || '').toLowerCase()
  switch (t) {
    case 'booking': return 'Booking'
    case 'booking_created': return 'New Booking'
    case 'booking_approved': return 'Approved'
    case 'booking_cancelled': return 'Cancelled'
    case 'booking_completed': return 'Completed'
    case 'booking_no_show': return 'No-Show'
    case 'booking_checked_in': return 'Checked In'
    case 'booking_in_progress': return 'In Progress'
    case 'payment': return 'Payment'
    case 'payment_received': return 'Payment Receipt'
    case 'payment_verified': return 'Confirmed'
    case 'payment_failed': return 'Failed'
    case 'system_update': return 'System'
    case 'wishlist_update': return 'Wishlist'
    case 'blog_update': return 'Blog'
    case 'general_update': return 'General'
    case 'review':
    case 'review_submitted': return 'Review'
    case 'review_approved': return 'Approved'
    case 'category_requested': return 'Category Request'
    case 'category_status_changed': return 'Category Update'
    case 'feedback_submitted': return 'Feedback'
    default: return 'Notification'
  }
}

export const getIconColorForType = (type) => {
  const t = (type || '').toLowerCase()
  if (t === 'booking_cancelled' || t === 'booking_no_show' || t === 'payment_failed') {
    return 'text-[var(--color-danger)]'
  }
  if (t === 'booking_approved' || t === 'booking_completed' || t === 'payment_verified' || t === 'review_approved') {
    return 'text-emerald-600 dark:text-emerald-400'
  }
  if (t.startsWith('booking')) {
    return 'text-[var(--color-accent)]'
  }
  if (t.startsWith('payment')) {
    return 'text-cyan-600 dark:text-cyan-400'
  }
  if (t.startsWith('category')) {
    return 'text-indigo-600 dark:text-indigo-400'
  }
  if (t.startsWith('feedback')) {
    return 'text-sky-600 dark:text-sky-400'
  }
  switch (t) {
    case 'system_update': return 'text-blue-600 dark:text-blue-400'
    case 'wishlist_update': return 'text-rose-600 dark:text-rose-400'
    case 'blog_update': return 'text-emerald-600 dark:text-emerald-400'
    case 'general_update': return 'text-purple-600 dark:text-purple-400'
    case 'review':
    case 'review_submitted': return 'text-amber-600 dark:text-amber-400'
    default: return 'text-[var(--color-text-secondary)]'
  }
}

export const getBgColorForType = (type) => {
  const t = (type || '').toLowerCase()
  if (t === 'booking_cancelled' || t === 'booking_no_show' || t === 'payment_failed') {
    return 'bg-red-500/10 border-red-500/20 text-red-600 dark:text-red-400'
  }
  if (t === 'booking_approved' || t === 'booking_completed' || t === 'payment_verified' || t === 'review_approved') {
    return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'
  }
  if (t.startsWith('booking')) {
    return 'bg-[var(--color-accent)]/10 border-[var(--color-accent)]/20 text-[var(--color-accent)]'
  }
  if (t.startsWith('payment')) {
    return 'bg-cyan-500/10 border-cyan-500/20 text-cyan-600 dark:text-cyan-400'
  }
  if (t.startsWith('category')) {
    return 'bg-indigo-500/10 border-indigo-500/20 text-indigo-600 dark:text-indigo-400'
  }
  if (t.startsWith('feedback')) {
    return 'bg-sky-500/10 border-sky-500/20 text-sky-600 dark:text-sky-400'
  }
  switch (t) {
    case 'system_update': return 'bg-blue-500/10 border-blue-500/20 text-blue-600 dark:text-blue-400'
    case 'wishlist_update': return 'bg-rose-500/10 border-rose-500/20 text-rose-600 dark:text-rose-400'
    case 'blog_update': return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'
    case 'general_update': return 'bg-purple-500/10 border-purple-500/20 text-purple-600 dark:text-purple-400'
    case 'review':
    case 'review_submitted': return 'bg-amber-500/10 border-amber-500/20 text-amber-600 dark:text-amber-400'
    default: return 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-secondary)]'
  }
}
