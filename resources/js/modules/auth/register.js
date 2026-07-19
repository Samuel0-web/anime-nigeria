import { clearError, setError, maskEmail } from "./helpers.js";

export function initRegister(form) {
    if (!form.classList.contains("an-auth__form")) return;
    const intro = document.querySelector(".an-auth__intro");
    const success = document.querySelector(".an-auth__success");
    if (!intro || !success) return;
    let countdownInterval;

    function startCountdown(seconds = 60) {
        const button = success.querySelector(".an-auth__resend");
        if (!button) return;
        clearInterval(countdownInterval);
        button.disabled = true;
        button.innerHTML = `Resend Email (<span class="an-auth__countdown">${seconds}</span>s)`;
        const countdown = button.querySelector(".an-auth__countdown");

        countdownInterval = setInterval(() => {
            seconds--;
            countdown.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(countdownInterval);
                button.disabled = false;
                button.textContent = "Resend Email";
            }
        }, 1000);
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        const response = await fetch("/auth/api/register", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        // Clear previous errors
        form.querySelectorAll(".an-auth__field").forEach(clearError);
        form.querySelector(".an-auth__checkbox-error").textContent = "";

        if (!result.success) {
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

        startCountdown(result.resend_after);
    });

    const resend = success.querySelector(".an-auth__resend");

    if (resend) {
        resend.addEventListener("click", async () => {

            // TODO:
            // await fetch("/auth/api/resend-verification")

            startCountdown(res.resend_after);
        });
    }
}