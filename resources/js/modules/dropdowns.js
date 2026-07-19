export function initDropdowns() {
  const triggers = document.querySelectorAll('.an-nav__trigger');
  if (!triggers.length) return;

  const closeAll = (except) => {
    triggers.forEach((trigger) => {
      if (trigger === except) return;

      trigger.setAttribute('aria-expanded', 'false');
      trigger
        .closest('.an-nav__item--dropdown')
        ?.classList.remove('is-open');
    });
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const parent = trigger.closest('.an-nav__item--dropdown');
      const isOpen = trigger.getAttribute('aria-expanded') === 'true';

      closeAll();

      if (!isOpen) {
        trigger.setAttribute('aria-expanded', 'true');
        parent?.classList.add('is-open');
      }
    });
  });

  // Close when clicking outside
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.an-nav__item--dropdown')) {
      closeAll();
    }
  });

  // Close with Escape
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeAll();
    }
  });

  // Close on scroll
  window.addEventListener('scroll', closeAll, { passive: true });

  // Close on resize
  window.addEventListener('resize', closeAll);
}