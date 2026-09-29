import { MODE_INTRO_DURATION_MS } from "../../../data/trivia";

// One shared timeline shape for every mode intro (Quiz, True or False, and
// Double Points alike). Entrance, hold, and exit always sum to exactly
// MODE_INTRO_DURATION_MS, the same fixed deadline the global event clock in
// Trivia.jsx already uses to advance to the question (see
// utils/trivia/eventTimeline.js), so no mode's animation can ever be cut
// short and none of them needs to guess when it is "done", the deadline and
// the animation budget are the same number by construction.
export const ENTER_MS = 230;
export const EXIT_MS = 140;
export const HOLD_MS = MODE_INTRO_DURATION_MS - ENTER_MS - EXIT_MS;
export const TOTAL_MS = MODE_INTRO_DURATION_MS;
export const TIMES = [0, ENTER_MS / TOTAL_MS, (ENTER_MS + HOLD_MS) / TOTAL_MS, 1];
export const sec = (n) => n / 1000;