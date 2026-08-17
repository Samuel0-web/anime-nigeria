import { motion } from "motion/react";

const floatingShapes = [
    { size: 220, top: "-10%", left: "-8%", delay: 0 },
    { size: 160, top: "60%", left: "70%", delay: 0.6 },
    { size: 120, top: "10%", left: "80%", delay: 1.1 },
];

export default function WaitingRoom({ event, countdownLabel }) {
    return (
        <section className="akd-trivia-waiting">
            <div className="akd-trivia-waiting__bg" />

            {floatingShapes.map((shape, i) => (
                <motion.span
                    key={i}
                    className="akd-trivia-waiting__shape"
                    style={{ width: shape.size, height: shape.size, top: shape.top, left: shape.left }}
                    animate={{ y: [0, -14, 0], opacity: [0.5, 0.8, 0.5] }}
                    transition={{ duration: 5 + i, repeat: Infinity, ease: "easeInOut", delay: shape.delay }}
                />
            ))}

            <motion.div
                className="akd-trivia-waiting__content"
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5, ease: "easeOut" }}
            >
                <span className="akd-trivia-badge akd-trivia-badge--live">
                    <span className="akd-trivia-badge__dot" />
                    Live Event
                </span>

                <p className="akd-trivia-waiting__kicker">{event.kicker}</p>
                <h1 className="akd-trivia-waiting__title">{event.title}</h1>

                <div className="akd-trivia-waiting__countdown">{countdownLabel}</div>

                <p className="akd-trivia-waiting__hint">
                    <i className="fa-solid fa-circle-notch fa-spin" /> Get ready...
                </p>

                <p className="akd-trivia-waiting__questions">{event.questionCount} Questions</p>
            </motion.div>
        </section>
    );
}