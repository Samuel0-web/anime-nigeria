import { api, handleApiError } from "./api";
import { setLoading, clearLoading } from "./loading-state";
import { setError, clearError, setValid, showFormMessage, clearFormMessage } from "./helpers";
import { initEmailValidation } from "./email-validation";

export function initLogin(form, updateButtons) {
    if (document.body.dataset.page !== "login") return;
    if (!form) return;
    const button = form.querySelector(".an-auth__submit");
    const email = form.querySelector("#email");
    const password = form.querySelector("#password");
    const emailField = form.querySelector("#email").closest(".an-auth__field");
    const passwordField = form.querySelector("#password").closest(".an-auth__field");

    [email, password].forEach(input => {
        input.addEventListener("input", () => {
            clearFormMessage(form);
        });
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        clearFormMessage(form);
        clearError(emailField);
        clearError(passwordField);
        setLoading(button, "Signing in...");

        try {
            const response = await api("/auth/api/login", {
                method: "POST",
                body: new FormData(form)
            });

            if (!response.success) {
                if (response.type === "auth") {
                    showFormMessage(form, response.message);
                    return;
                }

                if (response.errors.email) {
                    setError(emailField, response.errors.email);
                }

                if (response.errors.password) {
                    setError(passwordField, response.errors.password);
                }

                return;
            }

            window.location.href = response.redirect;
        } catch (err) {
            handleApiError(err);
        } finally {
            clearLoading(button);
        }
    });

    password.addEventListener("input", () => {
        if (password.value.trim() === "") {
            setError(password.closest(".an-auth__field"), "Password is required.");
            updateButtons();
            return;
        }

        clearError(password.closest(".an-auth__field"));
        setValid(password.closest(".an-auth__field"));
        updateButtons();
    });
}