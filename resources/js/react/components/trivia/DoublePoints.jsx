import { motion } from "motion/react";
import { TOTAL_MS, TIMES, sec } from "./ModeIntro/modeIntroTiming";

// Same shared ENTER/HOLD/EXIT budget as Quiz and True or False now (see
// modeIntroTiming.js). Only the internal choreography here is unique to
// this mode: ink travels in, impacts, the X2 forms, the ring draws. Every
// stage's own delay+duration is chosen to finish well inside the shared
// HOLD window, leaving a short genuine static hold before the shared exit
// fade begins, exactly like the shapes settling in QuizIntro/TrueFalseIntro.
const VISUAL_APPEAR_MS = 130;
const INK_ENTER_DELAY_MS = 50;
const INK_ENTER_MS = 300;
const INK_IMPACT_FRACTION = 220 / 300; // travels for ~220ms of its 300ms, then impacts
const X2_REVEAL_DELAY_MS = INK_ENTER_DELAY_MS + 270;
const X2_REVEAL_MS = 150;
const RING_DELAY_MS = X2_REVEAL_DELAY_MS + X2_REVEAL_MS;
const RING_MS = 380;
const RADIUS = 54;

export default function DoublePoints() {
    return (
        <motion.section
            className="akd-trivia-double"
            initial={{ opacity: 0 }}
            animate={{ opacity: [0, 1, 1, 0] }}
            transition={{ duration: sec(TOTAL_MS), times: TIMES, ease: ["easeOut", "linear", "easeIn"] }}
        >
            <p className="akd-trivia-double__label">Double points</p>

            <motion.div className="akd-trivia-double__visual"
                initial={{ opacity: 0, scale: 0.8 }} animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: sec(VISUAL_APPEAR_MS), ease: "easeOut" }}
            >
                <svg className="akd-trivia-double__ring" viewBox="0 0 128 128">
                    <circle className="akd-trivia-double__ring-track" cx="64" cy="64" r={RADIUS} />

                    <motion.circle className="akd-trivia-double__ring-progress"
                        cx="64" cy="64" r={RADIUS} initial={{ pathLength: 0 }}
                        animate={{ pathLength: 1 }}
                        transition={{ delay: sec(RING_DELAY_MS), duration: sec(RING_MS), ease: "easeOut" }}
                    />
                </svg>

                <div className="akd-trivia-double__inner">
                    <motion.span className="akd-trivia-double__ink"
                        initial={{ x: -90, scaleX: 1.5, scaleY: 0.7, rotate: -18, opacity: 1 }}
                        animate={{
                            x: [-90, 0, 0],
                            scaleX: [1.5, 0.85, 1.3],
                            scaleY: [0.7, 1.05, 0],
                            rotate: [-18, 0, 0],
                            opacity: [1, 1, 0],
                        }}
                        transition={{
                            delay: sec(INK_ENTER_DELAY_MS),
                            duration: sec(INK_ENTER_MS),
                            times: [0, INK_IMPACT_FRACTION, 1],
                            ease: ["easeOut", "easeIn"],
                        }}
                    />

                    <motion.span
                        className="akd-trivia-double__char akd-trivia-double__char--x"
                        initial={{ scale: 0, opacity: 0, rotate: -25 }}
                        animate={{ scale: 1, opacity: 1, rotate: 0 }}
                        transition={{ delay: sec(X2_REVEAL_DELAY_MS), duration: sec(X2_REVEAL_MS), type: "spring", bounce: 0.4 }}
                    >
                        X
                    </motion.span>
                    <motion.span
                        className="akd-trivia-double__char akd-trivia-double__char--two"
                        initial={{ scale: 0, opacity: 0, rotate: 25 }}
                        animate={{ scale: 1, opacity: 1, rotate: 0 }}
                        transition={{ delay: sec(X2_REVEAL_DELAY_MS + 30), duration: sec(X2_REVEAL_MS), type: "spring", bounce: 0.4 }}
                    >
                        2
                    </motion.span>
                </div>
            </motion.div>
        </motion.section>
    );
}