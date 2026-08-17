import { motion } from "motion/react";
import QuestionNumberBadge from "./QuestionNumberBadge";
import PlayerBadge from "./PlayerBadge";

export default function MobileAnswerScreen({
    questionNumber,
    image,
    answers,
    selectedAnswer,
    onSelect,
    durationSeconds,
    player,
}) {
    return (
        <section className="akd-trivia-mobile-answer">
            <div
                key={questionNumber}
                className="akd-trivia-mobile-answer__timerbar"
                style={{ "--duration": `${durationSeconds}s` }}
            />

            <div className="akd-trivia-mobile-answer__top">
                <QuestionNumberBadge number={questionNumber} variant="capsule" />
            </div>

            {image && (
                <div className="akd-trivia-mobile-answer__image">
                    <img src={image} alt="" />
                </div>
            )}

            <div className="akd-trivia-mobile-answer__grid">
                {answers.map((answer) => {
                    const isSelected = selectedAnswer === answer;
                    return (
                        <motion.button
                            key={answer}
                            className={`akd-trivia-mobile-answer__option ${isSelected ? "is-selected" : ""}`}
                            onClick={() => onSelect(answer)}
                            disabled={selectedAnswer !== null}
                            whileTap={{ scale: 0.96 }}
                        >
                            {answer}
                        </motion.button>
                    );
                })}
            </div>

            <div className="akd-trivia-mobile-answer__player">
                <PlayerBadge avatar={player.avatar} username={player.username} points={player.points} />
            </div>
        </section>
    );
}