/**
 * CandyCutz Unified Portal Theme & Tone Configuration
 * Maps portal roles to consistent SaaS design tokens while preserving authentic luxury branding.
 */

export function getPortalThemeConfig(portalName = 'Portal', isDark = true) {
  const p = (portalName || '').toLowerCase().trim();

  // Super Admin: Crimson Ruby Governance Accent
  if (p === 'super admin' || p === 'superadmin') {
    return {
      title: 'Command Center',
      roleBadge: 'text-rose-400 bg-rose-500/10 border-rose-500/30',
      dotClass: 'bg-rose-500',
      rimGradient: 'from-rose-500/40 via-rose-600/20 to-transparent',
      activeGradient: isDark
        ? 'bg-gradient-to-r from-rose-500/15 via-rose-500/10 to-transparent text-white'
        : 'bg-rose-50 text-rose-950',
      activeBorder: 'border-rose-500/30',
      activeIndicator: 'bg-rose-500',
      iconActive: 'bg-rose-500/20 text-rose-400 border-rose-500/30',
      iconHover: 'group-hover:text-rose-400',
      accentColor: '#EF4444',
      badgeClass: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
    };
  }

  // Admin: Tangerine Amber Operations Accent
  if (p === 'admin') {
    return {
      title: 'Operations',
      roleBadge: 'text-amber-400 bg-amber-500/10 border-amber-500/30',
      dotClass: 'bg-amber-500',
      rimGradient: 'from-amber-500/40 via-amber-600/20 to-transparent',
      activeGradient: isDark
        ? 'bg-gradient-to-r from-amber-500/15 via-amber-500/10 to-transparent text-white'
        : 'bg-amber-50 text-amber-950',
      activeBorder: 'border-amber-500/30',
      activeIndicator: 'bg-amber-500',
      iconActive: 'bg-amber-500/20 text-amber-400 border-amber-500/30',
      iconHover: 'group-hover:text-amber-400',
      accentColor: '#F59E0B',
      badgeClass: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    };
  }

  // Barber: Emerald Mint Chair Accent
  if (p === 'barber') {
    return {
      title: 'Barber Studio',
      roleBadge: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30',
      dotClass: 'bg-emerald-500',
      rimGradient: 'from-emerald-500/40 via-emerald-600/20 to-transparent',
      activeGradient: isDark
        ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white'
        : 'bg-emerald-50 text-emerald-950',
      activeBorder: 'border-emerald-500/30',
      activeIndicator: 'bg-emerald-500',
      iconActive: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
      iconHover: 'group-hover:text-emerald-400',
      accentColor: '#10B981',
      badgeClass: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    };
  }

  // Customer / Default: Champagne Gold Luxury Grooming
  return {
    title: 'Client Lounge',
    roleBadge: 'text-amber-300 bg-amber-500/10 border-amber-500/25',
    dotClass: 'bg-amber-400',
    rimGradient: 'from-amber-400/40 via-amber-500/20 to-transparent',
    activeGradient: isDark
      ? 'bg-gradient-to-r from-amber-500/15 via-amber-500/10 to-transparent text-white'
      : 'bg-amber-50 text-amber-950',
    activeBorder: 'border-amber-500/30',
    activeIndicator: 'bg-amber-400',
    iconActive: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
    iconHover: 'group-hover:text-amber-300',
    accentColor: '#D4AF37',
    badgeClass: 'bg-amber-500/15 text-amber-300 border-amber-500/25',
  };
}

