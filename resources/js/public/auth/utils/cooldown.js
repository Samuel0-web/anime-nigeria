export function startCooldown(form, seconds, onTick, onFinish) {
    // Stop any existing cooldown on this form
    if (form._cooldownTimer) {
        clearInterval(form._cooldownTimer);
    }

    const controls = form.querySelectorAll("input, button, textarea, select");

    controls.forEach(control => {
        control.dataset.cooldown = "true";
        control.disabled = true;
    });

    const endsAt = Date.now() + (seconds * 1000);

    const tick = () => {
        const remaining = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
        const minutes = Math.floor(remaining / 60);
        const secs = remaining % 60;
        onTick(`${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`);

        if (remaining === 0) {
            clearInterval(form._cooldownTimer);
            delete form._cooldownTimer;

            controls.forEach(control => {
                delete control.dataset.cooldown;
                control.disabled = false;
            });

            onFinish?.();
        }
    };

    tick();
    form._cooldownTimer = setInterval(tick, 1000);
}