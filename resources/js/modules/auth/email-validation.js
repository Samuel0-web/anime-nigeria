import { emailRegex, setValid, setError, resetField } from "./helpers.js";

export function initEmailValidation(form, updateButtons) {
    const email = form.querySelector('input[type="email"]');
    if (!email) return;
    const field = email.closest(".an-auth__field");

    email.addEventListener("input", () => {
        const value = email.value.trim();

        if (value === "") {
            resetField(field);
            updateButtons?.();
            return;
        }

        if (emailRegex.test(value)) {
            setValid(field);
        } else {
            setError(field, "Enter a valid email address.");
        }

        updateButtons?.();
    });
}