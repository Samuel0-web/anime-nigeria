export function getPodiumUsers(users) {
    return [...users].sort((a, b) => a.rank - b.rank).slice(0, 3);
}

export function getRemainingUsers(users) {
    return [...users].sort((a, b) => a.rank - b.rank).slice(3);
}

export function getCurrentUser(users) {
    return users.find((user) => user.isCurrentUser) ?? null;
}

/** The user directly above the current user in rank (the one they'd "pass"). */
export function getUserAbove(users, currentUser) {
    if (!currentUser || currentUser.rank <= 1) return null;
    return users.find((user) => user.rank === currentUser.rank - 1) ?? null;
}

export function getXpToPassAbove(currentUser, userAbove) {
    if (!currentUser || !userAbove) return null;
    const diff = userAbove.xp - currentUser.xp;
    return diff > 0 ? diff : null;
}

export function formatXP(value) {
    return value.toLocaleString();
}

export function getInitials(name) {
    return name
        .trim()
        .split(/\s+/)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
}

/** Tiny classnames joiner — avoids pulling in a dependency for this alone. */
export function cx(...classes) {
    return classes.filter(Boolean).join(" ");
}

export function getRankChange(user) {
    if (user.previousRank == null) return { direction: "none", amount: 0 };
    const diff = user.previousRank - user.rank; // positive = moved up (lower rank number = better)

    if (diff > 0) return { direction: "up", amount: diff };
    if (diff < 0) return { direction: "down", amount: Math.abs(diff) };
    return { direction: "none", amount: 0 };
}

export function getPlayerProfileUrl(username) {
    return `/member/player/${encodeURIComponent(username)}`;
}