import { clearError, setError, maskEmail, startCountdown } from "./helpers.js";
import { setLoading, clearLoading } from "./loading-state.js";
import { api, handleApiError } from "./api.js";

export function initResetPassword(form) {
    if (document.body.dataset.page !== "reset-password") return;
    if (!form) return;
    const success = document.querySelector(".an-auth__success");
    const intro = document.querySelector(".an-auth__intro");
    const footer = document.querySelector(".an-auth__footer");
    const divider = document.querySelector(".an-auth__divider2");
    if (!success) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn.disabled) return;
        form.querySelectorAll(".an-auth__field").forEach(clearError);
        setLoading(submitBtn, "Updating password...");

        try {
            const result = await api("/auth/api/reset-password", {
                method: "POST",
                body: new FormData(form)
            });

            if (!result.success) {
                Object.entries(result.errors ?? {}).forEach(([field, message]) => {
                    if (field === "token") {
                        handleApiError({message});
                        return;
                    }

                    const input = form.querySelector(`[name="${field}"]`);

                    if (input) {
                        setError(input.closest(".an-auth__field"), message);
                    }
                });

                return;
            }

            form.hidden = true;
            if (intro) intro.hidden = true;
            if (footer) footer.hidden = true;
            if (divider) divider.hidden = true;
            success.hidden = false;

            requestAnimationFrame(() => {
                success.classList.add("is-visible");
            });
        } catch (err) {
            handleApiError(err);
        } finally {
            clearLoading(submitBtn);
        }
    });
}