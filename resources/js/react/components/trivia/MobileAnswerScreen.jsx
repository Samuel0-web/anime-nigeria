import QuestionNumberBadge from "./QuestionNumberBadge";
import PlayerBadge from "./PlayerBadge";
import AnswerOptionCard from "./AnswerOptionCard";
import ModeIndicator from "./ModeIndicator";

export default function MobileAnswerScreen({questionNumber, image, answers, answerStyles,
    indicatorMode, selectedAnswer, onSelect, durationSeconds, player,
}) {
    return (
        <section className="akd-trivia-mobile-answer">
            <div key={questionNumber} className="akd-trivia-mobile-answer__timerbar"
                style={{ "--duration": `${durationSeconds}s` }}
            />

            <div className="akd-trivia-mobile-answer__top akd-trivia-header-row">
                <QuestionNumberBadge number={questionNumber} variant="capsule" />
                <ModeIndicator mode={indicatorMode} />
            </div>

            {image && (
                <div className="akd-trivia-mobile-answer__image">
                    <img src={image} alt="" />
                </div>
            )}

            <div className="akd-trivia-mobile-answer__grid">
                {answers.map((answer, i) => {
                    const style = answerStyles[i];
                    
                    return (
                        <AnswerOptionCard key={answer} label={answer} color={style.color}
                            shape={style.shape} isSelected={selectedAnswer === answer}
                            disabled={selectedAnswer !== null}
                            onClick={() => onSelect(answer)}
                        />
                    );
                })}
            </div>

            <div className="akd-trivia-mobile-answer__player">
                <PlayerBadge avatar={player.avatar} username={player.username} points={player.points} />
            </div>
        </section>
    );
}