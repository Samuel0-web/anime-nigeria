// Pure, timestamp-driven derivation of the Trivia event's global state.
// Nothing here reads or writes React state, everything is a deterministic
// function of (eventStartTime, now, timeline). This isolation is what lets
// it later be swapped for a real server-synchronized event clock without
// touching the rest of the Trivia UI.

export const EVENT_STATUS = {
    UPCOMING: "UPCOMING",
    LIVE_SOON: "LIVE_SOON",
    LIVE: "LIVE",
    ENDED: "ENDED",
};

export const SEGMENT_TYPE = {
    QUESTION: "QUESTION",
    DOUBLE_POINTS: "DOUBLE_POINTS",
};

export const QUESTION_SUB_PHASE = {
    INTRO: "INTRO",
    ANSWERING: "ANSWERING",
};

// Builds the ordered list of global broadcast segments (questions, plus any
// Double Points interstitial immediately before the question that flags it)
// as millisecond offsets from the event start time.
export function buildEventTimeline(questions, doublePointsDurationMs) {
    const segments = [];
    let cursor = 0;

    questions.forEach((question, index) => {
        if (question.doublePoints) {
            segments.push({
                type: SEGMENT_TYPE.DOUBLE_POINTS,
                questionIndex: index,
                startMs: cursor,
                endMs: cursor + doublePointsDurationMs,
            });
            cursor += doublePointsDurationMs;
        }

        const introMs = question.questionDuration * 1000;
        const answerMs = question.answerDuration * 1000;

        segments.push({
            type: SEGMENT_TYPE.QUESTION,
            questionIndex: index,
            startMs: cursor,
            introEndMs: cursor + introMs,
            endMs: cursor + introMs + answerMs,
        });
        cursor += introMs + answerMs;
    });

    return { segments, totalMs: cursor };
}

export function getEventStatus(now, eventStartTime, liveSoonMs, totalDurationMs) {
    const untilStart = eventStartTime - now;
    if (untilStart > liveSoonMs) return EVENT_STATUS.UPCOMING;
    if (untilStart > 0) return EVENT_STATUS.LIVE_SOON;
    if (now - eventStartTime < totalDurationMs) return EVENT_STATUS.LIVE;
    return EVENT_STATUS.ENDED;
}

export function getSegmentAtElapsed(timeline, elapsedMs) {
    if (elapsedMs < 0) return null;
    return timeline.segments.find((s) => elapsedMs >= s.startMs && elapsedMs < s.endMs) || null;
}

export function getQuestionSegment(timeline, questionIndex) {
    return timeline.segments.find(
        (s) => s.type === SEGMENT_TYPE.QUESTION && s.questionIndex === questionIndex
    );
}

export function getQuestionSubPhase(segment, elapsedMs) {
    if (elapsedMs < segment.introEndMs) {
        return { phase: QUESTION_SUB_PHASE.INTRO, endsAtMs: segment.introEndMs };
    }
    return { phase: QUESTION_SUB_PHASE.ANSWERING, endsAtMs: segment.endMs };
}

// The core "can this player enter right now" rule. Passive segments (Double
// Points) have no competitive stakes, so late entry is always fine. Question
// segments only allow entry within the grace window from their start, a
// player arriving later waits for the next segment boundary instead of being
// dropped into an almost-finished answer window.
export function resolveEntry(segment, elapsedMs, graceMs) {
    if (!segment) {
        return { canEnter: false, nextBoundaryMs: null };
    }

    if (segment.type === SEGMENT_TYPE.DOUBLE_POINTS) {
        return { canEnter: true };
    }

    const elapsedInSegment = elapsedMs - segment.startMs;
    if (elapsedInSegment <= graceMs) {
        return { canEnter: true };
    }

    return { canEnter: false, nextBoundaryMs: segment.endMs };
}