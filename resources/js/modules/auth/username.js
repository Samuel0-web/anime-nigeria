import { setError, clearError, resetField } from "./helpers.js";

export function initUsername(form, updateButtons) {
    const input = form.querySelector("#username");
    if (!input) return;
    const field = input.closest(".an-auth__field");
    const preview = document.querySelector(".an-auth__username-preview");
    const previewValue = document.querySelector(".an-auth__username-preview-value");
    const error = field.querySelector(".an-auth__error");
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;

    // Fake reserved usernames
    const reserved = [
        "admin",
        "administrator",
        "owner",
        "support",
        "staff",
        "system",
        "official",
        "anime",
        "animenigeria"
    ];

    let debounce;

    input.addEventListener("input", () => {
        clearTimeout(debounce);
        const value = input.value.trim();

        // -------------------------
        // Empty
        // -------------------------
        if (value === "") {
            preview.hidden = true;
            preview.classList.remove("is-visible");
            resetField(field);
            updateButtons();
            return;
        }

        // -------------------------
        // Preview
        // -------------------------
        preview.hidden = false;

        requestAnimationFrame(() => {
            preview.classList.add("is-visible");
        });

        previewValue.textContent = `@${value}`;

        // -------------------------
        // Live validation
        // -------------------------
        if (value.length < 3) {
            error.classList.remove("is-success", "is-checking");
            field.dataset.available = "false";
            setError(field, "Username must be at least 3 characters.");
            updateButtons();
            return;
        }

        if (!usernameRegex.test(value)) {
            error.classList.remove("is-success", "is-checking");
            field.dataset.available = "false";
            setError(field, "Only letters, numbers and underscores are allowed.");
            updateButtons();
            return;
        }

        error.classList.remove("is-success", "is-checking");
        resetField(field);
        delete field.dataset.available;

        // Looks valid so far
        field.classList.remove("is-invalid");
        field.dataset.available = "checking";
        error.classList.remove("is-success");
        error.classList.add("is-checking");
        error.textContent = "Checking...";

        // -------------------------
        // Fake availability check
        // -------------------------
        debounce = setTimeout(() => {
            const currentValue = input.value.trim().toLowerCase();
            error.classList.remove("is-checking");

            if (reserved.includes(currentValue)) {
                error.classList.remove("is-success");
                field.dataset.available = "false";
                setError(field, "Username is already taken.");
            } else {
                field.dataset.available = "true";
                field.classList.remove("is-invalid");
                error.classList.add("is-success");
                error.textContent = "Username is available.";
            }

            updateButtons();
        }, 500);
    });
}