const disabledStates = new WeakMap();
const loadingStates = new WeakMap();

export function setControlsDisabled(container, disabled) {
    if (!container) return;

    if (disabled) {
        if (disabledStates.has(container)) return;
        const controls = Array.from(container.querySelectorAll("input, select, textarea, button"));
        disabledStates.set(container, new Map(controls.map(control => [control, control.disabled])));
        controls.forEach(control => {
            control.disabled = true;
        });
        return;
    }

    const states = disabledStates.get(container);
    if (!states) return;

    states.forEach((wasDisabled, control) => {
        if (control.isConnected) control.disabled = wasDisabled;
    });

    disabledStates.delete(container);
}

export function setLoading(button, text) {
    const scope = button.closest("form") || button.closest(".akd-modal");
    loadingStates.set(button, { scope, wasDisabled: button.disabled });
    button.dataset.originalText = button.innerHTML;

    if (scope) {
        setControlsDisabled(scope, true);
    } else {
        button.disabled = true;
    }

    button.innerHTML = `
        <span class="spinner"></span>
        ${text}
    `;
}

export function clearLoading(button) {
    button.innerHTML = button.dataset.originalText;
    delete button.dataset.originalText;
    const state = loadingStates.get(button);
    if (state?.scope) {
        setControlsDisabled(state.scope, false);
    } else {
        button.disabled = state?.wasDisabled ?? false;
    }

    if (button.dataset.cooldown === "true") button.disabled = true;
    loadingStates.delete(button);
}