import LeaderboardAvatar from "./LeaderboardAvatar";
import { formatXP, cx } from "./leaderboardUtils";

export default function CurrentUserStickyCard({ user, visible, xpToPassAbove, rankAbove }) {
    if (!user) return null;

    return (
        <div
            className={cx("akd-sticky-user", visible && "akd-sticky-user--visible")}
            aria-hidden={!visible}
        >
            <div className="akd-sticky-user__inner">
                <LeaderboardAvatar user={user} size="sm" className="akd-sticky-user__avatar" />

                <div className="akd-sticky-user__info">
                    <span className="akd-sticky-user__rank">
                        #{user.rank} <strong>You</strong>
                    </span>
                    <span className="akd-sticky-user__meta">
                        {formatXP(user.xp)} XP &bull; Lv.{user.level}
                    </span>
                </div>

                {xpToPassAbove != null && (
                    <span className="akd-sticky-user__chip">
                        <i className="fa-solid fa-arrow-up" aria-hidden="true"></i>
                        {formatXP(xpToPassAbove)} XP to pass #{rankAbove}
                    </span>
                )}
            </div>
        </div>
    );
}