import { clearError, setError, maskEmail, startCountdown } from "./helpers.js";
import { setLoading, clearLoading } from "./loading-state.js";
import { api, handleApiError } from "./api.js";

export function initForgotPassword(form) {
    if (document.body.dataset.page !== "forgot-password") return;
    const email = form.querySelector('input[type="email"]');
    const intro = document.querySelector(".an-auth__intro");
    const success = document.querySelector(".an-auth__success");
    const resend = document.querySelector(".an-auth__resend");

    // Not the forgot-password page
    if (!email || !intro || !success) return;

    form.addEventListener("submit", async e => {
        // Only run on forgot-password page
        if (!document.querySelector(".an-auth__resend")) return;
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn.disabled) return;
        clearError(email.closest(".an-auth__field"));
        setLoading(submitBtn, "Sending...");
        
        try {
            const result = await api("/auth/api/forgot-password", {
                method: "POST",
                body: new FormData(form)
            });

            if (!result.success) {
                if (result.errors?.email) {
                    setError(email.closest(".an-auth__field"), result.errors.email);
                } else {
                    throw {
                        status: 500,
                        data: result,
                        message: result.message
                    };
                }

                return;
            }

            form.hidden = true;
            intro.hidden = true;
            success.hidden = false;

            success.querySelector(".an-auth__masked-email").textContent =
                maskEmail(result.email);

            requestAnimationFrame(() => {
                success.classList.add("is-visible");
            });

            startCountdown(resend, result.resend_after);
        } catch (err) {
            handleApiError(err);
        } finally {
            clearLoading(submitBtn);
        }
    });

    if (resend) {
        resend.addEventListener("click", async () => {
            if (resend.disabled) return;
            setLoading(resend, "Sending...");

            try {
                const result = await api("/auth/api/resend-password-reset", {
                    method: "POST"
                });

                if (!result.success) {
                    if (result.errors?.email) {
                        setError(email.closest(".an-auth__field"), result.errors.email);
                    } else {
                        throw {
                            status: 500,
                            data: result,
                            message: result.message
                        };
                    }
                    
                    clearLoading(resend);
                    return;
                }

                startCountdown(resend, result.resend_after);
            } catch (err) {
                handleApiError(err, "Could not resend email.");
                clearLoading(resend);
            }
        });
    }
}