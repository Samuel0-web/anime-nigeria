export function initButtonState(form) {
    const password = form.querySelector("#password");
    const email = form.querySelector('input[type="email"]');
    const fullname = form.querySelector("#fullname");
    const username = form.querySelector("#username");
    const confirmPassword = form.querySelector("#confirm-password");
    const submit = form.querySelector(".an-auth__submit");
    const terms = form.querySelector("#terms");
    const hasEmail = !!email;
    const hasPassword = !!password;
    const hasConfirm = !!confirmPassword;
    const hasName = !!fullname;
    const hasTerms = !!terms;
    
    function areFieldsValid() {
        const page = document.body.dataset.page;

        if (page === "google-register") {
            return true;
        }

        // Forgot Password
        if (hasEmail && !hasPassword) {
            return email.closest(".an-auth__field").classList.contains("is-valid");
        }

        // Reset Password
        if (!hasEmail && hasPassword && hasConfirm) {
            return (password.closest(".an-auth__field").classList.contains("is-valid") &&
                confirmPassword.closest(".an-auth__field").classList.contains("is-valid")
            );
        }

        // Login
        if (hasEmail && hasPassword && !hasTerms) {
            return (email.closest(".an-auth__field").classList.contains("is-valid") &&
                password.value.trim() !== ""
            );
        }

        // Username page
        if (username) {
            const field = username.closest(".an-auth__field");
            return (username.value.trim() !== "" && field.dataset.available === "true");
        }

        // Register
        return (fullname.closest(".an-auth__field").classList.contains("is-valid") &&
            email.closest(".an-auth__field").classList.contains("is-valid") &&
            password.closest(".an-auth__field").classList.contains("is-valid") &&
            confirmPassword.closest(".an-auth__field").classList.contains("is-valid")
        );
    }

    function updateButtons() {
        const valid = areFieldsValid();
        const agreed = terms?.checked ?? false;

        // Register only
        if (hasTerms) {
            const agreed = terms.checked;

            if (submit) {
                submit.disabled = !(valid && agreed);
            }

            return;
        }

        // Login, Forgot Password, Reset Password
        if (submit) {
            submit.disabled = !valid;
        }
    }

    if (terms) {
        terms.addEventListener("change", updateButtons);
    }

    updateButtons();
    return updateButtons;
}