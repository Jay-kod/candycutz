import { onMounted, onUnmounted } from 'vue';

/**
 * Scroll reveal composable.
 * Call inside any page's setup to animate elements with `data-reveal` attribute.
 * Optionally set `data-reveal-delay="100"` for staggered entrances.
 */
export function useScrollReveal(rootRef = null) {
  let observer = null;

  const init = () => {
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
