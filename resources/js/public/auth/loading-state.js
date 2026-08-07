export function setLoading(button, text) {
    button.dataset.originalText = button.innerHTML;
    button.disabled = true;

    button.innerHTML = `
        <span class="spinner"></span>
        ${text}
    `;
}

export function clearLoading(button) {
    button.innerHTML = button.dataset.originalText;
    delete button.dataset.originalText;
    button.disabled = button.dataset.cooldown === "true";
}