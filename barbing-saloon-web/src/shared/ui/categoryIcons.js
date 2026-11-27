import {
  Scissors,
  ScissorsLineDashed,
  Sparkles,
  Sparkle,
  Crown,
  Flame,
  Award,
  Star,
  Gem,
  Trophy,
  Shield,
  ShieldCheck,
  Heart,
  Zap,
  Camera,
  Image,
  Brush,
  Palette,
  Wand2,
  Feather,
  Droplet,
  SprayCan,
  User,
  Users,
  Smile,
  Clock,
  Calendar,
  Tag,
  Gift,
  Shirt,
  Watch,
  Coffee,
  Sun,
  Moon,
  Eye,
  Layers,
  Compass,
  Bookmark,
  CheckCircle,
  BadgeCheck,
  Building,
  Store,
  ShoppingBag,
  TrendingUp,
  ThumbsUp,
  Briefcase,
} from 'lucide-vue-next';

export const ICON_COMPONENTS = {
  // Barber & Grooming
  scissors: Scissors,
  'scissors-dashed': ScissorsLineDashed,
  haircut: Scissors,
  cut: Scissors,
  trim: Scissors,
  brush: Brush,
  spray: SprayCan,
  'spray-can': SprayCan,
  droplet: Droplet,
  water: Droplet,
  shave: Sparkles,
  feather: Feather,

  // Luxury & VIP
  crown: Crown,
  vip: Crown,
  gem: Gem,
  diamond: Gem,
  trophy: Trophy,
  award: Award,
  star: Star,
  sparkles: Sparkles,
  sparkle: Sparkle,
  flame: Flame,
  fire: Flame,
  badge: BadgeCheck,
  'badge-check': BadgeCheck,

  // Media & Creative
  camera: Camera,
  photo: Camera,
  gallery: Camera,
  image: Image,
  wand: Wand2,
  palette: Palette,
  art: Palette,
  layers: Layers,
  eye: Eye,

  // People & Community
  user: User,
  'user-group': Users,
  users: Users,
  family: Users,
  kids: Smile,
  smile: Smile,
  heart: Heart,
  thumbsup: ThumbsUp,
  'thumbs-up': ThumbsUp,

  // Lifestyle & Atmosphere
  store: Store,
  building: Building,
  shop: Building,
  lounge: Coffee,
  coffee: Coffee,
  shirt: Shirt,
  style: Shirt,
  watch: Watch,
  sun: Sun,
  moon: Moon,
  night: Moon,

  // General, Time & Badges
  clock: Clock,
  calendar: Calendar,
  zap: Zap,
  speed: Zap,
  fast: Zap,
  shield: Shield,
  'shield-check': ShieldCheck,
  tag: Tag,
  gift: Gift,
  package: Gift,
  bag: ShoppingBag,
  briefcase: Briefcase,
  trending: TrendingUp,
  compass: Compass,
  bookmark: Bookmark,
  check: CheckCircle,
  'check-circle': CheckCircle,
};

export const ICON_GROUPS = [
  { key: 'all', name: 'All' },
  { key: 'barber', name: 'Barber & Hair' },
  { key: 'luxury', name: 'VIP & Luxury' },
  { key: 'media', name: 'Media & Photo' },
  { key: 'lounge', name: 'Lounge & Style' },
  { key: 'general', name: 'Badges & Time' },
];

export const ICONS_CATALOG = [
  // Barber & Haircuts
  { key: 'scissors', name: 'Scissors', group: 'barber', tags: 'cut hair trim barbering shears' },
  { key: 'scissors-dashed', name: 'Detail Cut', group: 'barber', tags: 'scissors line fade trim' },
  { key: 'brush', name: 'Color Brush', group: 'barber', tags: 'dye color highlight tint brush' },
  { key: 'spray-can', name: 'Spray Can', group: 'barber', tags: 'mist aerosol spray hold styling' },
  { key: 'droplet', name: 'Wash & Oil', group: 'barber', tags: 'water shampoo treatment oil care' },
  { key: 'feather', name: 'Smooth Finish', group: 'barber', tags: 'gentle feather soft skin light' },
  { key: 'user', name: 'Men & Cuts', group: 'barber', tags: 'client person adult male styling' },
  { key: 'user-group', name: 'Junior & Group', group: 'barber', tags: 'kids youth family student cuts' },
  { key: 'smile', name: 'Kids Cuts', group: 'barber', tags: 'happy young children fun' },

  // VIP & Luxury
  { key: 'crown', name: 'Royal Crown', group: 'luxury', tags: 'vip king royal executive luxury' },
  { key: 'sparkles', name: 'Sparkles', group: 'luxury', tags: 'clean shine fresh glow beard' },
  { key: 'sparkle', name: 'Sparkle Star', group: 'luxury', tags: 'shine glint clean special' },
  { key: 'flame', name: 'Fire / Trend', group: 'luxury', tags: 'hot trending popular transformation fire' },
  { key: 'gem', name: 'Diamond / Gem', group: 'luxury', tags: 'premium jewel crystal exclusive' },
  { key: 'trophy', name: 'Trophy / Best', group: 'luxury', tags: 'award champion best signature' },
  { key: 'award', name: 'Badge Award', group: 'luxury', tags: 'certificate ribbon certified master' },
  { key: 'star', name: 'Star Service', group: 'luxury', tags: 'featured favorite premier top' },
  { key: 'badge-check', name: 'Verified Cut', group: 'luxury', tags: 'guarantee certified approved check' },

  // Media & Creative
  { key: 'camera', name: 'Camera', group: 'media', tags: 'photo picture gallery shot portrait' },
  { key: 'image', name: 'Portfolio', group: 'media', tags: 'picture visual art photo work' },
  { key: 'wand', name: 'Transformation', group: 'media', tags: 'magic makeover before after wand' },
  { key: 'palette', name: 'Color Art', group: 'media', tags: 'art creative design dye palette' },
  { key: 'layers', name: 'Fade & Layers', group: 'media', tags: 'texture blend taper fade layers' },
  { key: 'eye', name: 'Eye & Brows', group: 'media', tags: 'brows shape facial detail look' },

  // Lounge & Style
  { key: 'store', name: 'Shop Front', group: 'lounge', tags: 'barbershop salon parlor studio' },
  { key: 'building', name: 'Architecture', group: 'lounge', tags: 'branch location interior space' },
  { key: 'coffee', name: 'Lounge Cafe', group: 'lounge', tags: 'espresso relax wait drink hospitality' },
  { key: 'shirt', name: 'Executive Style', group: 'lounge', tags: 'formal suiting grooming outfit' },
  { key: 'watch', name: 'Watch / Time', group: 'lounge', tags: 'wrist luxury accessory time' },
  { key: 'sun', name: 'Day / Fresh', group: 'lounge', tags: 'morning bright day outdoor' },
  { key: 'moon', name: 'Night / Shave', group: 'lounge', tags: 'dark evening late night' },
  { key: 'heart', name: 'Care & Love', group: 'lounge', tags: 'wellness therapy health favorite' },

  // Badges & Time
  { key: 'clock', name: 'Time / Speed', group: 'general', tags: 'quick minutes duration appointment' },
  { key: 'calendar', name: 'Schedule', group: 'general', tags: 'booking event date session' },
  { key: 'zap', name: 'Express Cut', group: 'general', tags: 'fast quick instant power bolt' },
  { key: 'shield', name: 'Sanitized', group: 'general', tags: 'protection hygiene clean safe' },
  { key: 'shield-check', name: 'Hygiene Verified', group: 'general', tags: 'safe clean sterilized check' },
  { key: 'tag', name: 'Special Tag', group: 'general', tags: 'promo label discount category' },
  { key: 'gift', name: 'Package Gift', group: 'general', tags: 'combo bundle present bonus' },
  { key: 'bag', name: 'Grooming Kit', group: 'general', tags: 'retail product bag shop' },
  { key: 'briefcase', name: 'Executive Pack', group: 'general', tags: 'business corporate pro' },
  { key: 'trending', name: 'Trending Style', group: 'general', tags: 'popular rising hot cuts' },
  { key: 'compass', name: 'Signature Guide', group: 'general', tags: 'explore style direction' },
  { key: 'bookmark', name: 'Saved Pick', group: 'general', tags: 'favorite saved bookmark' },
  { key: 'check-circle', name: 'Completed', group: 'general', tags: 'done confirmed approved ok' },
  { key: 'thumbs-up', name: 'Satisfaction', group: 'general', tags: 'review praise rating thumbs' },
];

export function resolveCategoryIcon(name, fallback = 'tag') {
  if (!name) {
    return ICON_COMPONENTS[fallback] || Sparkles;
  }
  const normalized = String(name).trim().toLowerCase().replace(/_/g, '-');
  return ICON_COMPONENTS[normalized] || ICON_COMPONENTS[fallback] || Sparkles;
}

export function getCategoryIconLabel(name) {
  if (!name) return '';
  const normalized = String(name).trim().toLowerCase().replace(/_/g, '-');
  const found = ICONS_CATALOG.find(i => i.key === normalized);
  return found ? found.name : String(name);
}
