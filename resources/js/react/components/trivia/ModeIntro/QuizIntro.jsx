import { motion, useReducedMotion } from "motion/react";
import { ShapeGlyph, QUIZ_SHAPES } from "./modevisuals";
import { TOTAL_MS, TIMES, sec } from "./modeIntroTiming";

// Four boxes side by side, one compact horizontal row, QUIZ underneath as
// a single unified composition. Timing comes from the shared
// modeIntroTiming module, the same budget Double Points and True or False
// use.
const STAGGER_MS = 30;
const SHAPE_OFFSETS = [{ y: -40 }, { y: 40 }, { y: -40 }, { y: 40 }];

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
                                initial={prefersReducedMotion ? { opacity: 0 } : { opacity: 0, y: from.y, scale: 0.6, rotate: 0 }}
                                animate={
                                    prefersReducedMotion
                                        ? { opacity: 1 } : {
                                            y: [from.y, 0, 0, from.y],
                                            opacity: [0, 1, 1, 0],
                                            scale: [0.6, 1, 1, 0.6],
                                            rotate: [0, 30, 30, 0],
                                        }
                                }
                                transition={prefersReducedMotion ? { duration: sec(TOTAL_MS) }
                                        : { duration: sec(duration), delay: sec(delay), times: TIMES, ease: ["easeOut", "linear", "easeIn"] }
                                }
                            >
                                <ShapeGlyph shape={s.shape} size={26} />
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