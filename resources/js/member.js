import "../scss/member.scss";
import "bootstrap-icons/font/bootstrap-icons.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import "@fontsource/atkinson-hyperlegible/400.css";
import "@fontsource/atkinson-hyperlegible/700.css";
import "@fontsource/jetbrains-mono/500.css";
import { initPreloader } from "./modules/preloader";
import { initLogout } from "./modules/logout";
import { initSidebar } from './member/sidebar';
import './member/dashboard';
import { initProfileModal } from './member/profile-modal';
import { initAchievementModal } from './member/achievements';
import './member/awards-overview.js';
import './member/awards-nominations.js';
import './member/awards-voting.js';
import { initAnnouncementsFilter } from './member/announcements';
import './member/challenges.js';
import './member/community-awards-nominations.js';
import './member/community-awards-voting.js';
import './member/honoured-ones.js';
import './member/gallery.js';
import { initBlogSearch, initBlogCardImages } from './member/blog';
import { initCommentComposer, initTableOfContents, initCopyLink, initArticleLightbox,
    initReplyExpansion, initReplyComposers, initMobileCommentReply, 
    initMobileCommentsSheet, initCommentListHeight, initCommentDeletion, 
    initMobileCommentLongPressDelete }
    from './member/single-post';
import { initSettingsPage } from './member/settings';

initPreloader();

document.addEventListener('DOMContentLoaded', () => {
    initSidebar({layoutId: 'akdLayout', sidebarId: 'akdSidebar', 
        toggleBtnId: 'sidebarToggle', closeBtnId: 'sidebarClose', overlayId: 'akdOverlay',
        profileBtnId: 'profileDropdownTrigger', dropdownId: 'profileDropdown',
    });

    initProfileModal();
    initAchievementModal();
    initSettingsPage();
    initAnnouncementsFilter();
    initBlogSearch();
    initBlogCardImages();
    initCommentComposer();
    initTableOfContents();
    initCopyLink();
    initArticleLightbox();
    initReplyExpansion();
    initReplyComposers();
    initMobileCommentReply();
    initMobileCommentLongPressDelete();
    initMobileCommentsSheet();
    initCommentListHeight();
    initCommentDeletion();
    initLogout();
});