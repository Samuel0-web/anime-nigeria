import { motion } from "motion/react";
import { DOUBLE_POINTS_DURATION_MS } from "../../data/trivia";

// Sequence, all timed in ms against the existing 3.5s state:
//   0    -> visual (ring+circle) appears
//   150  -> black ink starts traveling in from the left
//   700  -> ink "impacts" (brief overscale) and begins fading
//   750  -> X and 2 pop in at the same spot, overlapping the ink's fade
//           so the fade-out and pop-in read as one continuous transformation
//   1100 -> outer ring starts drawing, using Motion's pathLength
//   2600 -> animation complete, remaining 900ms is a static hold
//   3500 -> state ends, Question 6 begins (governed separately by the
//           absolute-deadline timer in Trivia.jsx, untouched by this file)
const HOLD_MS = 900;
const ANIMATION_MS = DOUBLE_POINTS_DURATION_MS - HOLD_MS;

const VISUAL_APPEAR_MS = 150;
const INK_ENTER_MS = 550;
const INK_IMPACT_MS = 200;
const INK_ENTER_DELAY_MS = VISUAL_APPEAR_MS;
const INK_IMPACT_DELAY_MS = INK_ENTER_DELAY_MS + INK_ENTER_MS;
const X2_REVEAL_DELAY_MS = INK_IMPACT_DELAY_MS + 50;
const X2_REVEAL_MS = 350;
const RING_DELAY_MS = X2_REVEAL_DELAY_MS + X2_REVEAL_MS;
const RING_MS = ANIMATION_MS - RING_DELAY_MS;

const RADIUS = 54;
const sec = (n) => n / 1000;
const inkSplitPoint = INK_ENTER_MS / (INK_ENTER_MS + INK_IMPACT_MS);

export default function DoublePoints() {
    return (
        <motion.section
            className="akd-trivia-double"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.3 }}
        >
            <p className="akd-trivia-double__label">Double points</p>

            <motion.div
                className="akd-trivia-double__visual"
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: sec(VISUAL_APPEAR_MS), ease: "easeOut" }}
            >
                <svg className="akd-trivia-double__ring" viewBox="0 0 128 128">
                    <circle className="akd-trivia-double__ring-track" cx="64" cy="64" r={RADIUS} />
                    <motion.circle
                        className="akd-trivia-double__ring-progress"
                        cx="64" cy="64" r={RADIUS}
                        initial={{ pathLength: 0 }}
                        animate={{ pathLength: 1 }}
                        transition={{ delay: sec(RING_DELAY_MS), duration: sec(RING_MS), ease: "easeOut" }}
                    />
                </svg>

                <div className="akd-trivia-double__inner">
                    {/* The ink: travels in from the left, squashes on impact,
                        then fades exactly as X2 pops in at the same point. */}
                    <motion.span
                        className="akd-trivia-double__ink"
                        initial={{ x: -110, scaleX: 1.5, scaleY: 0.7, rotate: -18, opacity: 1 }}
                        animate={{
                            x: [-110, 0, 0],
                            scaleX: [1.5, 0.85, 1.3],
                            scaleY: [0.7, 1.05, 0],
                            rotate: [-18, 0, 0],
                            opacity: [1, 1, 0],
                        }}
                        transition={{
                            delay: sec(INK_ENTER_DELAY_MS),
                            duration: sec(INK_ENTER_MS + INK_IMPACT_MS),
                            times: [0, inkSplitPoint, 1],
                            ease: ["easeOut", "easeIn"],
                        }}
                    />

                    <motion.span
                        className="akd-trivia-double__char akd-trivia-double__char--x"
                        initial={{ scale: 0, opacity: 0, rotate: -25 }}
                        animate={{ scale: 1, opacity: 1, rotate: 0 }}
                        transition={{
                            delay: sec(X2_REVEAL_DELAY_MS),
                            duration: sec(X2_REVEAL_MS),
                            type: "spring",
                            stiffness: 420,
                            damping: 18,
                        }}
                    >
                        X
                    </motion.span>
                    <motion.span
                        className="akd-trivia-double__char akd-trivia-double__char--two"
                        initial={{ scale: 0, opacity: 0, rotate: 25 }}
                        animate={{ scale: 1, opacity: 1, rotate: 0 }}
                        transition={{
                            delay: sec(X2_REVEAL_DELAY_MS + 60),
                            duration: sec(X2_REVEAL_MS),
                            type: "spring",
                            stiffness: 420,
                            damping: 18,
                        }}
                    >
                        2
                    </motion.span>
                </div>
            </motion.div>
        </motion.section>
    );
}