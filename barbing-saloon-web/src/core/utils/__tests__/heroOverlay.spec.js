// globals provided by vitest config (globals: true)
import { 
  hexToRgb, 
  buildOverlayGradient, 
  DEFAULT_OVERLAY_COLOR, 
  OVERLAY_COLOR_PRESETS,
  DEFAULT_OVERLAY_PRESETS,
  isValidHexColor,
  normalizeHexColor,
  parseOverlayPresets 
} from '../heroOverlay';

describe('heroOverlay utils', () => {
  it('converts valid 6-character hex to rgb', () => {
    expect(hexToRgb('#D4AF37')).toEqual({ r: 212, g: 175, b: 55 });
    expect(hexToRgb('#111827')).toEqual({ r: 17, g: 24, b: 39 });
    expect(hexToRgb('#000000')).toEqual({ r: 0, g: 0, b: 0 });
    expect(hexToRgb('#ffffff')).toEqual({ r: 255, g: 255, b: 255 });
  });

  it('converts 3-character hex shorthand to rgb', () => {
    expect(hexToRgb('#fff')).toEqual({ r: 255, g: 255, b: 255 });
    expect(hexToRgb('#000')).toEqual({ r: 0, g: 0, b: 0 });
  });

  it('falls back to default gold rgb for invalid hex values', () => {
    expect(hexToRgb('')).toEqual({ r: 212, g: 175, b: 55 });
    expect(hexToRgb('invalid')).toEqual({ r: 212, g: 175, b: 55 });
    expect(hexToRgb(null)).toEqual({ r: 212, g: 175, b: 55 });
  });

  it('validates hex colors correctly', () => {
    expect(isValidHexColor('#D4AF37')).toBe(true);
    expect(isValidHexColor('#fff')).toBe(true);
    expect(isValidHexColor('#123456')).toBe(true);
    expect(isValidHexColor('D4AF37')).toBe(false);
    expect(isValidHexColor('#12345')).toBe(false);
    expect(isValidHexColor('#gggggg')).toBe(false);
    expect(isValidHexColor(null)).toBe(false);
  });

  it('normalizes hex colors with fallback', () => {
    expect(normalizeHexColor('D4AF37')).toBe('#D4AF37');
    expect(normalizeHexColor('#f59e0b')).toBe('#F59E0B');
    expect(normalizeHexColor('invalid')).toBe(DEFAULT_OVERLAY_COLOR);
    expect(normalizeHexColor(null)).toBe(DEFAULT_OVERLAY_COLOR);
  });

  it('parses valid overlay presets from JSON string or array', () => {
    const custom = [
      { label: 'Sunset Red', value: '#FF0055' },
      { label: 'Cyan Wave', value: '#00FFFF' }
    ];
    const parsed = parseOverlayPresets(JSON.stringify(custom));
    expect(parsed).toHaveLength(2);
    expect(parsed[0].label).toBe('Sunset Red');
    expect(parsed[0].value).toBe('#FF0055');

    const fromArray = parseOverlayPresets(custom);
    expect(fromArray).toEqual([
      { label: 'Sunset Red', value: '#FF0055' },
      { label: 'Cyan Wave', value: '#00FFFF' }
    ]);
  });

  it('falls back to default presets on invalid or empty input', () => {
    expect(parseOverlayPresets(null)).toEqual(DEFAULT_OVERLAY_PRESETS);
    expect(parseOverlayPresets('')).toEqual(DEFAULT_OVERLAY_PRESETS);
    expect(parseOverlayPresets('not-json')).toEqual(DEFAULT_OVERLAY_PRESETS);
    expect(parseOverlayPresets([])).toEqual(DEFAULT_OVERLAY_PRESETS);
  });

  it('returns signature gold gradient when color is default gold or null', () => {
    const defaultGrad = buildOverlayGradient(DEFAULT_OVERLAY_COLOR);
    expect(defaultGrad).toContain('radial-gradient');
    expect(defaultGrad).toContain('rgba(250, 190, 60');
    expect(buildOverlayGradient(null)).toBe(defaultGrad);
    expect(buildOverlayGradient('gold')).toBe(defaultGrad);
  });

  it('generates customized radial and linear gradient for custom colors', () => {
    const emeraldGrad = buildOverlayGradient('#059669');
    expect(emeraldGrad).toContain('radial-gradient');
    expect(emeraldGrad).toContain('linear-gradient');
    expect(emeraldGrad).toContain('rgba(5, 150, 105');
  });

  it('exports rich preset list including Gold, Amber, Obsidian, Emerald, Sapphire, Velvet', () => {
    expect(OVERLAY_COLOR_PRESETS.length).toBeGreaterThanOrEqual(6);
    const labels = OVERLAY_COLOR_PRESETS.map(p => p.label);
    expect(labels).toContain('Luxury Gold');
    expect(labels).toContain('Obsidian');
    expect(labels).toContain('Royal Emerald');
  });
});
