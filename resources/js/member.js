import "../scss/member.scss";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { initPreloader } from "./modules/preloader";
import { initSidebar } from './member/sidebar';
import { initProfileModal } from './member/profile-modal';
import { initAchievementModal } from './member/achievements';

initPreloader();

document.addEventListener('DOMContentLoaded', () => {
    initSidebar({layoutId: 'akdLayout', sidebarId: 'akdSidebar', toggleBtnId: 'sidebarToggle',
        closeBtnId: 'sidebarClose', overlayId: 'akdOverlay',
        profileBtnId: 'profileDropdownTrigger', dropdownId: 'profileDropdown',
    });

    initProfileModal();
    initAchievementModal();
});