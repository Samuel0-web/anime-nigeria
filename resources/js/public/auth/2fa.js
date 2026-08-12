// ======================================================================
// TWO-FACTOR AUTHENTICATION
// ======================================================================
import { api } from "../../modules/api.js";
import { setLoading, clearLoading } from "../../modules/loading-state.js";
import { setError, clearError, showFormMessage, clearFormMessage } from "./helpers.js";

const COPY = {
    totp: {
        description: "Enter the 6-digit code from your authenticator app to verify your identity.",
        switchLabel: "Use a recovery code instead",
    },
    recovery: {
        description: "Enter one of your recovery codes to continue. Each recovery code can only be used once.",
        switchLabel: "Use authenticator code instead",
    },
};

export function initTwoFactor() {
    if (document.body.dataset.page !== "2fa") return;
    const totpForm = document.getElementById("totp-form");
    const recoveryForm = document.getElementById("recovery-form");
    if (!totpForm || !recoveryForm) return;
    const switchButton = document.getElementById("switch-mode");
    const description = document.getElementById("2fa-description");
    const otpField = totpForm.querySelector(".an-auth__field--otp");
    const otpInputs = Array.from(totpForm.querySelectorAll(".an-auth__otp-input"));
    const totpSubmit = totpForm.querySelector(".an-auth__submit");
    const recoveryInput = document.getElementById("recovery-code");
    const recoveryField = recoveryInput.closest(".an-auth__field");
    const recoverySubmit = recoveryForm.querySelector(".an-auth__submit");
    let mode = "totp";

    // ---------------------------------------------------------------
    // OTP HELPERS
    // ---------------------------------------------------------------
    function getOtpValue() {
        return otpInputs.map(input => input.value).join("");
    }

    function updateTotpButtonState() {
        totpSubmit.disabled = getOtpValue().length !== otpInputs.length;
    }

    function clearOtpInputs() {
        otpInputs.forEach(input => (input.value = ""));
        updateTotpButtonState();
    }

    function focusFirstOtpInput() {
        otpInputs[0]?.focus();
    }

    function fillOtpFromDigits(digits) {
        const trimmed = digits.slice(0, otpInputs.length);

        otpInputs.forEach((input, i) => {
            input.value = trimmed[i] ?? "";
        });

        const nextIndex = Math.min(trimmed.length, otpInputs.length - 1);
        otpInputs[nextIndex].focus();
        clearError(otpField);
        updateTotpButtonState();
    }

    // ---------------------------------------------------------------
    // OTP INPUT BEHAVIOR
    // ---------------------------------------------------------------
    otpInputs.forEach((input, index) => {
        input.addEventListener("input", () => {
            const digits = input.value.replace(/\D/g, "");

            // Handles bulk-fill from OS/browser one-time-code autofill.
            if (digits.length > 1) {
                fillOtpFromDigits(digits);
                return;
            }

            input.value = digits;

            if (digits && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            clearError(otpField);
            updateTotpButtonState();
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace") {
                if (input.value === "" && index > 0) {
                    e.preventDefault();
                    otpInputs[index - 1].value = "";
                    otpInputs[index - 1].focus();
                    updateTotpButtonState();
                }
                return;
            }

            if (e.key === "ArrowLeft" && index > 0) {
                e.preventDefault();
                otpInputs[index - 1].focus();
            }

            if (e.key === "ArrowRight" && index < otpInputs.length - 1) {
                e.preventDefault();
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener("paste", (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData("text");
            const digits = pasted.replace(/\s/g, "").replace(/\D/g, "");
            if (digits) fillOtpFromDigits(digits);
        });
    });

    // ---------------------------------------------------------------
    // RECOVERY INPUT BEHAVIOR
    // ---------------------------------------------------------------
    function updateRecoveryButtonState() {
        recoverySubmit.disabled = recoveryInput.value.trim() === "";
    }

    recoveryInput.addEventListener("input", () => {
        clearError(recoveryField);
        updateRecoveryButtonState();
    });

    // ---------------------------------------------------------------
    // MODE SWITCHING
    // ---------------------------------------------------------------
    switchButton.addEventListener("click", () => {
        mode = mode === "totp" ? "recovery" : "totp";
        const isRecovery = mode === "recovery";
        totpForm.hidden = isRecovery;
        recoveryForm.hidden = !isRecovery;
        description.textContent = COPY[mode].description;
        switchButton.textContent = COPY[mode].switchLabel;
        clearFormMessage(totpForm);
        clearFormMessage(recoveryForm);
        clearError(otpField);
        clearError(recoveryField);
        clearOtpInputs();
        recoveryInput.value = "";
        updateRecoveryButtonState();
        isRecovery ? recoveryInput.focus() : focusFirstOtpInput();
    });

    // ---------------------------------------------------------------
    // ERROR HANDLING
    // ---------------------------------------------------------------
    function handleVerificationError(response, { form, field, onFieldError }) {
        // Field-level: bad/missing code — stay put, let them retry.
        if (response.type === "auth" && response.errors?.code) {
            setError(field, response.errors.code);
            onFieldError?.();
            return;
        }

        // Flow-level: invalid/expired 2FA session — bail to login.
        const message = response.message || response.errors?.general ||
            "Something went wrong. Please try again.";

        showFormMessage(form, message);

        if (response.type === "auth" && response.errors?.general) {
            setTimeout(() => {
                window.location.href = "/login";
            }, 2500);
        }
    }

    // ---------------------------------------------------------------
    // SUBMIT: TOTP
    // ---------------------------------------------------------------
    totpForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (totpSubmit.disabled) return;

        clearFormMessage(totpForm);
        clearError(otpField);
        setLoading(totpSubmit, "Verifying...");

        try {
            const formData = new FormData();
            formData.append("method", "totp");
            formData.append("code", getOtpValue());

            const response = await api("/auth/api/verify-2fa", {
                method: "POST",
                body: formData,
            });

            if (!response.success) {
                handleVerificationError(response, {
                    form: totpForm,
                    field: otpField,
                    onFieldError: () => {
                        clearOtpInputs();
                        focusFirstOtpInput();
                    },
                });
                return;
            }

            window.location.href = response.redirect;
        } finally {
            clearLoading(totpSubmit);
            updateTotpButtonState();
        }
    });

    // ---------------------------------------------------------------
    // SUBMIT: RECOVERY
    // ---------------------------------------------------------------
    recoveryForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (recoverySubmit.disabled) return;
        clearFormMessage(recoveryForm);
        clearError(recoveryField);
        setLoading(recoverySubmit, "Verifying...");

        try {
            const formData = new FormData();
            formData.append("method", "recovery");
            formData.append("code", recoveryInput.value.trim());

            const response = await api("/auth/api/verify-2fa", {
                method: "POST",
                body: formData,
            });

            if (!response.success) {
                handleVerificationError(response, {
                    form: recoveryForm,
                    field: recoveryField,
                    onFieldError: () => {
                        recoveryInput.value = "";
                        updateRecoveryButtonState();
                        recoveryInput.focus();
                    },
                });
                return;
            }

            window.location.href = response.redirect;
        } finally {
            clearLoading(recoverySubmit);
            updateRecoveryButtonState();
        }
    });

    // ---------------------------------------------------------------
    // INITIAL STATE
    // ---------------------------------------------------------------
    updateTotpButtonState();
    updateRecoveryButtonState();
    focusFirstOtpInput();
}