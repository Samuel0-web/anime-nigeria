export default function PlayerBadge({ avatar, username, points }) {
    return (
        <div className="akd-trivia-player">
            <img src={avatar} alt="" className="akd-trivia-player__avatar" />
            <div className="akd-trivia-player__info">
                <span className="akd-trivia-player__name">{username}</span>
                <span className="akd-trivia-player__points">{points.toLocaleString()} pts</span>
            </div>
        </div>
    );
}