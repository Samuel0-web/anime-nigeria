import "../scss/member.scss";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "@fontsource/atkinson-hyperlegible/400.css";
import "@fontsource/atkinson-hyperlegible/700.css";
import "@fontsource/jetbrains-mono/500.css";
import { initPreloader } from "./modules/preloader";
import { initLogout } from "./modules/logout";
import { initSidebar } from './member/sidebar';
import './member/dashboard';
import './member/awards-overview.js';
import './member/awards-nominations.js';
import './member/awards-voting.js';
import { initProfileModal } from './member/profile-modal';
import { initAchievementModal } from './member/achievements';
import { initSettingsPage } from './member/settings';

initPreloader();

document.addEventListener('DOMContentLoaded', () => {
    initSidebar({layoutId: 'akdLayout', sidebarId: 'akdSidebar', toggleBtnId: 'sidebarToggle',
        closeBtnId: 'sidebarClose', overlayId: 'akdOverlay',
        profileBtnId: 'profileDropdownTrigger', dropdownId: 'profileDropdown',
    });

    initProfileModal();
    initAchievementModal();
    initSettingsPage();
    initLogout();
});