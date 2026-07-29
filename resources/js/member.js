import "../scss/member.scss";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { initPreloader } from "./modules/preloader";
import { initSidebar } from './member/sidebar';

initPreloader();

document.addEventListener('DOMContentLoaded', () => {
    initSidebar({
        dashboardId: 'akdDashboard',
        sidebarId: 'akdSidebar',
        toggleBtnId: 'sidebarToggle',
        closeBtnId: 'sidebarClose',
        overlayId: 'akdOverlay',
        profileBtnId: 'profileDropdownTrigger',
        dropdownId: 'profileDropdown',
    });
});