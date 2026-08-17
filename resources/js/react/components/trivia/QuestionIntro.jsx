import { motion } from "motion/react";
import QuestionNumberBadge from "./QuestionNumberBadge";

export default function QuestionIntro({ questionNumber, questionText, variant = "desktop" }) {
    if (variant === "mobile") {
        return (
            <section className="akd-trivia-intro akd-trivia-intro--mobile">
                <div className="akd-trivia-intro__badge">
                    <QuestionNumberBadge number={questionNumber} variant="capsule" />
                </div>

                <motion.div
                    key={questionNumber}
                    className="akd-trivia-intro__card"
                    initial={{ opacity: 0, y: 16, scale: 0.96 }}
                    animate={{ opacity: 1, y: 0, scale: 1 }}
                    transition={{ type: "spring", stiffness: 260, damping: 22 }}
                >
                    <p className="akd-trivia-intro__card-text">{questionText}</p>
                </motion.div>
            </section>
        );
    }

    return (
        <section className="akd-trivia-intro">
            <div className="akd-trivia-intro__badge">
                <QuestionNumberBadge number={questionNumber} />
            </div>

            <motion.h2
                key={questionNumber}
                className="akd-trivia-intro__question"
                initial={{ opacity: 0, y: 14, scale: 0.96 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                transition={{ type: "spring", stiffness: 240, damping: 22 }}
            >
                {questionText}
            </motion.h2>
        </section>
    );
}