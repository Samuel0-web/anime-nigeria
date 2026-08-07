import { forwardRef } from "react";
import LeaderboardAvatar from "./LeaderboardAvatar";
import RankMovement from "./RankMovement";
import { formatXP, cx, getPlayerProfileUrl } from "./leaderboardUtils";

const LeaderboardRow = forwardRef(function LeaderboardRow(
    { user, isCurrentUser, isPulsing, xpToPassAbove, rankAbove },
    ref
) {
    return (
        <li
            ref={ref}
            className={cx(
                "akd-leaderboard__row",
                isCurrentUser && "akd-leaderboard__row--current",
                isPulsing && "is-pulsing"
            )}
        >
            <div className="akd-leaderboard__rank-group">
                <span className="akd-leaderboard__rank">#{user.rank}</span>
                <RankMovement user={user} />
            </div>

            <LeaderboardAvatar user={user} size="sm" className="akd-leaderboard__avatar" />

            <div className="akd-leaderboard__info">
                <h3 className="akd-leaderboard__fullname">
                    <a href={getPlayerProfileUrl(user.username)} className="akd-leaderboard__name-link">
                        {user.fullname}
                    </a>
                    {isCurrentUser && <span className="akd-leaderboard__you-tag">You</span>}
                </h3>
                <span className="akd-leaderboard__username">@{user.username}</span>
            </div>

            <div className="akd-leaderboard__stats">
                <div className="akd-leaderboard__xp">
                    <strong>{formatXP(user.xp)}</strong>
                    <span>XP</span>
                </div>
                <div className="akd-leaderboard__level">
                    <strong>{user.level}</strong>
                    <span>Level</span>
                </div>
            </div>

            {isCurrentUser && xpToPassAbove != null && (
                <span className="akd-leaderboard__progress-chip">
                    <i className="fa-solid fa-arrow-up" aria-hidden="true"></i>
                    {formatXP(xpToPassAbove)} XP to pass #{rankAbove}
                </span>
            )}
        </li>
    );
});

export default LeaderboardRow;