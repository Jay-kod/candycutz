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
  { value: 'booking', label: 'Bookings', color: 'text-gold', bg: 'bg-gold/10 border-gold/20' },
  { value: 'payment', label: 'Payments', color: 'text-cyan-400', bg: 'bg-cyan-400/10 border-cyan-400/20' },
  { value: 'system_update', label: 'System', color: 'text-blue-400', bg: 'bg-blue-400/10 border-blue-400/20' },
  { value: 'wishlist_update', label: 'Wishlist', color: 'text-rose-400', bg: 'bg-rose-400/10 border-rose-400/20' },
  { value: 'blog_update', label: 'Blog', color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-400/20' },
  { value: 'general_update', label: 'General', color: 'text-purple-400', bg: 'bg-purple-400/10 border-purple-400/20' },
  { value: 'review', label: 'Reviews', color: 'text-amber-400', bg: 'bg-amber-400/10 border-amber-400/20' },
  { value: 'review_approved', label: 'Approved', color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-400/20' },
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
  switch (type) {
    case 'booking': return CalendarDaysIcon
    case 'payment': return CreditCardIcon
    case 'system_update': return UserIcon
    case 'wishlist_update': return HeartIcon
    case 'blog_update': return DocumentTextIcon
    case 'general_update': return Cog6ToothIcon
    case 'review': return ChatBubbleLeftRightIcon
    case 'review_approved': return StarIcon
    default: return BellIcon
  }
}

export const getLabelForType = (type) => {
  switch (type) {
    case 'booking': return 'Booking'
    case 'payment': return 'Payment'
    case 'system_update': return 'System'
    case 'wishlist_update': return 'Wishlist'
    case 'blog_update': return 'Blog'
    case 'general_update': return 'General'
    case 'review': return 'Review'
    case 'review_approved': return 'Approved'
    default: return 'Notification'
  }
}

export const getIconColorForType = (type) => {
  switch (type) {
    case 'booking': return 'text-gold'
    case 'payment': return 'text-cyan-400'
    case 'system_update': return 'text-blue-400'
    case 'wishlist_update': return 'text-rose-400'
    case 'blog_update': return 'text-emerald-400'
    case 'general_update': return 'text-purple-400'
    case 'review': return 'text-amber-400'
    case 'review_approved': return 'text-emerald-400'
    default: return 'text-theme-muted'
  }
}

export const getBgColorForType = (type) => {
  switch (type) {
    case 'booking': return 'bg-gold/10 border-gold/20'
    case 'payment': return 'bg-cyan-400/10 border-cyan-400/20'
    case 'system_update': return 'bg-blue-400/10 border-blue-400/20'
    case 'wishlist_update': return 'bg-rose-400/10 border-rose-400/20'
    case 'blog_update': return 'bg-emerald-400/10 border-emerald-400/20'
    case 'general_update': return 'bg-purple-400/10 border-purple-400/20'
    case 'review': return 'bg-amber-400/10 border-amber-400/20'
    case 'review_approved': return 'bg-emerald-400/10 border-emerald-400/20'
    default: return 'bg-theme-border/30 border-theme-border/50'
  }
}
