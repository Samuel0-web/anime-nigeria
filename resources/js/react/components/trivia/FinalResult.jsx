import { motion } from "motion/react";
import Confetti from "./Confetti";

const container = {
    hidden: {},
    show: { transition: { staggerChildren: 0.12, delayChildren: 0.1 } },
};

const item = {
    hidden: { opacity: 0, y: 14 },
    show: { opacity: 1, y: 0, transition: { duration: 0.4, ease: [0.22, 1, 0.36, 1] } },
};

export default function FinalResult({ avatar, username, rankLabel, totalPoints, onViewFullResults, showConfetti }) {
    return (
        <motion.section className="akd-trivia-final" variants={container} initial="hidden" animate="show">
            {showConfetti && <Confetti />}
            <motion.img variants={item} src={avatar} alt="" className="akd-trivia-final__avatar" />
            <motion.h2 variants={item} className="akd-trivia-final__username">{username}</motion.h2>
            <motion.p variants={item} className="akd-trivia-final__rank">{rankLabel}</motion.p>
            <motion.p variants={item} className="akd-trivia-final__points">{totalPoints.toLocaleString()} points</motion.p>

            <motion.button
                variants={item}
                className="akd-trivia-btn akd-trivia-btn--primary"
                onClick={onViewFullResults}
                whileTap={{ scale: 0.97 }}
            >
                View full results
            </motion.button>
        </motion.section>
    );
}