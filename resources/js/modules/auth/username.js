import { setError, clearError, resetField } from "./helpers.js";
import { success } from "../toast.js";

export function initUsername(form, updateButtons) {
    const input = form.querySelector("#username");
    if (!input) return;
    const field = input.closest(".an-auth__field");
    const preview = document.querySelector(".an-auth__username-preview");
    const previewValue = document.querySelector(".an-auth__username-preview-value");
    const error = field.querySelector(".an-auth__error");
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;

    let debounce;

    input.addEventListener("input", () => {
        clearTimeout(debounce);
        const username = input.value.trim();

        // -------------------------
        // Empty
        // -------------------------
        if (username === "") {
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

        previewValue.textContent = `@${username}`;

        // -------------------------
        // Live validation
        // -------------------------
        if (username.length < 3) {
            error.classList.remove("is-success", "is-checking");
            field.dataset.available = "false";
            setError(field, "Username must be at least 3 characters.");
            updateButtons();
            return;
        }

        if (!usernameRegex.test(username)) {
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

        debounce = setTimeout(async () => {
            try {
                const response = await fetch("/auth/api/check-username", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: new URLSearchParams({
                        username,
                    }),
                });

                const result = await response.json();
                error.classList.remove("is-checking");

                if (result.success) {
                    field.dataset.available = "true";
                    field.classList.remove("is-invalid");

                    error.classList.add("is-success");
                    error.textContent = "Username is available.";
                } else {
                    field.dataset.available = "false";

                    error.classList.remove("is-success");
                    setError(field, result.errors.username ?? "Username is unavailable.");
                }

                updateButtons();
            } catch (e) {
                field.dataset.available = "false";
                error.classList.remove("is-checking");
                setError(field, "Unable to check username. Please try again.");
                updateButtons();
            }
        }, 500);
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const username = input.value.trim();

        if (field.dataset.available !== "true") {
            return;
        }

        const button = form.querySelector("button");
        button.disabled = true;

        const response = await fetch("/auth/api/complete-registration", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                username,
            }),
        });

        const result = await response.json();
        button.disabled = false;

        if (result.success) {
            success("Account created successfully.")
            window.location = result.redirect;
            return;
        }

        if (result.errors?.username) {
            setError(field, result.errors.username);
        }
    });
}