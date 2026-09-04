// resources/js/member/challenges.js
// Community Challenges, mock voting and mock submission-form interaction.
// Safe to import unconditionally from member.js: everything here
// no-ops when the page isn't present.

import { setLoading, clearLoading } from '../modules/loading-state.js';
import { useConfirmDialog } from '../modules/confirm-dialog.js';

function setVoteButtonState(button, isSelected) {
    button.classList.toggle('is-selected', isSelected);
    button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');

    const label = button.querySelector('.akd-challenge-vote-btn__label');
    if (label) {
        label.textContent = isSelected ? 'Voted' : 'Vote';
    }

    const card = button.closest('[data-submission-id]');
    if (card) {
        card.classList.toggle('is-selected', isSelected);
    }
}

function initVoting(root) {
    const maxSelections = parseInt(root.dataset.maxSelections, 10) || 1;
    const buttons = Array.from(root.querySelectorAll('[data-vote-btn]'));
    const remainingEl = root.querySelector('[data-votes-remaining]');
    const selected = new Set();

    function updateRemaining() {
        if (remainingEl) {
            remainingEl.textContent = String(maxSelections - selected.size);
        }

        buttons.forEach((button) => {
            const id = button.dataset.submissionId;
            const isSelected = selected.has(id);
            const atCapacity = selected.size >= maxSelections && maxSelections !== 1;

            button.disabled = !isSelected && atCapacity;
            button.classList.toggle('is-unavailable', button.disabled);
        });
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.submissionId;

            if (selected.has(id)) {
                selected.delete(id);
                setVoteButtonState(button, false);
                updateRemaining();
                return;
            }

            if (selected.size >= maxSelections) {
                if (maxSelections !== 1) {
                    return;
                }

                const [previousId] = selected;
                const previousButton = buttons.find((btn) => btn.dataset.submissionId === previousId);

                if (previousButton) {
                    setVoteButtonState(previousButton, false);
                }

                selected.clear();
            }

            selected.add(id);
            setVoteButtonState(button, true);
            updateRemaining();
        });
    });

    updateRemaining();
}

function formatFileSize(bytes) {
    if (bytes < 1024 * 1024) {
        return Math.round(bytes / 1024) + ' KB';
    }

    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function initCharCounter(input, counterEl, max) {
    const update = () => {
        const length = input.value.length;
        counterEl.textContent = length + ' / ' + max;
        counterEl.classList.toggle('is-near-limit', length >= max - 5 && length < max);
        counterEl.classList.toggle('is-at-limit', length >= max);
    };

    input.addEventListener('input', update);
    update();
}

function validateTitle(input, errorEl, min, max) {
    const value = input.value.trim();

    if (value.length < min) {
        errorEl.textContent = 'Enter at least ' + min + ' characters.';
        errorEl.hidden = false;
        return false;
    }

    if (value.length > max) {
        errorEl.textContent = 'Keep this to ' + max + ' characters or fewer.';
        errorEl.hidden = false;
        return false;
    }

    errorEl.hidden = true;
    return true;
}

function initFileField(field) {
    const dropzone    = field.querySelector('[data-dropzone]');
    const input       = field.querySelector('[data-file-input]');
    const selectedRow = field.querySelector('[data-file-selected]');
    const nameEl      = field.querySelector('[data-file-name]');
    const metaEl      = field.querySelector('[data-file-meta]');
    const removeBtn   = field.querySelector('[data-file-remove]');
    const errorEl     = field.querySelector('[data-file-error]');
    const previewImg  = field.querySelector('[data-file-preview]');
    const fileIcon    = field.querySelector('[data-file-icon]');
    const accept      = (field.dataset.accept || '').split(',').filter(Boolean);
    const maxSizeMb   = parseFloat(field.dataset.maxSizeMb) || 10;
    const acceptLabel = field.dataset.acceptLabel || 'a supported file';
    const previewable = field.dataset.previewable === '1';

    let currentFile = null;
    let previewUrl  = null;

    function showError(message) {
        errorEl.textContent = message;
        errorEl.hidden = false;
    }

    function clearError() {
        errorEl.hidden = true;
    }

    function revokePreview() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    }

    function reset() {
        input.value = '';
        currentFile = null;
        revokePreview();

        if (previewImg) {
            previewImg.src = '';
            previewImg.hidden = true;
        }

        if (fileIcon) {
            fileIcon.hidden = false;
        }

        selectedRow.hidden = true;
        dropzone.hidden = false;
    }

    function showSelected(file) {
        currentFile = file;
        nameEl.textContent = file.name;
        metaEl.textContent = formatFileSize(file.size) + ' \u00b7 Ready to submit';

        if (previewable && previewImg) {
            revokePreview();
            previewUrl = URL.createObjectURL(file);
            previewImg.src = previewUrl;
            previewImg.hidden = false;

            if (fileIcon) {
                fileIcon.hidden = true;
            }
        }

        selectedRow.hidden = false;
        dropzone.hidden = true;
    }

    function handleFile(file) {
        if (!file) {
            return false;
        }

        if (accept.length && !accept.includes(file.type)) {
            showError("This file type isn't supported for this challenge. Use " + acceptLabel + '.');
            reset();
            return false;
        }

        const sizeMb = file.size / (1024 * 1024);

        if (sizeMb > maxSizeMb) {
            showError('This file is larger than the ' + maxSizeMb + ' MB limit.');
            reset();
            return false;
        }

        clearError();
        showSelected(file);
        return true;
    }

    dropzone.addEventListener('click', () => input.click());

    dropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });

    dropzone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('is-dragover');
    });

    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragover');

        const file = event.dataTransfer.files[0];

        if (file) {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            handleFile(file);
        }
    });

    input.addEventListener('change', () => {
        handleFile(input.files[0]);
    });

    removeBtn.addEventListener('click', () => {
        reset();
        clearError();
    });

    field.validate = () => {
        if (!input.files[0]) {
            showError('Choose a file to submit.');
            return false;
        }

        return handleFile(input.files[0]);
    };

    field.getSelectedFile = () => currentFile;
}

function initMockSubmissionForm(container) {
    const form = container.querySelector('[data-mock-submit-form]');

    if (!form) {
        return;
    }

    const successEl    = container.querySelector('[data-submission-success]');
    const submitButton = form.querySelector('[data-mock-submit-btn]');
    const titleInput   = form.querySelector('[data-title-input]');
    const titleError   = form.querySelector('[data-title-error]');
    const charCount    = form.querySelector('[data-char-count]');
    const fileField    = form.querySelector('[data-file-field]');

    if (!successEl || !submitButton || !titleInput || !titleError || !charCount || !fileField) {
        console.warn('Challenge submission form is missing required elements.', {
            form, successEl, submitButton, titleInput, titleError, charCount, fileField,
        });

        return;
    }

    const min = parseInt(titleInput.getAttribute('minlength'), 10) || 3;
    const max = parseInt(titleInput.getAttribute('maxlength'), 10) || 40;
    initCharCounter(titleInput, charCount, max);
    initFileField(fileField);
    titleInput.addEventListener('blur', () => validateTitle(titleInput, titleError, min, max));

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const titleValid = validateTitle(titleInput, titleError, min, max);
        const fileValid = fileField.validate();

        if (!titleValid || !fileValid) {
            return;
        }

        const entryTitle = titleInput.value.trim();
        const selectedFile = fileField.getSelectedFile();
        const fileName = selectedFile ? selectedFile.name : '';

        const confirmed = await useConfirmDialog().ask({
            title: 'Ready to submit?',
            message: 'Submit "' + entryTitle + '" using "' + fileName + '"? Once submitted, your entry cannot be changed.',
            confirmLabel: 'Submit',
            cancelLabel: 'Cancel',
        });

        if (!confirmed) {
            return;
        }

        setLoading(submitButton, 'Submitting...');

        // Mock only, nothing is actually uploaded or persisted. The
        // success state resets on refresh since it lives in plain DOM
        // state, not local storage or a backend call.
        window.setTimeout(() => {
            clearLoading(submitButton);
            form.hidden = true;
            successEl.hidden = false;
        }, 900);
    });
}

function init() {
    const root = document.querySelector('[data-challenge-app]');

    if (!root) {
        return;
    }

    const votingRoot = root.querySelector('[data-voting-root]');

    if (votingRoot) {
        initVoting(votingRoot);
    }

    const submissionFlow = root.querySelector('[data-submission-flow]');

    if (submissionFlow) {
        initMockSubmissionForm(submissionFlow);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}