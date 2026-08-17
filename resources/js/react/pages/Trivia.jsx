import { useEffect, useMemo, useRef, useState } from "react";
import {
    PHASES,
    triviaEvent,
    currentPlayer,
    PLAYER_ID,
    questions,
    eventTimeline,
    createInitialLeaderboard,
    TICK_MS,
    EVENT_START_OFFSET_MS,
    LIVE_SOON_THRESHOLD_MS,
    ENTRY_GRACE_MS,
    JOINING_READY_THRESHOLD_MS,
    RESULT_DISPLAY_MS,
} from "../data/trivia";
import {
    EVENT_STATUS,
    SEGMENT_TYPE,
    QUESTION_SUB_PHASE,
    getEventStatus,
    getSegmentAtElapsed,
    getQuestionSegment,
    getQuestionSubPhase,
    resolveEntry,
} from "../utils/trivia/eventTimeline";
import {
    formatCountdown,
    resolveQuestionPoints,
    applyRoundScores,
    getRankedPlayers,
    getRankMessage,
    getFinalRankLabel,
    isPodiumFinish,
    getRandomEncouragement,
} from "../utils/trivia/triviaUtils";
import useIsMobile from "../hooks/trivia/useIsMobile";

import EventBanner from "../components/trivia/EventBanner";
import WaitingRoom from "../components/trivia/WaitingRoom";
import JoiningState from "../components/trivia/JoiningState";
import QuestionIntro from "../components/trivia/QuestionIntro";
import AnswerScreen from "../components/trivia/AnswerScreen";
import ResultScreen from "../components/trivia/ResultScreen";
import DoublePoints from "../components/trivia/DoublePoints";
import FinalResult from "../components/trivia/FinalResult";
import FullResults from "../components/trivia/FullResults";
import MobileTriviaPanel from "../components/trivia/MobileTriviaPanel";
import MobileResultsSummaryCard from "../components/trivia/MobileResultsSummaryCard";

export default function Trivia() {
    const isMobile = useIsMobile();

    const [eventStatus, setEventStatus] = useState(EVENT_STATUS.UPCOMING);
    const [countdownSeconds, setCountdownSeconds] = useState(EVENT_START_OFFSET_MS / 1000);

    const [hasJoined, setHasJoined] = useState(false);
    const [isMobilePanelOpen, setIsMobilePanelOpen] = useState(false);
    const [localPhase, setLocalPhaseState] = useState(PHASES.WAITING);
    const [joiningReady, setJoiningReady] = useState(false);

    const [reminderSet, setReminderSet] = useState(false);
    const [currentQuestionIndex, setCurrentQuestionIndexState] = useState(0);
    const [selectedAnswer, setSelectedAnswer] = useState(null);
    const [leaderboard, setLeaderboardState] = useState(createInitialLeaderboard);
    const [answerHistory, setAnswerHistory] = useState([]);
    const [lastResult, setLastResult] = useState(null);

    // Fixed once per mount, a page refresh is what resets the mock event,
    // tab visibility changes must never touch this.
    const eventStartTimeRef = useRef(Date.now() + EVENT_START_OFFSET_MS);

    const localPhaseRef = useRef(localPhase);
    const currentQuestionIndexRef = useRef(currentQuestionIndex);
    const leaderboardRef = useRef(leaderboard);
    const hasJoinedRef = useRef(false);
    const phaseEndsAtRef = useRef(null);
    const hasTransitionedRef = useRef(false);
    const hasAnsweredRef = useRef(false);

    function setLocalPhase(next) {
        localPhaseRef.current = next;
        setLocalPhaseState(next);
    }

    function setCurrentQuestionIndex(next) {
        currentQuestionIndexRef.current = next;
        setCurrentQuestionIndexState(next);
    }

    function setLeaderboard(next) {
        leaderboardRef.current = next;
        setLeaderboardState(next);
    }

    function getCurrentQuestion() {
        return questions[currentQuestionIndexRef.current];
    }

    // ---- Entry logic, shared by the initial Join click, catching up after
    // a personal Result screen, and waking up from the Joining state. ----
    function enterSegment(segment, elapsedMs) {
        setCurrentQuestionIndex(segment.questionIndex);
        hasTransitionedRef.current = false;

        if (segment.type === SEGMENT_TYPE.DOUBLE_POINTS) {
            setLocalPhase(PHASES.DOUBLE_POINTS);
            phaseEndsAtRef.current = eventStartTimeRef.current + segment.endMs;
            return;
        }

        const subPhase = getQuestionSubPhase(segment, elapsedMs);
        if (subPhase.phase === QUESTION_SUB_PHASE.INTRO) {
            setSelectedAnswer(null);
            setLocalPhase(PHASES.QUESTION_INTRO);
            phaseEndsAtRef.current = eventStartTimeRef.current + segment.introEndMs;
        } else {
            hasAnsweredRef.current = false;
            setSelectedAnswer(null);
            setLocalPhase(PHASES.ANSWERING);
            phaseEndsAtRef.current = eventStartTimeRef.current + segment.endMs;
        }
    }

    function attemptEntry(elapsedMs) {
        const now = Date.now();
        const status = getEventStatus(now, eventStartTimeRef.current, LIVE_SOON_THRESHOLD_MS, eventTimeline.totalMs);

        if (status === EVENT_STATUS.ENDED) {
            hasTransitionedRef.current = false;
            phaseEndsAtRef.current = null;
            setLocalPhase(PHASES.FINAL_RESULT);
            return;
        }

        const segment = getSegmentAtElapsed(eventTimeline, elapsedMs);

        if (!segment) {
            // Boundary case: elapsed time has run past the final segment in
            // the same tick that status flips to ENDED. Treat it as ended
            // instead of computing a deadline from a null boundary.
            hasTransitionedRef.current = false;
            phaseEndsAtRef.current = null;
            setLocalPhase(PHASES.FINAL_RESULT);
            return;
        }

        const decision = resolveEntry(segment, elapsedMs, ENTRY_GRACE_MS);

        if (decision.canEnter) {
            enterSegment(segment, elapsedMs);
        } else {
            setJoiningReady(false);
            setLocalPhase(PHASES.JOINING);
            phaseEndsAtRef.current = eventStartTimeRef.current + decision.nextBoundaryMs;
            hasTransitionedRef.current = false;
        }
    }

    function handleJoin() {
        hasJoinedRef.current = true;
        setHasJoined(true);
        if (isMobile) setIsMobilePanelOpen(true);
        hasTransitionedRef.current = false;

        const now = Date.now();
        const eventStartTime = eventStartTimeRef.current;

        if (now < eventStartTime) {
            setLocalPhase(PHASES.WAITING);
            phaseEndsAtRef.current = eventStartTime;
        } else {
            attemptEntry(now - eventStartTime);
        }
    }

    function handleAnswerLocked(answer) {
        if (hasAnsweredRef.current) return;
        hasAnsweredRef.current = true;

        const question = getCurrentQuestion();
        const isTimeout = answer === null;
        const isCorrect = !isTimeout && answer === question.correctAnswer;
        const remainingMs = isTimeout ? 0 : Math.max(0, (phaseEndsAtRef.current ?? 0) - Date.now());
        const pointsEarned = resolveQuestionPoints({
            isCorrect,
            remainingMs,
            totalMs: question.answerDuration * 1000,
            isDoublePoints: !!question.doublePoints,
        });

        const newLeaderboard = applyRoundScores(leaderboardRef.current, currentQuestionIndexRef.current, pointsEarned);
        const ranked = getRankedPlayers(newLeaderboard);
        const rankInfo = getRankMessage(ranked, PLAYER_ID);

        setLeaderboard(newLeaderboard);
        setAnswerHistory((prev) => [
            ...prev,
            { questionId: question.id, selectedAnswer: answer, isCorrect, isTimeout, pointsEarned },
        ]);
        setSelectedAnswer(answer);
        setLastResult({
            isCorrect,
            isTimeout,
            pointsEarned,
            positionLabel: rankInfo.positionLabel,
            gapLabel: rankInfo.gapLabel,
            encouragement: isCorrect ? null : getRandomEncouragement(),
        });

        hasTransitionedRef.current = false;
        setLocalPhase(PHASES.RESULT);
        phaseEndsAtRef.current = Date.now() + RESULT_DISPLAY_MS;
    }

    function handleClosePanel() {
        // Purely a visibility toggle, only reachable from Final/Full Results,
        // the global event has already finished by then regardless.
        setIsMobilePanelOpen(false);
    }

    function handleReopenPanel() {
        setIsMobilePanelOpen(true);
    }

    function handleBackToFinal() {
        setLocalPhase(PHASES.FINAL_RESULT);
    }

    // Single interval for the component's whole lifetime. Everything mutable
    // it needs lives in a ref, so it never resubscribes and never reads a
    // stale closure. The same tick also runs immediately on visibilitychange
    // so returning to a hidden tab reconciles state at once rather than
    // waiting for the next scheduled tick.
    useEffect(() => {
        function beginAnsweringFromIntro() {
            const segment = getQuestionSegment(eventTimeline, currentQuestionIndexRef.current);
            hasAnsweredRef.current = false;
            setSelectedAnswer(null);
            setLocalPhase(PHASES.ANSWERING);
            phaseEndsAtRef.current = eventStartTimeRef.current + segment.endMs;
            hasTransitionedRef.current = false;
        }

        function advanceFromDoublePoints() {
            const segment = getQuestionSegment(eventTimeline, currentQuestionIndexRef.current);
            enterSegment(segment, segment.startMs);
        }

        function tick() {
            const now = Date.now();
            const eventStartTime = eventStartTimeRef.current;

            const status = getEventStatus(now, eventStartTime, LIVE_SOON_THRESHOLD_MS, eventTimeline.totalMs);
            setEventStatus(status);

            if (status === EVENT_STATUS.UPCOMING || status === EVENT_STATUS.LIVE_SOON) {
                setCountdownSeconds(Math.max(0, (eventStartTime - now) / 1000));
            }

            if (!hasJoinedRef.current) return;

            if (localPhaseRef.current === PHASES.JOINING) {
                const remaining = (phaseEndsAtRef.current ?? 0) - now;
                setJoiningReady(remaining <= JOINING_READY_THRESHOLD_MS);
            }

            if (!phaseEndsAtRef.current || hasTransitionedRef.current) return;
            if (now < phaseEndsAtRef.current) return;

            hasTransitionedRef.current = true;

            switch (localPhaseRef.current) {
                case PHASES.WAITING:
                    attemptEntry(0);
                    break;
                case PHASES.JOINING:
                    attemptEntry(now - eventStartTime);
                    break;
                case PHASES.QUESTION_INTRO:
                    beginAnsweringFromIntro();
                    break;
                case PHASES.ANSWERING:
                    handleAnswerLocked(null);
                    break;
                case PHASES.RESULT:
                    attemptEntry(now - eventStartTime);
                    break;
                case PHASES.DOUBLE_POINTS:
                    advanceFromDoublePoints();
                    break;
                default:
                    break;
            }
        }

        tick();
        const intervalId = setInterval(tick, TICK_MS);
        document.addEventListener("visibilitychange", tick);

        return () => {
            clearInterval(intervalId);
            document.removeEventListener("visibilitychange", tick);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // ---- Derived values ----
    const question = questions[currentQuestionIndex];
    const countdownLabel = useMemo(() => formatCountdown(countdownSeconds), [countdownSeconds]);
    const rankedLeaderboard = useMemo(() => getRankedPlayers(leaderboard), [leaderboard]);
    const playerRankEntry = rankedLeaderboard.find((p) => p.id === PLAYER_ID);
    const correctCount = answerHistory.filter((a) => a.isCorrect).length;
    const incorrectCount = answerHistory.length - correctCount;
    const rankLabel = getFinalRankLabel(playerRankEntry?.rank ?? rankedLeaderboard.length);
    const showConfetti = isPodiumFinish(playerRankEntry?.rank ?? Infinity);

    const livePlayer = {
        avatar: currentPlayer.avatar,
        username: currentPlayer.username,
        points: playerRankEntry?.points ?? 0,
    };

    const showMobileSummary =
        isMobile &&
        !isMobilePanelOpen &&
        (localPhase === PHASES.FINAL_RESULT || localPhase === PHASES.FULL_RESULTS);

    return (
        <main className="akd-content">
            <div className="akd-trivia">
                {!hasJoined && (
                    <EventBanner
                        event={triviaEvent}
                        eventStatus={eventStatus}
                        countdownLabel={countdownLabel}
                        reminderSet={reminderSet}
                        onSetReminder={() => setReminderSet(true)}
                        onJoin={handleJoin}
                    />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.WAITING && (
                    <WaitingRoom event={triviaEvent} countdownLabel={countdownLabel} />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.JOINING && (
                    <JoiningState isReady={joiningReady} />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.QUESTION_INTRO && (
                    <QuestionIntro
                        questionNumber={currentQuestionIndex + 1}
                        questionText={question.question}
                    />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.ANSWERING && (
                    <AnswerScreen
                        questionNumber={currentQuestionIndex + 1}
                        image={question.image}
                        answers={question.answers}
                        selectedAnswer={selectedAnswer}
                        onSelect={handleAnswerLocked}
                        durationSeconds={question.answerDuration}
                        player={livePlayer}
                    />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.RESULT && lastResult && (
                    <ResultScreen
                        isCorrect={lastResult.isCorrect}
                        isTimeout={lastResult.isTimeout}
                        pointsEarned={lastResult.pointsEarned}
                        positionLabel={lastResult.positionLabel}
                        gapLabel={lastResult.gapLabel}
                        encouragement={lastResult.encouragement}
                    />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.DOUBLE_POINTS && <DoublePoints />}

                {!isMobile && hasJoined && localPhase === PHASES.FINAL_RESULT && (
                    <FinalResult
                        avatar={livePlayer.avatar}
                        username={livePlayer.username}
                        rankLabel={rankLabel}
                        totalPoints={livePlayer.points}
                        showConfetti={showConfetti}
                        onViewFullResults={() => setLocalPhase(PHASES.FULL_RESULTS)}
                    />
                )}

                {!isMobile && hasJoined && localPhase === PHASES.FULL_RESULTS && (
                    <FullResults
                        questions={questions}
                        answerHistory={answerHistory}
                        correctCount={correctCount}
                        incorrectCount={incorrectCount}
                        totalPoints={livePlayer.points}
                    />
                )}

                {showMobileSummary && (
                    <MobileResultsSummaryCard
                        avatar={livePlayer.avatar}
                        username={livePlayer.username}
                        rankLabel={rankLabel}
                        totalPoints={livePlayer.points}
                        onReopen={handleReopenPanel}
                    />
                )}
            </div>

            {isMobile && hasJoined && (
                <MobileTriviaPanel
                    isOpen={isMobilePanelOpen}
                    phase={localPhase}
                    event={triviaEvent}
                    countdownLabel={countdownLabel}
                    joiningReady={joiningReady}
                    question={question}
                    questionNumber={currentQuestionIndex + 1}
                    selectedAnswer={selectedAnswer}
                    onSelectAnswer={handleAnswerLocked}
                    player={livePlayer}
                    lastResult={lastResult}
                    showConfetti={showConfetti}
                    resultsProps={{ rankLabel, questions, answerHistory, correctCount, incorrectCount }}
                    onViewFullResults={() => setLocalPhase(PHASES.FULL_RESULTS)}
                    onBackToFinal={handleBackToFinal}
                    onClose={handleClosePanel}
                />
            )}
        </main>
    );
}