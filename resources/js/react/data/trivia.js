// Mock data for the Trivia experience.
// Swap these exports for real API/DB-backed data later, nothing else in the
// Trivia feature should need to change shape-wise.
//
// The Trivia runs as a background event: eventStartTime is fixed once per
// page load, and every phase is derived from real elapsed time. See
// utils/trivia/eventTimeline.js for the derivation logic.
//
// Each question now carries a `mode` ("quiz" or "true_false"). A MODE_INTRO
// segment is inserted whenever the mode changes between consecutive
// questions, or whenever a question is flagged `doublePoints` (which always
// gets its own intro regardless of mode continuity). See
// utils/trivia/eventTimeline.js for exactly how that's derived.

import { buildEventTimeline } from "../utils/trivia/eventTimeline";

export const PHASES = {
    WAITING: "WAITING",
    JOINING: "JOINING",
    MODE_INTRO: "MODE_INTRO",
    QUESTION_INTRO: "QUESTION_INTRO",
    ANSWERING: "ANSWERING",
    RESULT: "RESULT",
    FINAL_RESULT: "FINAL_RESULT",
    FULL_RESULTS: "FULL_RESULTS",
};

export const TICK_MS = 200;

// Real wall-clock timing, nothing here is compressed or accelerated.
export const EVENT_START_OFFSET_MS = 20000;
export const LIVE_SOON_THRESHOLD_MS = 10000;
export const ENTRY_GRACE_MS = 2000;
export const JOINING_READY_THRESHOLD_MS = 1200;
export const RESULT_DISPLAY_MS = 5000;
// Shared by every mode intro (Quiz, True or False, Double Points alike),
// see components/trivia/ModeIntro/modeIntroTiming.js for how this splits
// into entrance/hold/exit.
export const MODE_INTRO_DURATION_MS = 4000;
export const PLAYER_ID = "you";

export const triviaEvent = {
    id: 4,
    kicker: "Anime Trivia #04",
    title: "The Ultimate Anime Challenge",
    questionCount: 6,
    estimatedDuration: "Approx. 1 minute 10 seconds",
    bannerImage: "/uploads/frieren-poster.webp",
};

export const currentPlayer = {
    id: PLAYER_ID,
    username: "you_the_goat",
    avatar: "/uploads/upscalemedia-transformed.png",
};

export const opponents = [
    { id: "p1", username: "GokuSSJ", avatar: "/uploads/logos/upscalemedia-transformed%20(1).png", roundPoints: [820, 750, 900, 680, 790, 1600] },
    { id: "p2", username: "SakuraBloom", avatar: "/uploads/upscalemedia-transformed%20(2).png", roundPoints: [600, 820, 500, 900, 650, 900] },
    { id: "p3", username: "LuffyKing", avatar: "/uploads/upscalemedia-transformed%20(3).png", roundPoints: [750, 600, 820, 750, 900, 1200] },
    { id: "p4", username: "ZeroTwo_02", avatar: "/uploads/upscalemedia-transformed.png", roundPoints: [500, 700, 650, 800, 700, 800] },
    { id: "p5", username: "TanjiroFlame", avatar: "/uploads/logos/upscalemedia-transformed%20(1).png", roundPoints: [900, 500, 700, 600, 800, 1400] },
    { id: "p6", username: "MikasaAckerman", avatar: "/uploads/upscalemedia-transformed%20(2).png", roundPoints: [650, 900, 750, 500, 600, 1000] },
];

export function createInitialLeaderboard() {
    return [
        ...opponents.map((o) => ({ id: o.id, username: o.username, avatar: o.avatar, points: 0, roundPoints: o.roundPoints })),
        { id: currentPlayer.id, username: currentPlayer.username, avatar: currentPlayer.avatar, points: 0 },
    ];
}

// Six questions. Q1, Q2, Q4, Q5 are quiz mode. Q3 is true_false (converted
// from its original 4-answer form to demonstrate the mode, its underlying
// anime-trivia content is preserved as a true/false statement instead).
// Q6 stays quiz-format but is flagged doublePoints, which always gets its
// own mode intro regardless of the surrounding mode.
export const questions = [
    {
        id: 1,
        mode: "quiz",
        question: "What is the name of the Nine-Tails sealed within Naruto Uzumaki?",
        image: null,
        answers: ["Kurama", "Gyuki", "Isobu", "Kokuo"],
        correctAnswer: "Kurama",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 2,
        mode: "quiz",
        question: "What is the name of Monkey D. Luffy's pirate crew?",
        image: null,
        answers: ["Straw Hat Pirates", "Red Hair Pirates", "Heart Pirates", "Whitebeard Pirates"],
        correctAnswer: "Straw Hat Pirates",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 3,
        mode: "true_false",
        question: "Eren Yeager joins the Survey Corps to fight Titans beyond the walls.",
        image: null,
        answers: ["True", "False"],
        correctAnswer: "True",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 4,
        mode: "quiz",
        question: "What breathing style does Tanjiro Kamado primarily use?",
        image: null,
        answers: ["Water Breathing", "Flame Breathing", "Thunder Breathing", "Insect Breathing"],
        correctAnswer: "Water Breathing",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 5,
        mode: "quiz",
        question: "Who is this character?",
        image: "/uploads/frieren-poster.webp",
        answers: ["Frieren", "Fern", "Himmel", "Serie"],
        correctAnswer: "Frieren",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 6,
        mode: "quiz",
        question: "In One Piece, what is the name of the ancient weapon said to be capable of destroying a country?",
        image: null,
        answers: ["Pluton", "Uranus", "Poseidon", "Noah"],
        correctAnswer: "Pluton",
        questionDuration: 5,
        answerDuration: 5,
        doublePoints: true,
    },
];

export const eventTimeline = buildEventTimeline(questions, MODE_INTRO_DURATION_MS);