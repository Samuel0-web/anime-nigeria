// ======================================================================
// AUTH
// ======================================================================
import { emailRegex, nameRegex, setError, clearError, setValid,
    resetField
} from "./modules/auth/helpers.js";
import { initPasswordToggle } from "./modules/auth/password-toggle.js";
import { initEmailValidation } from "./modules/auth/email-validation.js";
import { initNameValidation } from "./modules/auth/name-validation.js";
import { initPasswordValidation } from "./modules/auth/password-validation.js";
import { initConfirmPassword } from "./modules/auth/confirm-password.js";
import { initForgotPassword } from "./modules/auth/forgot-password.js";
import { initButtonState } from "./modules/auth/button-state.js";
import { initResetPassword } from "./modules/auth/reset-password.js";
import { initUsername } from "./modules/auth/username.js";

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector(".an-auth__form");
    if (!form) return;
    const updateButtons = initButtonState(form);
    initPasswordToggle();
    initEmailValidation(form, updateButtons);
    initNameValidation(form, updateButtons);
    initPasswordValidation(form, updateButtons);
    initConfirmPassword(form, updateButtons);
    initForgotPassword(form);
    initResetPassword(form);
    initUsername(form, updateButtons);
});