export const DEFAULT_OVERLAY_COLOR = '#D4AF37';

export const DEFAULT_OVERLAY_PRESETS = [
  { label: 'Luxury Gold', value: '#D4AF37' },
  { label: 'Warm Amber', value: '#F59E0B' },
  { label: 'Deep Bronze', value: '#B45309' },
  { label: 'Obsidian', value: '#111827' },
  { label: 'Royal Emerald', value: '#059669' },
  { label: 'Midnight Sapphire', value: '#2563EB' },
  { label: 'Wine Velvet', value: '#991B1B' },
];

export const OVERLAY_COLOR_PRESETS = DEFAULT_OVERLAY_PRESETS;

export function isValidHexColor(hex) {
  if (!hex || typeof hex !== 'string') return false;
  return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex.trim());
}

export function normalizeHexColor(hex, fallback = DEFAULT_OVERLAY_COLOR) {
  if (!hex || typeof hex !== 'string') return fallback;
  let clean = hex.trim();
  if (!clean.startsWith('#')) {
    clean = '#' + clean;
  }
  return isValidHexColor(clean) ? clean.toUpperCase() : fallback;
}

export function parseOverlayPresets(input) {
  if (!input) return JSON.parse(JSON.stringify(DEFAULT_OVERLAY_PRESETS));
  let parsed = input;
  if (typeof input === 'string') {
    try {
      parsed = JSON.parse(input);
    } catch {
      return JSON.parse(JSON.stringify(DEFAULT_OVERLAY_PRESETS));
    }
  }
  if (!Array.isArray(parsed) || parsed.length === 0) {
    return JSON.parse(JSON.stringify(DEFAULT_OVERLAY_PRESETS));
  }
  const result = parsed
    .filter(item => item && typeof item === 'object')
    .map((item, idx) => {
      const label = item.label && typeof item.label === 'string' && item.label.trim()
        ? item.label.trim()
        : `Preset ${idx + 1}`;
      const value = normalizeHexColor(item.value, DEFAULT_OVERLAY_COLOR);
      return { label, value };
    });
  return result.length > 0 ? result : JSON.parse(JSON.stringify(DEFAULT_OVERLAY_PRESETS));
}

export function hexToRgb(hex) {
  if (!hex || typeof hex !== 'string') return { r: 212, g: 175, b: 55 };
  let clean = hex.replace('#', '').trim();
  if (clean.length === 3) {
    clean = clean.split('').map(c => c + c).join('');
  }
  if (clean.length !== 6) {
    return { r: 212, g: 175, b: 55 };
  }
  const num = parseInt(clean, 16);
  if (isNaN(num)) {
    return { r: 212, g: 175, b: 55 };
  }
  return {
    r: (num >> 16) & 255,
    g: (num >> 8) & 255,
    b: num & 255,
  };
}

export function buildOverlayGradient(color) {
  if (!color || color === 'gold' || color.toLowerCase() === '#d4af37' || color.toLowerCase() === '#ff9900') {
    return 'radial-gradient(ellipse at 50% 40%, rgba(250, 190, 60, 0.20) 0%, rgba(212, 143, 24, 0.26) 38%, rgba(138, 86, 11, 0.45) 72%, rgba(20, 14, 6, 0.82) 100%), linear-gradient(180deg, rgba(230, 168, 23, 0.10) 0%, rgba(14, 10, 4, 0.68) 100%)';
  }

  const { r, g, b } = hexToRgb(color);

  const lightR = Math.min(255, Math.round(r * 1.15 + 15));
  const lightG = Math.min(255, Math.round(g * 1.15 + 15));
  const lightB = Math.min(255, Math.round(b * 1.15 + 15));

  const darkR = Math.round(r * 0.45);
  const darkG = Math.round(g * 0.45);
  const darkB = Math.round(b * 0.45);

  const deepR = Math.round(r * 0.15);
  const deepG = Math.round(g * 0.15);
  const deepB = Math.round(b * 0.15);

  return `radial-gradient(ellipse at 50% 40%, rgba(${lightR}, ${lightG}, ${lightB}, 0.20) 0%, rgba(${r}, ${g}, ${b}, 0.26) 38%, rgba(${darkR}, ${darkG}, ${darkB}, 0.45) 72%, rgba(${deepR}, ${deepG}, ${deepB}, 0.82) 100%), linear-gradient(180deg, rgba(${r}, ${g}, ${b}, 0.10) 0%, rgba(${deepR}, ${deepG}, ${deepB}, 0.68) 100%)`;
}
