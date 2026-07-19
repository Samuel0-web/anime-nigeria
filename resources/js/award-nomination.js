export function initAwardsNominationForm() {
  initNominationFaq();

  const dropdown = document.querySelector('.an-awards-dropdown');
  if (!dropdown) return;

  initCategoryDropdown(dropdown);
  initCharacterCounter();
  initAgreementGate();
}

function initCategoryDropdown(dropdown) {
  const trigger = dropdown.querySelector('.an-awards-dropdown__trigger');
  const triggerText = dropdown.querySelector('.an-awards-dropdown__trigger-text');
  const triggerIcon = dropdown.querySelector('.an-awards-dropdown__trigger-icon i');
  const hiddenValue = dropdown.querySelector('#nom-category-value');
  const searchInput = dropdown.querySelector('.an-awards-dropdown__search-input');
  const options = Array.from(dropdown.querySelectorAll('.an-awards-dropdown__option'));

  const visibleOptions = () => options.filter((option) => !option.hidden);

  const openDropdown = () => {
    dropdown.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
    if (searchInput) {
      searchInput.value = '';
      filterOptions('');
      searchInput.focus();
    }
  };

  const closeDropdown = ({ focusTrigger = false } = {}) => {
    dropdown.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    if (focusTrigger) trigger.focus();
  };

  const focusOption = (option) => {
    if (!option) return;
    options.forEach((opt) => opt.setAttribute('tabindex', '-1'));
    option.setAttribute('tabindex', '0');
    option.focus();
  };

  const selectOption = (option) => {
    options.forEach((opt) => opt.setAttribute('aria-selected', 'false'));
    option.setAttribute('aria-selected', 'true');

    triggerText.textContent = option.dataset.label;
    if (triggerIcon) triggerIcon.className = `fa-solid ${option.dataset.icon}`;
    if (hiddenValue) hiddenValue.value = option.dataset.value;

    closeDropdown({ focusTrigger: true });
  };

  const filterOptions = (query) => {
    const normalized = query.trim().toLowerCase();
    options.forEach((option) => {
      option.hidden = !option.dataset.label.toLowerCase().includes(normalized);
    });
  };

  // Trigger: click toggles, ArrowDown/Enter opens and jumps into the list
  trigger.addEventListener('click', () => {
    if (dropdown.classList.contains('is-open')) {
      closeDropdown();
    } else {
      openDropdown();
    }
  });

  trigger.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      openDropdown();
      focusOption(visibleOptions()[0]);
    }
  });

  // Options: click or keyboard select, arrow-key navigation
  options.forEach((option) => {
    option.addEventListener('click', () => selectOption(option));

    option.addEventListener('keydown', (event) => {
      const visible = visibleOptions();
      const index = visible.indexOf(option);

      switch (event.key) {
        case 'Enter':
        case ' ':
          event.preventDefault();
          selectOption(option);
          break;
        case 'ArrowDown':
          event.preventDefault();
          focusOption(visible[index + 1] || visible[0]);
          break;
        case 'ArrowUp':
          event.preventDefault();
          focusOption(visible[index - 1] || visible[visible.length - 1]);
          break;
        case 'Home':
          event.preventDefault();
          focusOption(visible[0]);
          break;
        case 'End':
          event.preventDefault();
          focusOption(visible[visible.length - 1]);
          break;
        case 'Escape':
          event.preventDefault();
          closeDropdown({ focusTrigger: true });
          break;
        case 'Tab':
          closeDropdown();
          break;
      }
    });
  });

  // Search: filters the list live, ArrowDown jumps into results
  if (searchInput) {
    searchInput.addEventListener('input', (event) => filterOptions(event.target.value));

    searchInput.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        focusOption(visibleOptions()[0]);
      } else if (event.key === 'Escape') {
        event.preventDefault();
        closeDropdown({ focusTrigger: true });
      }
    });
  }

  // Click outside closes
  document.addEventListener('click', (event) => {
    if (!dropdown.contains(event.target)) closeDropdown();
  });
}

function initCharacterCounter() {
  const field = document.getElementById('nom-reason');
  const counter = document.getElementById('nom-reason-counter');
  if (!field || !counter) return;

  const maxLength = Number(field.getAttribute('maxlength')) || 500;

  const update = () => {
    // Defensive trim in addition to the native maxlength attribute
    if (field.value.length > maxLength) {
      field.value = field.value.slice(0, maxLength);
    }

    const length = field.value.length;
    counter.textContent = `${length} / ${maxLength}`;
    counter.classList.toggle('is-limit', length >= maxLength);
  };

  field.addEventListener('input', update);
  update();
}

function initAgreementGate() {
  const agreement = document.getElementById('nom-agreement');
  const submitBtn = document.getElementById('nom-submit');
  if (!agreement || !submitBtn) return;

  agreement.addEventListener('change', () => {
    submitBtn.disabled = !agreement.checked;
    submitBtn.setAttribute('aria-disabled', String(!agreement.checked));
  });
}

// =============================================================================
// NOMINATION FAQ
// =============================================================================

function initNominationFaq() {
    const items = document.querySelectorAll('.an-nomination-faq__item');
    if (!items.length) return;

    items.forEach((item, index) => {
        const button = item.querySelector('.an-nomination-faq__question');
        const answer = item.querySelector('.an-nomination-faq__answer');
        if (!button || !answer) return;

        // Open the first item by default.
        if (index === 0) {
            openNominationFaqItem(item, button, answer);
        }

        button.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            items.forEach(otherItem => {
                const otherButton = otherItem.querySelector('.an-nomination-faq__question');
                const otherAnswer = otherItem.querySelector('.an-nomination-faq__answer');
                if (!otherButton || !otherAnswer) return;

                closeNominationFaqItem(otherItem, otherButton, otherAnswer);
            });

            if (!isOpen) {
                openNominationFaqItem(item, button, answer);
            }
        });
    });
}

function openNominationFaqItem(item, button, answer) {
    item.classList.add('is-open');
    button.setAttribute('aria-expanded', 'true');
    answer.style.maxHeight = `${answer.scrollHeight}px`;
}

function closeNominationFaqItem(item, button, answer) {
    item.classList.remove('is-open');
    button.setAttribute('aria-expanded', 'false');
    answer.style.maxHeight = null;
}