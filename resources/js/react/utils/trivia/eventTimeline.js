// Pure, timestamp-driven derivation of the Trivia event's global state.
// Nothing here reads or writes React state, everything is a deterministic
// function of (eventStartTime, now, timeline).

export const EVENT_STATUS = {
    UPCOMING: "UPCOMING",
    LIVE_SOON: "LIVE_SOON",
    LIVE: "LIVE",
    ENDED: "ENDED",
};

export const SEGMENT_TYPE = {
    QUESTION: "QUESTION",
    // Covers quiz / true_false / double_points intros, which mode to show
    // is carried on the segment itself as `introMode`.
    MODE_INTRO: "MODE_INTRO",
};

export const QUESTION_SUB_PHASE = {
    INTRO: "INTRO",
    ANSWERING: "ANSWERING",
};

// A MODE_INTRO segment is inserted before every question, including two
// consecutive questions that share a mode, there is deliberately no
// "skip if unchanged" optimization. Double Points is just another mode
// here, it uses the exact same modeIntroDurationMs as Quiz and True or
// False rather than a duration of its own, that unification is what keeps
// every mode's animation on one shared timing budget.
export function buildEventTimeline(questions, modeIntroDurationMs) {
    const segments = [];
    let cursor = 0;

    questions.forEach((question, index) => {
        const introMode = question.doublePoints ? "double_points" : question.mode;

        segments.push({
            type: SEGMENT_TYPE.MODE_INTRO,
            introMode,
            questionIndex: index,
            startMs: cursor,
            endMs: cursor + modeIntroDurationMs,
        });

        cursor += modeIntroDurationMs;
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

export function getModeIntroSegment(timeline, questionIndex) {
    return timeline.segments.find(
        (s) => s.type === SEGMENT_TYPE.MODE_INTRO && s.questionIndex === questionIndex
    );
}

export function getQuestionSubPhase(segment, elapsedMs) {
    if (elapsedMs < segment.introEndMs) {
        return { phase: QUESTION_SUB_PHASE.INTRO, endsAtMs: segment.introEndMs };
    }
    
    return { phase: QUESTION_SUB_PHASE.ANSWERING, endsAtMs: segment.endMs };
}

// MODE_INTRO segments are passive/non-competitive, like Double Points was
// before this generalization, late entry is always fine.
export function resolveEntry(segment, elapsedMs, graceMs) {
    if (!segment) {
        return { canEnter: false, nextBoundaryMs: null };
    }

    if (segment.type === SEGMENT_TYPE.MODE_INTRO) {
        return { canEnter: true };
    }

    const elapsedInSegment = elapsedMs - segment.startMs;
    if (elapsedInSegment <= graceMs) {
        return { canEnter: true };
    }

    return { canEnter: false, nextBoundaryMs: segment.endMs };
}