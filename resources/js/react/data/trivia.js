// Mock data for the Trivia experience.
// Swap these exports for real API/DB-backed data later, nothing else in the
// Trivia feature should need to change shape-wise.
//
// The Trivia now runs as a background event: eventStartTime is fixed once
// per page load, and every phase (banner status, question, answer window)
// is derived from real elapsed time rather than waited for. See
// utils/trivia/eventTimeline.js for the derivation logic itself.

import { buildEventTimeline } from "../utils/trivia/eventTimeline";

export const PHASES = {
    WAITING: "WAITING",
    JOINING: "JOINING",
    QUESTION_INTRO: "QUESTION_INTRO",
    ANSWERING: "ANSWERING",
    RESULT: "RESULT",
    DOUBLE_POINTS: "DOUBLE_POINTS",
    FINAL_RESULT: "FINAL_RESULT",
    FULL_RESULTS: "FULL_RESULTS",
};

export const TICK_MS = 200;

// Real wall-clock timing. Nothing here is compressed or accelerated, the mock
// event genuinely takes this long, per instruction.
export const EVENT_START_OFFSET_MS = 20000; // event goes live 20s after page load
export const LIVE_SOON_THRESHOLD_MS = 10000; // final 10s of lead-in shows "Live Soon"
export const ENTRY_GRACE_MS = 2000; // join within 2s of a question starting to enter it directly
export const JOINING_READY_THRESHOLD_MS = 1200; // "You're in!" appears this close to the next entry point
export const RESULT_DISPLAY_MS = 5000; // personal, not part of the global timeline
export const DOUBLE_POINTS_DURATION_MS = 3500;

export const PLAYER_ID = "you";

export const triviaEvent = {
    id: 4,
    kicker: "Anime Trivia #04",
    title: "The Ultimate Anime Challenge",
    questionCount: 6,
    estimatedDuration: "Approx. 1 minute",
    bannerImage: "/uploads/frieren-poster.webp",
};

export const currentPlayer = {
    id: PLAYER_ID,
    username: "you_the_goat",
    avatar: "/uploads/upscalemedia-transformed.png",
};

// Other "live" participants. roundPoints[i] is what they score on question i+1.
// Round 6 is the Double Points round, their scores reflect that.
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

// Six questions. Only Question 5 carries an image. Only Question 6 is
// flagged doublePoints, the timeline builder reads that flag rather than
// hardcoding a "last question" assumption.
export const questions = [
    {
        id: 1,
        question: "What is the name of the Nine-Tails sealed within Naruto Uzumaki?",
        image: null,
        answers: ["Kurama", "Gyuki", "Isobu", "Kokuo"],
        correctAnswer: "Kurama",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 2,
        question: "What is the name of Monkey D. Luffy's pirate crew?",
        image: null,
        answers: ["Straw Hat Pirates", "Red Hair Pirates", "Heart Pirates", "Whitebeard Pirates"],
        correctAnswer: "Straw Hat Pirates",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 3,
        question: "Which organization does Eren Yeager join to fight Titans beyond the walls?",
        image: null,
        answers: ["Survey Corps", "Military Police", "Garrison Regiment", "Training Corps"],
        correctAnswer: "Survey Corps",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 4,
        question: "What breathing style does Tanjiro Kamado primarily use?",
        image: null,
        answers: ["Water Breathing", "Flame Breathing", "Thunder Breathing", "Insect Breathing"],
        correctAnswer: "Water Breathing",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 5,
        question: "Who is this character?",
        image: "/uploads/frieren-poster.webp",
        answers: ["Frieren", "Fern", "Himmel", "Serie"],
        correctAnswer: "Frieren",
        questionDuration: 5,
        answerDuration: 5,
    },
    {
        id: 6,
        question: "In One Piece, what is the name of the ancient weapon said to be capable of destroying a country?",
        image: null,
        answers: ["Pluton", "Uranus", "Poseidon", "Noah"],
        correctAnswer: "Pluton",
        questionDuration: 5,
        answerDuration: 5,
        doublePoints: true,
    },
];

// Precomputed once, the timeline is pure and only depends on the constants
// above. Swapping to a real backend later means fetching this shape from the
// server instead of building it locally, nothing downstream needs to change.
export const eventTimeline = buildEventTimeline(questions, DOUBLE_POINTS_DURATION_MS);