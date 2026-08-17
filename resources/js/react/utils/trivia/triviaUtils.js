export function formatCountdown(totalSeconds) {
    const s = Math.max(0, Math.round(totalSeconds));
    const m = Math.floor(s / 60);
    const sec = s % 60;
    return `${m}:${String(sec).padStart(2, "0")}`;
}

// Linear score: full marks for an instant answer, 0 at the buzzer.
// Kept isolated, floor/multiplier rules live in resolveQuestionPoints.
export function calculateScore(remainingMs, totalMs, maxPoints = 900) {
    if (remainingMs <= 0 || totalMs <= 0) return 0;
    const ratio = Math.min(1, Math.max(0, remainingMs / totalMs));
    return Math.round(maxPoints * ratio);
}

export function resolveQuestionPoints({ isCorrect, remainingMs, totalMs, isDoublePoints, maxPoints = 900, minPoints = 200 }) {
    if (!isCorrect) return 0;
    const raw = calculateScore(remainingMs, totalMs, maxPoints);
    const floored = Math.max(minPoints, raw);
    return isDoublePoints ? floored * 2 : floored;
}

export function applyRoundScores(players, roundIndex, playerPoints) {
    return players.map((p) => {
        if (p.id === "you") {
            return { ...p, points: p.points + playerPoints };
        }
        const roundPts = p.roundPoints?.[roundIndex] ?? 0;
        return { ...p, points: p.points + roundPts };
    });
}

export function getRankedPlayers(players) {
    return [...players]
        .sort((a, b) => b.points - a.points)
        .map((p, i) => ({ ...p, rank: i + 1 }));
}

export function ordinal(n) {
    const rem100 = n % 100;
    if (rem100 >= 11 && rem100 <= 13) return `${n}th`;
    switch (n % 10) {
        case 1: return `${n}st`;
        case 2: return `${n}nd`;
        case 3: return `${n}rd`;
        default: return `${n}th`;
    }
}

export function isPodiumFinish(rank) {
    return rank <= 3;
}

// Used on the per-question Result screen while the Trivia is still running.
export function getRankMessage(rankedPlayers, playerId) {
    const idx = rankedPlayers.findIndex((p) => p.id === playerId);
    const player = rankedPlayers[idx];

    if (isPodiumFinish(player.rank)) {
        return { positionLabel: "You're on the podium!", gapLabel: null };
    }

    const above = rankedPlayers[idx - 1];
    const gap = above.points - player.points;

    return {
        positionLabel: `You're ${ordinal(player.rank)}`,
        gapLabel: gap > 0 ? `${gap} points behind ${above.username}` : `Tied with ${above.username}`,
    };
}

// Used on the Final Result screen. Unlike the in-progress messaging above,
// the final result always states the actual rank, podium or not.
export function getFinalRankLabel(rank) {
    return `${ordinal(rank)} Place`;
}

const ENCOURAGEMENTS = [
    "Keep going. The next one is yours.",
    "Don't sweat it. You've got more questions.",
    "One miss doesn't decide the game.",
    "Stay sharp. There's still time to climb.",
];

export function getRandomEncouragement() {
    return ENCOURAGEMENTS[Math.floor(Math.random() * ENCOURAGEMENTS.length)];
}