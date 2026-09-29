// Quiz-mode answer styling. Shape is pinned to its A/B/C/D position (fixed
// by the visual reference), only the color assigned to each position is
// shuffled, and only for quiz-mode questions, per the reference's own
// scoping ("For Quiz mode, the four colours must appear in a random order").
// True/False keeps a fixed color identity instead, since teal/purple IS
// that mode's visual identity, not something meant to vary.

const QUIZ_SHAPES = ["triangle", "diamond", "circle", "square"]; // fixed order = A, B, C, D
const QUIZ_COLORS = ["red", "blue", "yellow", "green"];

function shuffle(array) {
    const result = [...array];

    for (let i = result.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [result[i], result[j]] = [result[j], result[i]];
    }
    
    return result;
}

const TRUE_FALSE_STYLES = [
    { shape: "circle", color: "teal" },
    { shape: "triangle", color: "purple" },
];

// Called once when a question segment is entered (never mid-question), the
// result is stored in Trivia.jsx state and stays stable until the next
// question is entered.
export function getAnswerStyles(question) {
    if (question.mode === "true_false") return TRUE_FALSE_STYLES;
    const colors = shuffle(QUIZ_COLORS);
    return QUIZ_SHAPES.map((shape, i) => ({ shape, color: colors[i] }));
}