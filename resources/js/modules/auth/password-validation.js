import { setValid, resetField } from "./helpers.js";

export function initPasswordValidation(form, updateButtons) {
    const password = form.querySelector("#password");
    const rules = form.querySelector(".an-auth__password-rules");
    if (!password) return;

    // Login page has no password rules
    if (!rules) {
        password.addEventListener("input", () => {
            updateButtons?.();
        });

        return;
    }

    const field = password.closest(".an-auth__field");

    const ruleChecks = {
        length: value => value.length >= 8,
        upper: value => /[A-Z]/.test(value),
        number: value => /\d/.test(value),
        symbol: value => /[!@#$%&*?,]/.test(value)
    };

    password.addEventListener("input", () => {
        const value = password.value;
        let completed = 0;

        Object.entries(ruleChecks).forEach(([key, check]) => {
            const item = rules.querySelector(`[data-rule="${key}"]`);

            if (check(value)) {
                completed++;
                item.classList.add("is-valid");
            } else {
                item.classList.remove("is-valid");
            }
        });

        if (value === "") {
            resetField(field);
            rules.classList.remove("is-complete");
            updateButtons?.();
            return;
        }

        if (completed === 4) {
            setValid(field);
            rules.classList.add("is-complete");
        } else {
            resetField(field);
            rules.classList.remove("is-complete");
        }

        updateButtons?.();
    });
}