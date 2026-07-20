export function initResetPassword(form) {
    if (document.body.dataset.page !== "reset-password") return;
    const success = document.querySelector(".an-auth__success");
    const intro = document.querySelector(".an-auth__intro");
    const footer = document.querySelector(".an-auth__footer");
    const divider = document.querySelector(".an-auth__divider2");

    if (!success) return;

    form.addEventListener("submit", e => {
        e.preventDefault();

        form.hidden = true;

        if (intro) intro.hidden = true;
        if (footer) footer.hidden = true;
        if (divider) divider.hidden = true;

        success.hidden = false;

        requestAnimationFrame(() => {
            success.classList.add("is-visible");
        });
    });
}