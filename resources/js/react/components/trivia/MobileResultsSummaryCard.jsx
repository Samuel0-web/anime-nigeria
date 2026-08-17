export default function MobileResultsSummaryCard({ avatar, username, rankLabel, totalPoints, onReopen }) {
    return (
        <section className="akd-trivia-mobile-summary">
            <img src={avatar} alt="" className="akd-trivia-mobile-summary__avatar" />
            <div className="akd-trivia-mobile-summary__info">
                <p className="akd-trivia-mobile-summary__username">{username}</p>
                <p className="akd-trivia-mobile-summary__meta">{rankLabel} · {totalPoints.toLocaleString()} points</p>
            </div>
            <button className="akd-trivia-btn akd-trivia-btn--secondary" onClick={onReopen}>
                View results
            </button>
        </section>
    );
}