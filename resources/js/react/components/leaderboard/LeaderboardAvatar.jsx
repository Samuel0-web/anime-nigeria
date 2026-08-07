import { getInitials } from "./leaderboardUtils";

export default function LeaderboardAvatar({ user, size = "md", className = "" }) {
    return (
        <div className={`akd-avatar-chip akd-avatar-chip--${size} ${className}`.trim()}>
            {user.avatar ? (
                <img src={user.avatar} alt="" className="akd-avatar-chip__image" />
            ) : (
                <div className="akd-avatar-chip__initials">{getInitials(user.fullname)}</div>
            )}
        </div>
    );
}