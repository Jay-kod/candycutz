import { describe, it, expect, vi } from 'vitest';
import { useConfirm } from '../useConfirm';

describe('useConfirm Composable with 2-Stage Morphing Resolution', () => {
  it('opens confirmation modal with default options', () => {
    const { isOpen, title, message, variant, resolutionType, confirm } = useConfirm();

    confirm('Delete Service', 'Are you sure you want to delete this service?');

    expect(isOpen.value).toBe(true);
    expect(title.value).toBe('Delete Service');
    expect(message.value).toBe('Are you sure you want to delete this service?');
    expect(variant.value).toBe('danger');
    expect(resolutionType.value).toBe('deleted');
  });

  it('handles custom resolution configuration and options object', () => {
    const { isOpen, resolutionType, resolutionTitle, autoDismissDelay, confirm } = useConfirm();

    confirm({
      title: 'Accept Booking',
      message: 'Client will be notified.',
      variant: 'primary',
      resolutionType: 'confirmed',
      resolutionTitle: 'Booking Accepted!',
      autoDismissDelay: 1000,
    });

    expect(isOpen.value).toBe(true);
    expect(resolutionType.value).toBe('confirmed');
    expect(resolutionTitle.value).toBe('Booking Accepted!');
    expect(autoDismissDelay.value).toBe(1000);
  });

  it('transitions to resolved state on agree and executes callback', async () => {
    vi.useFakeTimers();
    const { isOpen, isResolved, confirm, agree } = useConfirm();
    const actionSpy = vi.fn();

    const promise = confirm({
      title: 'Remove Item',
      onConfirm: actionSpy,
      autoDismissDelay: 500,
    });

    expect(isOpen.value).toBe(true);
    expect(isResolved.value).toBe(false);

    await agree();

    expect(actionSpy).toHaveBeenCalled();
    expect(isResolved.value).toBe(true);

    // Fast-forward auto-dismiss timer
    vi.advanceTimersByTime(500);
    const result = await promise;

    expect(result).toBe(true);
    expect(isOpen.value).toBe(false);
    expect(isResolved.value).toBe(false);

    vi.useRealTimers();
  });

  it('cancels without entering resolved state', async () => {
    const { isOpen, isResolved, confirm, cancel } = useConfirm();

    const promise = confirm('Test cancel');
    expect(isOpen.value).toBe(true);

    cancel();

    const result = await promise;
    expect(result).toBe(false);
    expect(isOpen.value).toBe(false);
    expect(isResolved.value).toBe(false);
  });
});
