import '../scss/app.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { initStickyHeader } from './modules/sticky-header.js';
import { initDropdowns } from './modules/dropdowns.js';
import { initMobileNav } from './modules/mobile-nav.js';
import { initScrollReveal } from './modules/scroll-reveal.js';
import { initGallery } from "./community-gallery.js";
import { initCommunityAwardsFaq } from "./community-awards.js";
import { initAwardsNominationForm } from "./award-nomination.js";
import { initVotingFaq } from "./award-voting.js";
import { initWinnersFaq } from "./award-winners.js";
import { initAnimeAwards } from "./anime-awards.js";
import { initContactFaq } from "./contact.js";
import { initTriviaFaq } from "./trivia.js";
import { initLeaderboardFaq } from "./trivia-leaderboard.js";
import './honoured-ones.js';
import './auth.js';
import './legals.js';

document.addEventListener('DOMContentLoaded', () => {
  initStickyHeader();
  initGallery();
  initCommunityAwardsFaq();
  initContactFaq();
  initAwardsNominationForm();
  initVotingFaq();
  initWinnersFaq();
  initTriviaFaq();
  initLeaderboardFaq();
  initAnimeAwards();
  initDropdowns();
  initMobileNav();
  initScrollReveal();
});

