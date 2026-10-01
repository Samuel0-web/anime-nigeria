import { motion, useReducedMotion } from "motion/react";
import { ShapeGlyph, QUIZ_SHAPES } from "./modevisuals";
import { TOTAL_MS, TIMES, sec } from "./modeIntroTiming";

// A true touching 2x2 grid, zero gap, this is the literal source
// composition ModeIndicator.jsx scales down (see its --grid modifier).
// Cells carry a small settling rotation only during entrance, by the time
// the hold begins every cell is back to rotate:0 so the four colors sit
// flush with no visible seam or diamond-shaped gap in the center.
const STAGGER_MS = 30;
const SHAPE_OFFSETS = [
    { x: -60, y: -60, rotate: -12 },
    { x: 60, y: -60, rotate: 12 },
    { x: -60, y: 60, rotate: 12 },
    { x: 60, y: 60, rotate: -12 },
];

export default function QuizIntro() {
    const prefersReducedMotion = useReducedMotion();

    return (
        <section className="akd-mode-intro akd-mode-intro--quiz">
            <div className="akd-mode-intro__quiz-card">
                <div className="akd-mode-intro__stage">
                    {QUIZ_SHAPES.map((s, i) => {
                        const from = SHAPE_OFFSETS[i];
                        const delay = i * STAGGER_MS;
                        const duration = TOTAL_MS - delay;

                        return (
                            <motion.div key={s.key}
                                className={`akd-mode-intro__shape-card akd-answer-card--${s.color}`}
                                initial={prefersReducedMotion ? { opacity: 0 } : { opacity: 0, x: from.x, y: from.y, scale: 0.7, rotate: from.rotate }}
                                animate={
                                    prefersReducedMotion
                                        ? { opacity: 1 } : {
                                            x: [from.x, 0, 0, from.x],
                                            y: [from.y, 0, 0, from.y],
                                            opacity: [0, 1, 1, 0],
                                            scale: [0.7, 1, 1, 0.7],
                                            rotate: [from.rotate, 0, 0, 0],
                                        }
                                }
                                transition={prefersReducedMotion ? { duration: sec(TOTAL_MS) }
                                        : { duration: sec(duration), delay: sec(delay), times: TIMES, ease: ["easeOut", "linear", "easeIn"] }
                                }
                            >
                                <ShapeGlyph shape={s.shape} size={22} />
                            </motion.div>
                        );
                    })}
                </div>

                <motion.h2 className="akd-mode-intro__quiz-title"
                    initial={prefersReducedMotion ? { opacity: 0 } : { opacity: 0, y: 8 }}
                    animate={prefersReducedMotion ? { opacity: 1 } : { opacity: [0, 1, 1, 0], y: [8, 0, 0, 8] }}
                    transition={prefersReducedMotion ? { duration: sec(TOTAL_MS) }
                            : { duration: sec(TOTAL_MS - 40), delay: sec(40), times: TIMES, ease: ["easeOut", "linear", "easeIn"] }
                    }
                >
                    QUIZ
                </motion.h2>
            </div>
        </section>
    );
}