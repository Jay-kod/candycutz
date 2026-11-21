import { describe, expect, it } from 'vitest';
import { formatCurrency, formatNairaAmount } from '../index';

describe('currency formatting', () => {
  it('formats service prices as naira without converting them from kobo', () => {
    expect(formatNairaAmount(1500)).toBe('₦1,500');
    expect(formatNairaAmount('1500')).toBe('₦1,500');
    expect(formatNairaAmount(1500.5)).toBe('₦1,501');
  });

  it('keeps the existing kobo formatter behavior for payment amounts', () => {
    expect(formatCurrency(150000)).toBe('₦1,500.00');
  });
});
