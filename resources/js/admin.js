import "../scss/admin.scss";
import "bootstrap-icons/font/bootstrap-icons.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "@fontsource/atkinson-hyperlegible/400.css";
import "@fontsource/atkinson-hyperlegible/700.css";
import "@fontsource/jetbrains-mono/500.css";
import { initPreloader } from "./modules/preloader";
import { initLogout } from "./modules/logout";
import { initAdminSidebar } from './admin/sidebar';
import { initAdminProfile } from './admin/profile';

initPreloader();

document.addEventListener('DOMContentLoaded', () => {
    initAdminSidebar({
        layoutId: 'akdLayout',
        sidebarId: 'akdSidebar',
        toggleBtnId: 'sidebarToggle',
        closeBtnId: 'sidebarClose',
        overlayId: 'akdOverlay',
    });

    initAdminProfile();
    initLogout();
});