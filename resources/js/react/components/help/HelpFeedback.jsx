import { useState } from "react";

export default function HelpFeedback({ articleId }) {
    const [vote, setVote] = useState(null); // "yes" | "no" | null
    const [comment, setComment] = useState("");
    const [submitted, setSubmitted] = useState(false);

    const handleVote = (value) => {
        setVote(value);
        setSubmitted(false);
        if (value === "yes") {
            console.log("Help article feedback", { articleId, helpful: true });
        }
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        console.log("Help article feedback", { articleId, helpful: false, comment });
        setSubmitted(true);
    };

    return (
        <div className="akd-help-feedback">
            <p className="akd-help-feedback__prompt">Was this article helpful?</p>

            <div className="akd-help-feedback__votes" role="group"
                aria-label="Was this article helpful?"
            >
                <button type="button"
                    className={`akd-help-feedback__vote${vote === "yes" ? " is-selected" : ""}`}
                    onClick={() => handleVote("yes")} aria-pressed={vote === "yes"}
                >
                    <i className="fa-solid fa-thumbs-up" aria-hidden="true" />
                    Yes
                </button>

                <button type="button"
                    className={`akd-help-feedback__vote${vote === "no" ? " is-selected" : ""}`}
                    onClick={() => handleVote("no")} aria-pressed={vote === "no"}
                >
                    <i className="fa-solid fa-thumbs-down" aria-hidden="true" />
                    No
                </button>
            </div>

            {vote === "yes" && (
                <p className="akd-help-feedback__thanks">Thanks for letting us know.</p>
            )}

            {vote === "no" && !submitted && (
                <form className="akd-help-feedback__form" onSubmit={handleSubmit}>
                    <label htmlFor="help-feedback-comment">What could we improve?</label>
                    <textarea id="help-feedback-comment" value={comment}
                        onChange={(event) => setComment(event.target.value)}
                        placeholder="Tell us what was missing..." rows={3}
                    />

                    <button type="submit" className="akd-help-btn akd-help-btn--secondary"
                        disabled={!comment.trim()}
                    >
                        Submit feedback
                    </button>
                </form>
            )}

            {vote === "no" && submitted && (
                <p className="akd-help-feedback__thanks">Thanks for the feedback.</p>
            )}
        </div>
    );
}