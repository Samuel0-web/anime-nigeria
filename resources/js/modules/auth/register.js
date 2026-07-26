import { clearError, setError, maskEmail, startCountdown, showFormMessage, clearFormMessage
} from "./helpers.js";

import { setLoading, clearLoading } from "./loading-state.js";
import { api, handleApiError } from "./api.js";
import { startCooldown } from "./utils/cooldown.js";

export function initRegister(form, updateButtons) {
    if (document.body.dataset.page !== "register") return;
    if (!form.classList.contains("an-auth__form")) return;
    const intro = document.querySelector(".an-auth__intro");
    const success = document.querySelector(".an-auth__success");
    if (!intro || !success) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn.disabled) return;
        clearFormMessage(form);
        form.querySelectorAll(".an-auth__field").forEach(clearError);
        form.querySelector(".an-auth__checkbox-error").textContent = "";
        setLoading(submitBtn, "Creating account...");

        try {
            const result = await api("/auth/api/register", {
                method: "POST",
                body: new FormData(form)
            });

            if (!result.success) {
                if (result.type === "auth" && result.meta?.cooldown) {
                    startCooldown(form, result.meta.cooldown,
                        (time) => {
                            showFormMessage(form, `Too many attempts. Try again in ${time}.`);
                        },
                        () => {
                            clearFormMessage(form);
                        }
                    );

                    return;
                }

                Object.entries(result.errors).forEach(([field, message]) => {
                    if (field === "terms") {
                        form.querySelector(".an-auth__checkbox-error").textContent = message;
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
            intro.hidden = true;
            document.querySelector(".an-auth__divider").hidden = true;
            document.querySelector(".an-auth__google").hidden = true;
            document.querySelector(".an-auth__footer").hidden = true;
            success.hidden = false;
            success.querySelector(".an-auth__masked-email").textContent = maskEmail(result.email);

            requestAnimationFrame(() => {
                success.classList.add("is-visible");
            });

            startCountdown(resend, result.resend_after);
        } catch (err) {
            handleApiError(err);
        } finally {
            clearLoading(submitBtn);
            updateButtons();
        }
    });

    const resend = success.querySelector(".an-auth__resend");

    if (resend) {
        resend.addEventListener("click", async () => {
            if (resend.disabled) return;
            setLoading(resend, "Sending...");

            try {
                const result = await api("/auth/api/resend-verification", {
                    method: "POST"
                });

                if (!result.success) {
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