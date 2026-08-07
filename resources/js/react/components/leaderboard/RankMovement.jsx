import { getRankChange } from "./leaderboardUtils";

export default function RankMovement({ user, className = "" }) {
    const { direction, amount } = getRankChange(user);

    if (direction === "none") {
        return (
            <span className={`akd-rank-movement akd-rank-movement--none ${className}`.trim()} aria-label="No change in rank">
                <i className="fa-solid fa-minus" aria-hidden="true"></i>
            </span>
        );
    }

    const isUp = direction === "up";

    return (
        <span
            className={`akd-rank-movement akd-rank-movement--${direction} ${className}`.trim()}
            aria-label={isUp ? `Moved up ${amount} position${amount === 1 ? "" : "s"}` : `Moved down ${amount} position${amount === 1 ? "" : "s"}`}
        >
            <i className={isUp ? "fa-solid fa-caret-up" : "fa-solid fa-caret-down"} aria-hidden="true"></i>
            {amount}
        </span>
    );
}