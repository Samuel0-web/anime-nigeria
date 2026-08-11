// ======================================================================
// AUTH
// ======================================================================
import { emailRegex, nameRegex, setError, clearError, setValid,
    resetField
} from "./helpers.js";
import { initPasswordToggle } from "./password-toggle.js";
import { initEmailValidation } from "./email-validation.js";
import { initNameValidation } from "./name-validation.js";
import { initPasswordValidation } from "./password-validation.js";
import { initConfirmPassword } from "./confirm-password.js";
import { initForgotPassword } from "./forgot-password.js";
import { initButtonState } from "./button-state.js";
import { initResetPassword } from "./reset-password.js";
import { initUsername } from "./username.js";
import { initRegister } from "./register.js";
import { initGoogleRegister } from "./google-register.js";
import { initLogin } from "./login.js";
import { initTwoFactor } from "./2fa.js";
import { initGoogleAuth } from "./helpers.js";
import { error as errorToast } from "../../modules/toast.js";

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
    initRegister(form, updateButtons);
    initGoogleRegister(form);
    initLogin(form, updateButtons);
    initGoogleAuth();
    initTwoFactor();

    if (window.oauthError) {
        errorToast(window.oauthError);
        delete window.oauthError;
    }
});