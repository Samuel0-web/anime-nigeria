export function setLoading(button, text) {
    button.dataset.originalText = button.innerHTML;
    button.disabled = true;

    button.innerHTML = `
        <span class="spinner"></span>
        ${text}
    `;
}

export function clearLoading(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText;
}