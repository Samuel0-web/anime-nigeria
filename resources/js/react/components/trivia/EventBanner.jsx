import { EVENT_STATUS } from "../../utils/trivia/eventTimeline";

const STATUS_LABEL = {
    [EVENT_STATUS.UPCOMING]: "Live event",
    [EVENT_STATUS.LIVE_SOON]: "Live Soon",
    [EVENT_STATUS.LIVE]: "Live",
    [EVENT_STATUS.ENDED]: "Ended",
};

export default function EventBanner({ event, eventStatus, countdownLabel, reminderSet, onSetReminder, onJoin }) {
    const isEnded = eventStatus === EVENT_STATUS.ENDED;
    const canJoin = eventStatus === EVENT_STATUS.LIVE_SOON || eventStatus === EVENT_STATUS.LIVE;
    const showCountdown = eventStatus === EVENT_STATUS.UPCOMING || eventStatus === EVENT_STATUS.LIVE_SOON;
    const badgeModifier = eventStatus === EVENT_STATUS.LIVE_SOON ? "soon" : "live";

    return (
        <section
            className={`akd-trivia-banner ${isEnded ? "akd-trivia-banner--ended" : ""}`}
            style={{ backgroundImage: `url(${event.bannerImage})` }}
        >
            <div className="akd-trivia-banner__overlay" />

            <div className="akd-trivia-banner__content">
                {!isEnded && (
                    <span className={`akd-trivia-badge akd-trivia-badge--${badgeModifier}`}>
                        <span className="akd-trivia-badge__dot" />
                        {STATUS_LABEL[eventStatus]}
                    </span>
                )}

                <div className="akd-trivia-banner__bottom">
                    <p className="akd-trivia-banner__kicker">{event.kicker}</p>
                    <h1 className="akd-trivia-banner__title">{event.title}</h1>

                    <div className="akd-trivia-banner__meta">
                        <span><i className="fa-solid fa-list-ol" /> {event.questionCount} Questions</span>
                        <span className="akd-trivia-banner__meta-dot">&bull;</span>
                        <span><i className="fa-solid fa-tower-broadcast" /> {STATUS_LABEL[eventStatus]}</span>
                        <span className="akd-trivia-banner__meta-dot">&bull;</span>
                        <span><i className="fa-regular fa-clock" /> {event.estimatedDuration}</span>
                    </div>

                    {!isEnded && (
                        <div className="akd-trivia-banner__actions">
                            {showCountdown && (
                                <div className="akd-trivia-banner__countdown">
                                    <span className="akd-trivia-banner__countdown-label">Starts in</span>
                                    <span className="akd-trivia-banner__countdown-value">{countdownLabel}</span>
                                </div>
                            )}

                            {canJoin ? (
                                <button className="akd-trivia-btn akd-trivia-btn--primary" onClick={onJoin}>
                                    <i className="fa-solid fa-play" />
                                    Join now
                                </button>
                            ) : (
                                <button
                                    className={`akd-trivia-btn ${reminderSet ? "akd-trivia-btn--set" : "akd-trivia-btn--secondary"}`}
                                    onClick={onSetReminder}
                                    disabled={reminderSet}
                                >
                                    <i className={`fa-${reminderSet ? "solid" : "regular"} fa-bell`} />
                                    {reminderSet ? "Reminder set" : "Remind me"}
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}