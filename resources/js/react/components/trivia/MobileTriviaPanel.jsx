import { useEffect } from "react";
import { motion, AnimatePresence } from "motion/react";
import { PHASES } from "../../data/trivia";
import WaitingRoom from "./WaitingRoom";
import JoiningState from "./JoiningState";
import QuestionIntro from "./QuestionIntro";
import MobileAnswerScreen from "./MobileAnswerScreen";
import ResultScreen from "./ResultScreen";
import DoublePoints from "./DoublePoints";
import FinalResult from "./FinalResult";
import FullResults from "./FullResults";

const CLOSABLE_PHASES = [PHASES.FINAL_RESULT, PHASES.FULL_RESULTS];

export default function MobileTriviaPanel({
    isOpen,
    phase,
    event,
    countdownLabel,
    joiningReady,
    question,
    questionNumber,
    selectedAnswer,
    onSelectAnswer,
    player,
    lastResult,
    showConfetti,
    resultsProps,
    onViewFullResults,
    onBackToFinal,
    onClose,
}) {
    useEffect(() => {
        if (!isOpen) return;
        document.body.classList.add("akd-no-scroll");
        return () => document.body.classList.remove("akd-no-scroll");
    }, [isOpen]);

    const canClose = CLOSABLE_PHASES.includes(phase);
    const showBack = phase === PHASES.FULL_RESULTS;

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    className="akd-trivia-mobile-panel"
                    initial={{ y: "100%" }}
                    animate={{ y: 0 }}
                    exit={{ y: "100%" }}
                    transition={{ type: "spring", stiffness: 300, damping: 32 }}
                >
                    {canClose && (
                        <div className="akd-trivia-mobile-panel__header">
                            {showBack ? (
                                <button className="akd-trivia-mobile-panel__icon-btn" onClick={onBackToFinal}>
                                    <i className="fa-solid fa-chevron-left" />
                                </button>
                            ) : (
                                <span />
                            )}
                            <button className="akd-trivia-mobile-panel__icon-btn" onClick={onClose}>
                                <i className="fa-solid fa-xmark" />
                            </button>
                        </div>
                    )}

                    <div className="akd-trivia-mobile-panel__content">
                        {phase === PHASES.WAITING && (
                            <WaitingRoom event={event} countdownLabel={countdownLabel} />
                        )}

                        {phase === PHASES.JOINING && (
                            <JoiningState isReady={joiningReady} />
                        )}

                        {phase === PHASES.QUESTION_INTRO && (
                            <QuestionIntro
                                variant="mobile"
                                questionNumber={questionNumber}
                                questionText={question.question}
                            />
                        )}

                        {phase === PHASES.ANSWERING && (
                            <MobileAnswerScreen
                                questionNumber={questionNumber}
                                image={question.image}
                                answers={question.answers}
                                selectedAnswer={selectedAnswer}
                                onSelect={onSelectAnswer}
                                durationSeconds={question.answerDuration}
                                player={player}
                            />
                        )}

                        {phase === PHASES.RESULT && lastResult && (
                            <ResultScreen
                                isCorrect={lastResult.isCorrect}
                                isTimeout={lastResult.isTimeout}
                                pointsEarned={lastResult.pointsEarned}
                                positionLabel={lastResult.positionLabel}
                                gapLabel={lastResult.gapLabel}
                                encouragement={lastResult.encouragement}
                            />
                        )}

                        {phase === PHASES.DOUBLE_POINTS && <DoublePoints />}

                        {phase === PHASES.FINAL_RESULT && (
                            <FinalResult
                                avatar={player.avatar}
                                username={player.username}
                                rankLabel={resultsProps.rankLabel}
                                totalPoints={player.points}
                                showConfetti={showConfetti}
                                onViewFullResults={onViewFullResults}
                            />
                        )}

                        {phase === PHASES.FULL_RESULTS && (
                            <FullResults
                                questions={resultsProps.questions}
                                answerHistory={resultsProps.answerHistory}
                                correctCount={resultsProps.correctCount}
                                incorrectCount={resultsProps.incorrectCount}
                                totalPoints={player.points}
                            />
                        )}
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}