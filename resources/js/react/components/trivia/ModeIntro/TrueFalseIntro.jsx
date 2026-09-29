import { motion, useReducedMotion } from "motion/react";
import { ShapeGlyph, TRUE_FALSE_SHAPES } from "./modevisuals";
import { TOTAL_MS, TIMES, sec } from "./modeIntroTiming";

const STAGGER_MS = 40;
const OFFSETS = [{ x: -70, y: 0 }, { x: 70, y: 0 }];

export default function TrueFalseIntro() {
    const prefersReducedMotion = useReducedMotion();

    return (
        <section className="akd-mode-intro akd-mode-intro--true-false">
            <div className="akd-mode-intro__tf-composition">
                <div className="akd-mode-intro__stage akd-mode-intro__stage--two-card">
                    {TRUE_FALSE_SHAPES.map((c, i) => {
                        const from = OFFSETS[i];
                        const delay = i * STAGGER_MS;
                        const duration = TOTAL_MS - delay;

                        return (
                            <motion.div key={c.key}
                                className={`akd-mode-intro__tf-card akd-answer-card--${c.color}`}
                                initial={prefersReducedMotion ? { opacity: 0 } : { opacity: 0, x: from.x, scale: 0.7, rotate: 0 }}
                                animate={prefersReducedMotion ? { opacity: 1 }
                                        : { x: [from.x, 0, 0, from.x], opacity: [0, 1, 1, 0], scale: [0.7, 1, 1, 0.7], rotate: [0, 30, 30, 0] }
                                }
                                transition={prefersReducedMotion ? { duration: sec(TOTAL_MS) }
                                        : { duration: sec(duration), delay: sec(delay), times: TIMES, ease: ["easeOut", "linear", "easeIn"] }
                                }
                            >
                                <ShapeGlyph shape={c.shape} size={30} />
                            </motion.div>
                        );
                    })}
                </div>

                <motion.h2 className="akd-mode-intro__tf-title"
                    initial={prefersReducedMotion ? { opacity: 0 } : { opacity: 0, y: 8 }}
                    animate={prefersReducedMotion ? { opacity: 1 } : { opacity: [0, 1, 1, 0], y: [8, 0, 0, 8] }}
                    transition={prefersReducedMotion ? { duration: sec(TOTAL_MS) }
                            : { duration: sec(TOTAL_MS - 40), delay: sec(40), times: TIMES, ease: ["easeOut", "linear", "easeIn"] }
                    }
                >
                    TRUE OR FALSE
                </motion.h2>
            </div>
        </section>
    );
}