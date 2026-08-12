export function startCooldown(container, seconds, onTick, onFinish, extraControls = []) {
    // Stop any existing cooldown on this container
    if (container._cooldownTimer) {
        clearInterval(container._cooldownTimer);
        delete container._cooldownTimer;
    }

    const controls = [
        ...container.querySelectorAll("input, button, textarea, select"),
        ...extraControls
    ].filter((control, index, all) => all.indexOf(control) === index);

    controls.forEach(control => {
        control.dataset.cooldown = "true";
        control.dataset.wasDisabled = control.disabled ? "true" : "false";
        control.disabled = true;
    });

    const endsAt = Date.now() + (seconds * 1000);

    const tick = () => {
        const remaining = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
        const minutes = Math.floor(remaining / 60);
        const secs = remaining % 60;
        onTick(`${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`);

        if (remaining === 0) {
            clearInterval(container._cooldownTimer);
            delete container._cooldownTimer;

            controls.forEach(control => {
                const wasDisabled = control.dataset.wasDisabled === "true";
                delete control.dataset.cooldown;
                delete control.dataset.wasDisabled;
                control.disabled = wasDisabled;
            });

            onFinish?.();
        }
    };

    tick();
    container._cooldownTimer = setInterval(tick, 1000);
}