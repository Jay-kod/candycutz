import { ref, computed, onMounted } from 'vue'
import client from '@/shared/api/client'
import { useToast } from '@/core/composables/useToast'

export const SETTINGS_CATEGORIES = [
  { id: 'general', label: 'General & Business' },
  { id: 'booking', label: 'Booking & Policies' },
  { id: 'contact', label: 'Contact & Location' },
  { id: 'social', label: 'Social Channels' },
  { id: 'seo', label: 'SEO & Metadata' },
  { id: 'brand', label: 'Brand Aesthetics' },
  { id: 'other', label: 'Advanced & Core' },
]

export const KEY_METADATA = {
  site_name: { label: 'Platform / Salon Name', placeholder: 'e.g. CandyCutz Studio', type: 'text', category: 'general' },
  brand_name: { label: 'Display Brand Name', placeholder: 'e.g. CandyCutz', type: 'text', category: 'general' },
  site_description: { label: 'Platform Description', placeholder: 'Brief summary of the business', type: 'textarea', category: 'general' },
  brand_tagline: { label: 'Brand Tagline / Slogan', placeholder: 'e.g. Master Grooming & Luxury Barbering', type: 'text', category: 'general' },
  currency: { label: 'Default Currency Code', placeholder: 'e.g. NGN, USD, GBP', type: 'text', category: 'general' },
  timezone: { label: 'Server Timezone', placeholder: 'e.g. Africa/Lagos, UTC', type: 'text', category: 'general' },

  deposit_amount: { label: 'Standard Booking Deposit', placeholder: 'e.g. 2500', type: 'number', category: 'booking' },
  min_notice_hours: { label: 'Minimum Notice (Hours)', placeholder: 'e.g. 2', type: 'number', category: 'booking' },
  max_days_ahead: { label: 'Maximum Advance Booking (Days)', placeholder: 'e.g. 30', type: 'number', category: 'booking' },
  slot_interval_minutes: { label: 'Appointment Slot Interval (Min)', placeholder: 'e.g. 30', type: 'number', category: 'booking' },
  cancellation_cutoff_hours: { label: 'Cancellation Window (Hours)', placeholder: 'e.g. 12', type: 'number', category: 'booking' },

  phone: { label: 'Direct Telephone Line', placeholder: 'e.g. +234 800 000 0000', type: 'text', category: 'contact' },
  email: { label: 'Official Support Email', placeholder: 'e.g. contact@candycutz.com', type: 'text', category: 'contact' },
  address: { label: 'Physical Street Address', placeholder: 'e.g. 14 Luxury Boulevard', type: 'textarea', category: 'contact' },
  city: { label: 'City', placeholder: 'e.g. Victoria Island, Lagos', type: 'text', category: 'contact' },
  state: { label: 'State / Province', placeholder: 'e.g. Lagos State', type: 'text', category: 'contact' },
  map_link: { label: 'Google Maps Location URL', placeholder: 'https://maps.google.com/?q=...', type: 'text', category: 'contact' },
  latitude: { label: 'GPS Latitude', placeholder: 'e.g. 6.4281', type: 'text', category: 'contact' },
  longitude: { label: 'GPS Longitude', placeholder: 'e.g. 3.4219', type: 'text', category: 'contact' },

  instagram: { label: 'Instagram Handle / URL', placeholder: 'e.g. @candycutz or https://instagram.com/...', type: 'text', category: 'social' },
  facebook: { label: 'Facebook Page URL', placeholder: 'https://facebook.com/...', type: 'text', category: 'social' },
  whatsapp: { label: 'WhatsApp Contact Number', placeholder: 'e.g. +234 800 000 0000', type: 'text', category: 'social' },
  twitter: { label: 'X (Twitter) Profile', placeholder: 'e.g. @candycutz', type: 'text', category: 'social' },
  tiktok: { label: 'TikTok Account', placeholder: 'e.g. @candycutz', type: 'text', category: 'social' },
  youtube: { label: 'YouTube Channel', placeholder: 'https://youtube.com/@candycutz', type: 'text', category: 'social' },

  meta_title: { label: 'Default Meta SEO Title', placeholder: 'CandyCutz | Premium Grooming Lounge', type: 'text', category: 'seo' },
  meta_description: { label: 'Default Meta Description', placeholder: 'Experience executive styling and premier cuts…', type: 'textarea', category: 'seo' },
  meta_keywords: { label: 'Meta Search Keywords', placeholder: 'barber, salon, styling, grooming', type: 'text', category: 'seo' },

  brand_name_part_one: { label: 'Brand Wordmark Prefix', placeholder: 'e.g. Candy', type: 'text', category: 'brand' },
  brand_name_part_two: { label: 'Brand Wordmark Suffix', placeholder: 'e.g. Cutz', type: 'text', category: 'brand' },
  brand_name_separator: { label: 'Wordmark Separator (none or space)', placeholder: 'none', type: 'text', category: 'brand' },
  brand_gold_part: { label: 'Accent Highlight Part (first or second)', placeholder: 'second', type: 'text', category: 'brand' },
}

export function useAdminSettings() {
  const toast = useToast()

  const rawSettings = ref({})
  const originalSettings = ref({})
  const loading = ref(true)
  const saving = ref(false)
  const activeTab = ref('general')
  const searchQuery = ref('')

  // ── Infer category for a setting key ──
  const inferCategory = (key) => {
    if (KEY_METADATA[key]?.category) return KEY_METADATA[key].category
    if (key.startsWith('booking_') || key.includes('deposit') || key.includes('notice') || key.includes('slot')) return 'booking'
    if (key.startsWith('contact_') || ['phone', 'email', 'address', 'city', 'state', 'map_link', 'latitude', 'longitude'].includes(key)) return 'contact'
    if (key.startsWith('social_') || ['instagram', 'facebook', 'whatsapp', 'twitter', 'tiktok', 'youtube'].includes(key)) return 'social'
    if (key.startsWith('meta_') || key.startsWith('seo_')) return 'seo'
    if (key.startsWith('brand_') || key.includes('color') || key.includes('logo')) return 'brand'
    if (key.startsWith('site_') || key.includes('currency') || key.includes('timezone') || key.includes('business')) return 'general'
    return 'other'
  }

  // ── Format human-readable key label ──
  const formatKeyLabel = (key) => {
    if (KEY_METADATA[key]?.label) return KEY_METADATA[key].label
    return key
      .replace(/_/g, ' ')
      .replace(/\b\w/g, (c) => c.toUpperCase())
  }

  // ── Fetch Settings ──
  const fetchSettings = async () => {
    loading.value = true
    try {
      const res = await client.get('/admin/settings')
      const data = res.data?.data || res.data || {}

      // Normalize into flat map of key -> string value
      const flat = {}
      for (const [k, v] of Object.entries(data)) {
        if (typeof v === 'object' && v !== null && !Array.isArray(v)) {
          for (const [subK, subV] of Object.entries(v)) {
            flat[subK] = subV ?? ''
          }
        } else if (!Array.isArray(v)) {
          flat[k] = v ?? ''
        }
      }

      // Prepopulate known fields with empty strings if not present
      for (const key of Object.keys(KEY_METADATA)) {
        if (!(key in flat)) flat[key] = ''
      }

      rawSettings.value = { ...flat }
      originalSettings.value = { ...flat }
    } catch (err) {
      console.error('Failed to load settings:', err)
      toast.error('Failed to retrieve system settings')
    } finally {
      loading.value = false
    }
  }

  // ── Save Settings ──
  const saveSettings = async () => {
    saving.value = true
    try {
      const payload = { settings: {} }
      for (const [key, value] of Object.entries(rawSettings.value)) {
        payload.settings[key] = value ?? ''
      }

      await client.post('/admin/settings', payload)
      originalSettings.value = { ...rawSettings.value }
      toast.success('System configuration saved successfully')
    } catch (err) {
      console.error('Failed to save settings:', err)
      const msg = err.response?.data?.message || 'Failed to update system settings'
      toast.error(msg)
    } finally {
      saving.value = false
    }
  }

  // ── Revert Changes ──
  const revertChanges = () => {
    rawSettings.value = { ...originalSettings.value }
    toast.info('Discarded unsaved configuration changes')
  }

  // ── Dirty State ──
  const isDirty = computed(() => {
    return JSON.stringify(rawSettings.value) !== JSON.stringify(originalSettings.value)
  })

  // ── Categorized & Filtered Settings ──
  const categorizedSettings = computed(() => {
    const categories = {}
    for (const cat of SETTINGS_CATEGORIES) {
      categories[cat.id] = []
    }

    const query = searchQuery.value.trim().toLowerCase()

    for (const [key, value] of Object.entries(rawSettings.value)) {
      if (['mobile', 'animation_resolved'].includes(key)) continue
      const cat = inferCategory(key)
      const label = formatKeyLabel(key)
      const meta = KEY_METADATA[key] || {}

      if (query) {
        const matchesKey = key.toLowerCase().includes(query)
        const matchesLabel = label.toLowerCase().includes(query)
        const matchesVal = String(value).toLowerCase().includes(query)
        if (!matchesKey && !matchesLabel && !matchesVal) continue
      }

      if (!categories[cat]) categories[cat] = []
      categories[cat].push({
        key,
        value,
        label,
        type: meta.type || 'text',
        placeholder: meta.placeholder || `Enter ${label}…`,
      })
    }

    return categories
  })

  // ── Category Counts ──
  const categoryCounts = computed(() => {
    const counts = {}
    for (const [catId, fields] of Object.entries(categorizedSettings.value)) {
      counts[catId] = fields.length
    }
    return counts
  })

  onMounted(fetchSettings)

  return {
    rawSettings,
    originalSettings,
    loading,
    saving,
    activeTab,
    searchQuery,
    isDirty,
    categorizedSettings,
    categoryCounts,
    fetchSettings,
    saveSettings,
    revertChanges,
    formatKeyLabel,
  }
}

