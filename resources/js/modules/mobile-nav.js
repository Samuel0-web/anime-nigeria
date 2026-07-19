export function initMobileNav() {
  const toggle = document.getElementById('nav-toggle');
  const panel = document.getElementById('mobile-nav');
  if (!toggle || !panel) return;

  toggle.addEventListener('click', () => {
    const isOpen = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!isOpen));
    panel.classList.toggle('is-open', !isOpen);
    document.body.style.overflow = !isOpen ? 'hidden' : '';
  });

  const groupTriggers = panel.querySelectorAll('.an-mobile-nav__trigger');
  groupTriggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const isOpen = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', String(!isOpen));
      trigger.nextElementSibling?.classList.toggle('is-open', !isOpen);
    });
  });

  document.addEventListener('keydown', (event) => {
    if (!panel.classList.contains('is-open')) return;

    if (event.key === 'Escape') {
      toggle.click();
      toggle.focus();
      return;
    }

    if (event.key === 'Tab') {
      const focusable = panel.querySelectorAll('a[href], button:not([disabled])');
      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });
}