import { motion, useReducedMotion } from "motion/react";
import { ShapeGlyph, QUIZ_SHAPES, TRUE_FALSE_SHAPES } from "./ModeIntro/modevisuals";

const MODE_LABEL = {
    quiz: "Quiz",
    true_false: "True or False",
    double_points: "Double Points",
};

// Mirrors the actual mode-intro's structure (shapes grouped together,
// positioned above the label, matching QuizIntro.jsx / TrueFalseIntro.jsx)
// rather than listing shapes and label side by side in one flat row.
function ShapesGroup({ shapes, layout }) {
    return (
        <span className={`akd-mode-indicator__shapes akd-mode-indicator__shapes--${layout}`}>
            {shapes.map((s) => (
                <span key={s.key} className={`akd-mode-indicator__chip akd-answer-card--${s.color}`}>
                    <ShapeGlyph shape={s.shape} size={9} />
                </span>
            ))}
        </span>
    );
}

function DoublePointsMini() {
    return (
        <span className="akd-mode-indicator__dp-ring">
            <span className="akd-mode-indicator__dp-inner">X2</span>
        </span>
    );
}

// Never replays the large mode intro, this only ever fades in a static
// composition, key={mode} gives the subtle entrance when the mode changes.
export default function ModeIndicator({ mode }) {
    const prefersReducedMotion = useReducedMotion();
    const isDoublePoints = mode === "double_points";
    const isTrueFalse = mode === "true_false";
    const shapes = isTrueFalse ? TRUE_FALSE_SHAPES : QUIZ_SHAPES;

    return (
        <motion.div className="akd-mode-indicator" key={mode}
            initial={prefersReducedMotion ? false : { opacity: 0, y: -4 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.2 }}
        >
            {isDoublePoints ? (
                <>
                    <span className="akd-mode-indicator__label">{MODE_LABEL.double_points}</span>
                    <DoublePointsMini />
                </>
            ) : (
                <>
                    {/* Quiz mirrors its intro's 2x2 block, True or False
                        keeps its side-by-side row, matching its intro. */}
                    <ShapesGroup shapes={shapes} layout={isTrueFalse ? "row" : "grid"} />
                    <span className="akd-mode-indicator__label">{MODE_LABEL[mode] ?? MODE_LABEL.quiz}</span>
                </>
            )}
        </motion.div>
    );
}