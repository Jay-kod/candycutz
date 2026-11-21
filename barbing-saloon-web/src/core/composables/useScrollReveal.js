import { onMounted, onUnmounted } from 'vue';

const nativeScrollTimeline =
  typeof window !== 'undefined' &&
  window.CSS &&
  window.CSS.supports &&
  window.CSS.supports('animation-timeline', 'view()');

/**
 * Scroll reveal composable (IntersectionObserver fallback).
 * When the browser supports native CSS scroll-driven animations
 * (animation-timeline: view()), the CSS in main.css handles the reveal
 * with zero JS — this becomes a no-op. For older browsers it observes
 * elements and toggles `.revealed` via IntersectionObserver.
 * Optionally set `data-reveal-delay="100"` for staggered entrances.
 */
export function useScrollReveal(rootRef = null) {
  let observer = null;

  const init = () => {
    // Native CSS scroll-driven animations already cover the reveal.
    if (nativeScrollTimeline) return;

    if (!observer) {
      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              const delay = entry.target.dataset.revealDelay || 0;
              setTimeout(() => {
                entry.target.classList.add('revealed');
              }, Number(delay));
              observer.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
      );
    }

    const root = rootRef?.value || document;
    // Small timeout to ensure DOM is updated after Vue reactivity ticks
    setTimeout(() => {
      root.querySelectorAll('[data-reveal]:not(.revealed)').forEach((el) => {
        observer.observe(el);
      });
    }, 100);
  };

  onMounted(() => {
    init();
  });

  onUnmounted(() => {
    observer?.disconnect();
  });

  return { init };
}
