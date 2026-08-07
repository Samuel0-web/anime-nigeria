import { useConfirmDialog } from './confirm-dialog';

export function initLogout() {
    const confirmDialog = useConfirmDialog();

    document.querySelectorAll('form[data-logout-form]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const confirmed = await confirmDialog.ask({
                title: 'Sign out?',
                message: 'Are you sure you want to sign out of your account?',
                confirmLabel: 'Sign Out',
                cancelLabel: 'Stay Signed In',
                destructive: true,
            });

            if (confirmed) {
                form.submit();
            }
        });
    });
}