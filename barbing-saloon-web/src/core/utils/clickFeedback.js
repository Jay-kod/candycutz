/**
 * Click & Tap Feedback (Micro-Ripple & Haptic Engine)
 * Automatically provides instantaneous tactile and visual feedback on user interaction.
 */
export function initClickFeedback() {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;

  const triggerImpact = (e) => {
    // Only respond to primary click / touch
    if (e.button !== undefined && e.button !== 0) return;

    const target = e.target?.closest?.('button, [role="button"], a.btn, .btn, .card-clickable, .tab-button');
    if (!target) return;

    // Ignore disabled or non-interactive elements
    if (
      target.disabled ||
      target.getAttribute('aria-disabled') === 'true' ||
      target.classList.contains('disabled') ||
      target.classList.contains('pointer-events-none')
    ) {
      return;
    }

    // Trigger subtle mobile vibration on supported touch devices
    if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
      try {
        navigator.vibrate(8);
      } catch {
        // Silently ignore if blocked by browser policy
      }
    }

    // Spawn localized GPU-accelerated micro-ripple
    const rect = target.getBoundingClientRect();
    if (!rect.width || !rect.height) return;

    const ripple = document.createElement('span');
    ripple.className = 'click-impact-ripple';

    const clientX = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : rect.left + rect.width / 2);
    const clientY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : rect.top + rect.height / 2);

    const x = clientX - rect.left;
    const y = clientY - rect.top;
    const size = Math.max(rect.width, rect.height) * 1.4;

    ripple.style.width = `${size}px`;
    ripple.style.height = `${size}px`;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;

    const computedStyle = window.getComputedStyle(target);
    if (computedStyle.position === 'static') {
      target.style.position = 'relative';
    }
    target.style.overflow = 'hidden';

    target.appendChild(ripple);

    setTimeout(() => {
      ripple.remove();
    }, 380);
  };

  document.addEventListener('pointerdown', triggerImpact, { passive: true });
}
