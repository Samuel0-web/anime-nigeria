import { api, handleApiError } from "./api.js";
import { setLoading, clearLoading } from "./loading-state.js";

export function initGoogleRegister(form) {
    if (document.body.dataset.page !== "google-register") return;
    if (!form) return;
    const submitBtn = form.querySelector(".an-auth__submit");

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (submitBtn.disabled) return;
        form.querySelector(".an-auth__checkbox-error").textContent = "";
        setLoading(submitBtn, "Creating account...");

        try {
            const result = await api("/auth/api/google-register", {
                method: "POST",
                body: new FormData(form),
            });

            if (!result.success) {
                if (result.errors?.terms) {
                    form.querySelector(".an-auth__checkbox-error").textContent = result.errors.terms;
                }

                return;
            }

            window.location.href = result.redirect;
        } catch (err) {
            handleApiError(err);
        } finally {
            clearLoading(submitBtn);
        }
    });
}