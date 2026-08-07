import { forwardRef } from "react";
import LeaderboardAvatar from "./LeaderboardAvatar";
import RankMovement from "./RankMovement";
import { formatXP, cx, getPlayerProfileUrl } from "./leaderboardUtils";

const RANK_ICON = {
    1: "fa-solid fa-crown",
    2: "fa-solid fa-medal",
    3: "fa-solid fa-medal",
};

const LeaderboardPodium = forwardRef(function LeaderboardPodium(
    { users, currentUserId, isPulsing, xpToPassAbove, rankAbove },
    ref
) {
    if (!users.length) return null;

    return (
        <section className="akd-podium" ref={ref} aria-label="Top 3 members">
            <ol className="akd-podium__list">
                {users.map((user) => {
                    const isCurrent = user.id === currentUserId;

                    return (
                        <li
                            key={user.id}
                            className={cx(
                                "akd-podium__card",
                                `akd-podium__card--rank-${user.rank}`,
                                isCurrent && "akd-podium__card--current",
                                isCurrent && isPulsing && "is-pulsing"
                            )}
                        >
                            <span className="akd-podium__rank-icon" aria-hidden="true">
                                <i className={RANK_ICON[user.rank]}></i>
                            </span>

                            <LeaderboardAvatar
                                user={user}
                                size={user.rank === 1 ? "lg" : "md"}
                                className="akd-podium__avatar"
                            />

                            <h3 className="akd-podium__name">
                                <a href={getPlayerProfileUrl(user.username)} className="akd-podium__name-link">
                                    {user.fullname}
                                </a>
                            </h3>
                            <span className="akd-podium__username">@{user.username}</span>

                            <div className="akd-podium__xp">
                                <strong>{formatXP(user.xp)}</strong>
                                <span>XP</span>
                            </div>

                            <span className="akd-podium__badge">#{user.rank}</span>
                            <RankMovement user={user} className="akd-podium__movement" />

                            {isCurrent && xpToPassAbove != null && (
                                <span className="akd-podium__progress-chip">
                                    <i className="fa-solid fa-arrow-up" aria-hidden="true"></i>
                                    {formatXP(xpToPassAbove)} XP to pass #{rankAbove}
                                </span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </section>
    );
});

export default LeaderboardPodium;