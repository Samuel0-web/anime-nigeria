import { motion, AnimatePresence } from "motion/react";

export default function JoiningState({ isReady }) {
    return (
        <section className="akd-trivia-joining">
            <div className="akd-trivia-joining__spinner" />
            <AnimatePresence mode="wait">
                <motion.p
                    key={isReady ? "ready" : "joining"}
                    className="akd-trivia-joining__text"
                    initial={{ opacity: 0, y: 6 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -6 }}
                    transition={{ duration: 0.25 }}
                >
                    {isReady ? "You're in!" : "Joining..."}
                </motion.p>
            </AnimatePresence>
        </section>
    );
}