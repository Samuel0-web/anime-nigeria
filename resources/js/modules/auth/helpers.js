// ======================================================================
// HELPERS
// ======================================================================
export const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
export const nameRegex = /^[A-Za-zÀ-ÿ' -]{2,}$/;

export function setError(field, message) {
    field.classList.remove("is-valid");
    field.classList.add("is-invalid");
    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = message;
        error.classList.add("is-visible");
    }
}

export function clearError(field) {
    field.classList.remove("is-invalid");
    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = "";
        error.classList.remove("is-visible", "is-checking", "is-success");
    }
}

export function showFormMessage(form, message) {
    const box = form.querySelector(".an-auth__message");

    if (!box) return;

    box.textContent = message;
    box.classList.add("is-visible");
}

export function clearFormMessage(form) {
    const box = form.querySelector(".an-auth__message");

    if (!box) return;

    box.textContent = "";
    box.classList.remove("is-visible");
}

export function setValid(field) {
    field.classList.remove("is-invalid");
    field.classList.add("is-valid");
    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = "";
        error.classList.remove("is-visible", "is-checking", "is-success");
    }
}

export function resetField(field) {
    field.classList.remove("is-valid", "is-invalid");
    const error = field.querySelector(".an-auth__error");

    if (error) {
        error.textContent = "";
        error.classList.remove("is-visible", "is-checking", "is-success");
    }
}

export function maskEmail(email) {
    const [name, domain] = email.split("@");
    const visible = name.slice(0, 2);
    return `${visible}${"*".repeat(Math.max(3, name.length - 2))}@${domain}`;
}

export function startCountdown(button, seconds = 60) {
    if (!button.dataset.originalText) {
        button.dataset.originalText = button.textContent.trim();
    }

    const originalText = button.dataset.originalText;
    button.disabled = true;
    button.innerHTML = `${originalText} (<span class="an-auth__countdown">${seconds}</span>s)`;
    const countdown = button.querySelector(".an-auth__countdown");

    const interval = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(interval);
            button.disabled = false;
            button.textContent = originalText;
        }
    }, 1000);

    return interval;
}