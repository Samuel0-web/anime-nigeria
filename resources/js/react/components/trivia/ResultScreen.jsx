import { useEffect, useState } from "react";
import { motion, animate } from "motion/react";

function AnimatedPoints({ value }) {
    const [display, setDisplay] = useState(0);

    useEffect(() => {
        const controls = animate(0, value, {
            duration: 0.6,
            ease: [0.16, 1, 0.3, 1],
            onUpdate: (latest) => setDisplay(Math.round(latest)),
        });
        return () => controls.stop();
    }, [value]);

    return <>+{display} points</>;
}

export default function ResultScreen({ isCorrect, isTimeout, pointsEarned, positionLabel, gapLabel, encouragement }) {
    return (
        <section className={`akd-trivia-result ${isCorrect ? "akd-trivia-result--correct" : "akd-trivia-result--incorrect"}`}>
            <motion.div
                className="akd-trivia-result__icon"
                initial={{ scale: 0.4, opacity: 0 }}
                animate={
                    isCorrect
                        ? { scale: [0.4, 1.15, 1], opacity: 1 }
                        : { scale: 1, opacity: 1, x: [0, -8, 8, -6, 6, 0] }
                }
                transition={{ duration: isCorrect ? 0.45 : 0.4, ease: "easeOut" }}
            >
                <i className={`fa-solid ${isCorrect ? "fa-check" : "fa-xmark"}`} />
            </motion.div>

            <motion.h2
                className="akd-trivia-result__headline"
                initial={{ opacity: 0, y: 8 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.15, duration: 0.3 }}
            >
                {isCorrect ? "Correct!" : isTimeout ? "Time's up" : "Not quite"}
            </motion.h2>

            {isCorrect ? (
                <motion.p
                    className="akd-trivia-result__points"
                    initial={{ opacity: 0, scale: 0.8 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ delay: 0.2, duration: 0.3 }}
                >
                    <AnimatedPoints value={pointsEarned} />
                </motion.p>
            ) : (
                <motion.p
                    className="akd-trivia-result__encouragement"
                    initial={{ opacity: 0, y: 6 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.2, duration: 0.3 }}
                >
                    {encouragement}
                </motion.p>
            )}

            <div className="akd-trivia-result__rank">
                <p className="akd-trivia-result__position">{positionLabel}</p>
                {gapLabel && <p className="akd-trivia-result__gap">{gapLabel}</p>}
            </div>
        </section>
    );
}