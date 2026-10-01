import { useEffect, useMemo, useRef, useState } from "react";

import { PHASES, triviaEvent, currentPlayer, PLAYER_ID, questions, eventTimeline,
    createInitialLeaderboard, TICK_MS, EVENT_START_OFFSET_MS, LIVE_SOON_THRESHOLD_MS,
    ENTRY_GRACE_MS, JOINING_READY_THRESHOLD_MS, RESULT_DISPLAY_MS, MODE_INTRO_DURATION_MS,
} from "../data/trivia";

import { EVENT_STATUS, SEGMENT_TYPE, QUESTION_SUB_PHASE, getEventStatus,
    getSegmentAtElapsed, getQuestionSegment, getModeIntroSegment, getQuestionSubPhase, resolveEntry,
} from "../utils/trivia/eventTimeline";

import { formatCountdown, resolveQuestionPoints, applyRoundScores,
    getRankedPlayers, getRankMessage, getFinalRankLabel, isPodiumFinish,
    getRandomEncouragement,
} from "../utils/trivia/triviaUtils";

import { getAnswerStyles } from "../utils/trivia/answerColors";
import useIsMobile from "../hooks/trivia/useIsMobile";
import EventBanner from "../components/trivia/EventBanner";
import TriviaPlaySurface from "../components/trivia/TriviaPlaySurface";
import MobileResultsSummaryCard from "../components/trivia/MobileResultsSummaryCard";

export default function Trivia() {
    const isMobile = useIsMobile();
    const [eventStatus, setEventStatus] = useState(EVENT_STATUS.UPCOMING);
    const [countdownSeconds, setCountdownSeconds] = useState(EVENT_START_OFFSET_MS / 1000);
    const [hasJoined, setHasJoined] = useState(false);
    const [isSurfaceOpen, setIsSurfaceOpen] = useState(false);
    const [localPhase, setLocalPhaseState] = useState(PHASES.WAITING);
    const [joiningReady, setJoiningReady] = useState(false);
    const [activeIntroMode, setActiveIntroMode] = useState(null);
    const [answerStyles, setAnswerStyles] = useState([]);
    const [reminderSet, setReminderSet] = useState(false);
    const [currentQuestionIndex, setCurrentQuestionIndexState] = useState(0);
    const [selectedAnswer, setSelectedAnswer] = useState(null);
    const [leaderboard, setLeaderboardState] = useState(createInitialLeaderboard);
    const [answerHistory, setAnswerHistory] = useState([]);
    const [lastResult, setLastResult] = useState(null);
    // Captured once, when FINAL_RESULT is entered, rather than derived
    // reactively every render, per the "trigger once per state entry" rule.
    const [finalConfetti, setFinalConfetti] = useState(false);

    // Fixed once per mount, a page refresh is what resets the mock event,
    // tab visibility changes must never touch this.
    const eventStartTimeRef = useRef(Date.now() + EVENT_START_OFFSET_MS);
    const localPhaseRef = useRef(localPhase);
    const currentQuestionIndexRef = useRef(currentQuestionIndex);
    const leaderboardRef = useRef(leaderboard);
    const hasJoinedRef = useRef(false);
    // Becomes true the moment the player ever successfully enters a live
    // segment. Once true, later transitions must never re-run the late-join
    // grace check, that check is only for someone who hasn't joined yet.
    const hasEnteredSessionRef = useRef(false);
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
    // a personal Result screen, and waking up from Joining/Mode Intro. ----
    function enterSegment(segment, elapsedMs) {
        hasTransitionedRef.current = false;
        hasEnteredSessionRef.current = true;

        if (segment.type === SEGMENT_TYPE.MODE_INTRO) {
            setCurrentQuestionIndex(segment.questionIndex);
            setActiveIntroMode(segment.introMode);
            setLocalPhase(PHASES.MODE_INTRO);
            // Remaining time within this segment's real startMs..endMs
            // window, same formula as Question Intro/Answering below. A
            // genuine first/late join (attemptEntry passes the real global
            // elapsedMs) correctly gets only what's left of the broadcast's
            // current Mode Intro, punishing late arrival as intended. The
            // post-join chained case (advanceToNextQuestion passes
            // elapsedMs = segment.startMs against the real next segment)
            // resolves to the full nominal duration, since entering exactly
            // at a segment's start always has its whole window ahead of it.
            // One formula, two call sites, no separate "give it fresh" path.
            phaseEndsAtRef.current = Date.now() + (segment.endMs - elapsedMs);
            return;
        }

        setCurrentQuestionIndex(segment.questionIndex);
        setAnswerStyles(getAnswerStyles(questions[segment.questionIndex]));
        const subPhase = getQuestionSubPhase(segment, elapsedMs);

        // (segment.introEndMs - elapsedMs) / (segment.endMs - elapsedMs) is
        // the genuinely remaining nominal time from wherever elapsedMs
        // says we are. For a real-time join this is numerically identical
        // to the old absolute-timestamp formula (elapsedMs was measured
        // against the same clock). For the chained post-Mode-Intro case
        // (advanceFromModeIntro passes elapsedMs = segment.startMs), it
        // correctly resolves to the full nominal duration rather than a
        // stale absolute deadline that may already be in the past.
        if (subPhase.phase === QUESTION_SUB_PHASE.INTRO) {
            setSelectedAnswer(null);
            setLocalPhase(PHASES.QUESTION_INTRO);
            phaseEndsAtRef.current = Date.now() + (segment.introEndMs - elapsedMs);
        } else {
            hasAnsweredRef.current = false;
            setSelectedAnswer(null);
            setLocalPhase(PHASES.ANSWERING);
            phaseEndsAtRef.current = Date.now() + (segment.endMs - elapsedMs);
        }
    }

    function enterFinalResult() {
        hasTransitionedRef.current = false;
        phaseEndsAtRef.current = null;
        const ranked = getRankedPlayers(leaderboardRef.current);
        const entry = ranked.find((p) => p.id === PLAYER_ID);
        setFinalConfetti(isPodiumFinish(entry?.rank ?? Infinity));
        setLocalPhase(PHASES.FINAL_RESULT);
    }

    function attemptEntry(elapsedMs) {
        const now = Date.now();
        const status = getEventStatus(now, eventStartTimeRef.current, LIVE_SOON_THRESHOLD_MS, eventTimeline.totalMs);

        if (status === EVENT_STATUS.ENDED) {
            enterFinalResult();
            return;
        }

        const segment = getSegmentAtElapsed(eventTimeline, elapsedMs);

        if (!segment) {
            enterFinalResult();
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

    // Used only when the player is already an active participant (after
    // their own personal Result screen ends). This is the "post-join local
    // lifecycle" half of the two-track model: it deliberately does not ask
    // "where is the real broadcast clock right now," RESULT_DISPLAY_MS
    // (5000ms) is longer than MODE_INTRO_DURATION_MS (4000ms), so that
    // question would always land past the next Mode Intro's end. Instead
    // it looks up the real next Mode Intro segment and enters it "at its
    // start," which resolves to a full nominal duration via the same
    // formula enterSegment always uses. The global clock's only remaining
    // job is establishing where a player first joins (attemptEntry); once
    // joined, progression is chained locally from here on.
    function advanceToNextQuestion() {
        const nextIndex = currentQuestionIndexRef.current + 1;

        if (nextIndex >= questions.length) {
            enterFinalResult();
            return;
        }

        const segment = getModeIntroSegment(eventTimeline, nextIndex);
        enterSegment(segment, segment.startMs);
    }

    function handleJoin() {
        hasJoinedRef.current = true;
        setHasJoined(true);
        setIsSurfaceOpen(true);
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
        const pointsEarned = resolveQuestionPoints({ isCorrect, remainingMs,
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

        setLastResult({ isCorrect, isTimeout, pointsEarned,
            positionLabel: rankInfo.positionLabel,
            gapLabel: rankInfo.gapLabel,
            encouragement: isCorrect ? null : getRandomEncouragement(),
        });

        hasTransitionedRef.current = false;
        setLocalPhase(PHASES.RESULT);
        phaseEndsAtRef.current = Date.now() + RESULT_DISPLAY_MS;
    }

    function handleCloseSurface() {
        // Purely a visibility toggle, only reachable from Final/Full
        // Results, so there is no live timer running underneath to protect.
        setIsSurfaceOpen(false);
    }

    function handleReopenSurface() {
        setIsSurfaceOpen(true);
    }

    function handleBackToFinal() {
        setLocalPhase(PHASES.FINAL_RESULT);
    }

    // Single interval for the component's whole lifetime, everything
    // mutable it needs lives in a ref so it never resubscribes or reads a
    // stale closure. The same tick runs immediately on visibilitychange so
    // returning to a hidden tab reconciles state at once.
    useEffect(() => {
        function beginAnsweringFromIntro() {
            hasAnsweredRef.current = false;
            setSelectedAnswer(null);
            setLocalPhase(PHASES.ANSWERING);
            // Fresh full answerDuration from now, chained off whenever
            // Question Intro actually ended, not a stale absolute offset.
            phaseEndsAtRef.current = Date.now() + getCurrentQuestion().answerDuration * 1000;
            hasTransitionedRef.current = false;
        }

        function advanceFromModeIntro() {
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
                case PHASES.MODE_INTRO:
                    advanceFromModeIntro();
                    break;
                case PHASES.QUESTION_INTRO:
                    beginAnsweringFromIntro();
                    break;
                case PHASES.ANSWERING:
                    handleAnswerLocked(null);
                    break;
                case PHASES.RESULT:
                    advanceToNextQuestion();
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
    // The mode indicator shows "Double Points" for a doublePoints-flagged
    // question even though its underlying mode is still "quiz", the player
    // just saw the Double Points intro, the indicator should agree with it.
    const indicatorMode = question.doublePoints ? "double_points" : question.mode;
    const countdownLabel = useMemo(() => formatCountdown(countdownSeconds), [countdownSeconds]);
    const rankedLeaderboard = useMemo(() => getRankedPlayers(leaderboard), [leaderboard]);
    const playerRankEntry = rankedLeaderboard.find((p) => p.id === PLAYER_ID);
    const correctCount = answerHistory.filter((a) => a.isCorrect).length;
    const incorrectCount = answerHistory.length - correctCount;
    const rankLabel = getFinalRankLabel(playerRankEntry?.rank ?? rankedLeaderboard.length);

    const livePlayer = {
        avatar: currentPlayer.avatar,
        username: currentPlayer.username,
        points: playerRankEntry?.points ?? 0,
    };

    const showSummaryCard = hasJoined && !isSurfaceOpen &&
        (localPhase === PHASES.FINAL_RESULT || localPhase === PHASES.FULL_RESULTS);

    return (
        <main className="akd-content">
            <div className="akd-trivia">
                {!hasJoined && (
                    <EventBanner event={triviaEvent} eventStatus={eventStatus}
                        countdownLabel={countdownLabel} reminderSet={reminderSet}
                        onSetReminder={() => setReminderSet(true)} onJoin={handleJoin}
                    />
                )}

                {showSummaryCard && (
                    <MobileResultsSummaryCard avatar={livePlayer.avatar}
                        username={livePlayer.username} rankLabel={rankLabel}
                        totalPoints={livePlayer.points} onReopen={handleReopenSurface}
                    />
                )}
            </div>

            {hasJoined && (
                <TriviaPlaySurface isOpen={isSurfaceOpen} isMobile={isMobile}
                    phase={localPhase} event={triviaEvent} countdownLabel={countdownLabel}
                    joiningReady={joiningReady} activeIntroMode={activeIntroMode}
                    question={question} questionNumber={currentQuestionIndex + 1}
                    indicatorMode={indicatorMode}
                    answerStyles={answerStyles} selectedAnswer={selectedAnswer}
                    onSelectAnswer={handleAnswerLocked} player={livePlayer}
                    lastResult={lastResult} showConfetti={finalConfetti}
                    resultsProps={{ rankLabel, questions, answerHistory, correctCount, incorrectCount }}
                    onViewFullResults={() => setLocalPhase(PHASES.FULL_RESULTS)}
                    onBackToFinal={handleBackToFinal} onClose={handleCloseSurface}
                />
            )}
        </main>
    );
}