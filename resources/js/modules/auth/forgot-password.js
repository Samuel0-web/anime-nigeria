import { maskEmail } from "./helpers.js";

export function initForgotPassword(form) {
    if (document.body.dataset.page !== "forgot-password") return;
    const email = form.querySelector('input[type="email"]');
    const intro = document.querySelector(".an-auth__intro");
    const success = document.querySelector(".an-auth__success");

    // Not the forgot-password page
    if (!email || !intro || !success) return;

    let countdownInterval;

    function startCountdown() {
        const button = document.querySelector(".an-auth__resend");

        if (!button) return;

        let seconds = 60;

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

    form.addEventListener("submit", e => {
        // Only run on forgot-password page
        if (!document.querySelector(".an-auth__resend")) return;

        e.preventDefault();

        form.style.display = "none";
        intro.hidden = true;
        success.hidden = false;

        success.querySelector(".an-auth__masked-email").textContent =
            maskEmail(email.value.trim());

        requestAnimationFrame(() => {
            success.classList.add("is-visible");
        });

        startCountdown();
    });

    const resend = document.querySelector(".an-auth__resend");

    if (resend) {
        resend.addEventListener("click", () => {

            // TODO:
            // fetch("/forgot-password/resend"...)

            startCountdown();
        });
    }
}