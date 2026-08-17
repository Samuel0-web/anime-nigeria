import { useState } from "react";
import { motion, AnimatePresence } from "motion/react";

export default function FullResults({ questions, answerHistory, correctCount, incorrectCount, totalPoints }) {
    const [index, setIndex] = useState(0);
    const question = questions[index];
    const record = answerHistory[index];

    const isFirst = index === 0;
    const isLast = index === questions.length - 1;

    return (
        <section className="akd-trivia-full-results">
            <div className="akd-trivia-full-results__summary">
                <div className="akd-trivia-full-results__summary-item">
                    <span className="akd-trivia-full-results__summary-value">{correctCount}</span>
                    <span className="akd-trivia-full-results__summary-label">Correct</span>
                </div>
                <div className="akd-trivia-full-results__summary-item">
                    <span className="akd-trivia-full-results__summary-value">{incorrectCount}</span>
                    <span className="akd-trivia-full-results__summary-label">Incorrect</span>
                </div>
                <div className="akd-trivia-full-results__summary-item">
                    <span className="akd-trivia-full-results__summary-value">{totalPoints.toLocaleString()}</span>
                    <span className="akd-trivia-full-results__summary-label">Points</span>
                </div>
            </div>

            <AnimatePresence mode="wait">
                <motion.div
                    key={index}
                    className="akd-trivia-full-results__review"
                    initial={{ opacity: 0, x: 12 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -12 }}
                    transition={{ duration: 0.25, ease: "easeOut" }}
                >
                    <p className="akd-trivia-full-results__question-number">
                        Question {String(index + 1).padStart(2, "0")}
                    </p>
                    <h3 className="akd-trivia-full-results__question">{question.question}</h3>

                    <div className="akd-trivia-full-results__answers">
                        {question.answers.map((answer) => {
                            const isCorrectAnswer = answer === question.correctAnswer;
                            const isSelected = answer === record?.selectedAnswer;
                            let stateClass = "";
                            if (isCorrectAnswer) stateClass = "is-correct";
                            else if (isSelected) stateClass = "is-incorrect";

                            return (
                                <div key={answer} className={`akd-trivia-full-results__answer ${stateClass}`}>
                                    {answer}
                                </div>
                            );
                        })}
                    </div>
                </motion.div>
            </AnimatePresence>

            <div className="akd-trivia-full-results__nav">
                <button
                    className="akd-trivia-btn akd-trivia-btn--secondary"
                    onClick={() => setIndex((i) => i - 1)}
                    disabled={isFirst}
                >
                    <i className="fa-solid fa-chevron-left" /> Previous
                </button>
                <button
                    className="akd-trivia-btn akd-trivia-btn--secondary"
                    onClick={() => setIndex((i) => i + 1)}
                    disabled={isLast}
                >
                    Next <i className="fa-solid fa-chevron-right" />
                </button>
            </div>
        </section>
    );
}