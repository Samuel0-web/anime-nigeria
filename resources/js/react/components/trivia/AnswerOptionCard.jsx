import { motion } from "motion/react";

// Fixed per shape, not per color, colors are what get shuffled per question.
const SHAPE_PATHS = {
    triangle: <path d="M8 2 L14.5 13.5 H1.5 Z" />,
    diamond: <path d="M8 1.5 L14.5 8 L8 14.5 L1.5 8 Z" />,
    circle: <circle cx="8" cy="8" r="6.25" />,
    square: <rect x="2" y="2" width="12" height="12" rx="1.5" />,
};

function ShapeIcon({ shape }) {
    return (
        <svg className="akd-answer-card__shape" viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true">
            {SHAPE_PATHS[shape]}
        </svg>
    );
}

// No entrance animation props on purpose, only whileTap. An untimed
// entrance means the card is already in its final state and interactive
// the instant this mounts, the five-second answer timer never gets eaten
// by a staggered reveal.
export default function AnswerOptionCard({ label, color, shape, isSelected, disabled, onClick }) {
    return (
        <motion.button type="button"
            className={`akd-answer-card akd-answer-card--${color} ${isSelected ? "is-selected" : ""}`}
            onClick={onClick} disabled={disabled} whileTap={{ scale: 0.97 }}
        >
            <ShapeIcon shape={shape} />
            <span className="akd-answer-card__label">{label}</span>
        </motion.button>
    );
}