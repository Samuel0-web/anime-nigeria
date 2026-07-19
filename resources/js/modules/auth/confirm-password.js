import { setValid, setError, resetField } from "./helpers.js";

export function initConfirmPassword(form, updateButtons) {
    const password = form.querySelector("#password");
    const confirmPassword = form.querySelector("#confirm-password");
    if (!password || !confirmPassword) return;
    const field = confirmPassword.closest(".an-auth__field");

    function validate() {
        if (confirmPassword.value === "") {
            resetField(field);
            updateButtons?.();
            return;
        }

        if (confirmPassword.value === password.value) {
            setValid(field);
        } else {
            setError(field, "Passwords do not match.");
        }

        updateButtons?.();
    }

    confirmPassword.addEventListener("input", validate);

    // Revalidate if password changes after confirm was filled
    password.addEventListener("input", () => {
        if (confirmPassword.value !== "") {
            validate();
        }
    });
}