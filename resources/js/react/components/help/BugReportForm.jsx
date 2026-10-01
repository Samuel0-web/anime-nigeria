import { useEffect, useState } from "react";
import BugImageUploader from "./BugImageUploader";
import { getAutoBugContext } from "../../utils/help/bugContext";
import { submitBugReport } from "../../utils/help/submitBugReport";

const MAX_DESCRIPTION = 3000;
const MIN_DESCRIPTION = 10;
const NEAR_LIMIT_THRESHOLD = 200;
const MAX_PAGE_LENGTH = 500;

export default function BugReportForm({ onDone, initialPage = "" }) {
    const [pageUrl, setPageUrl] = useState(initialPage);
    const [description, setDescription] = useState("");
    const [images, setImages] = useState([]);
    const [imageError, setImageError] = useState("");
    const [status, setStatus] = useState("idle"); // idle | submitting | success | error
    const [errorMessage, setErrorMessage] = useState("");

    // Release object URLs created for previews if the form unmounts
    // (e.g. the panel is closed) before they're explicitly removed.
    useEffect(() => {
        return () => {
            images.forEach((image) => URL.revokeObjectURL(image.previewUrl));
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const remaining = MAX_DESCRIPTION - description.length;
    const isNearLimit = remaining <= NEAR_LIMIT_THRESHOLD;
    const canSubmit = description.trim().length >= MIN_DESCRIPTION && status !== "submitting";

    const handleDescriptionChange = (event) => {
        setDescription(event.target.value.slice(0, MAX_DESCRIPTION));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        if (!canSubmit) return;

        setStatus("submitting");
        setErrorMessage("");

        try {
            const payload = {
                page: pageUrl.trim(),
                description: description.trim(),
                images: images.map((image) => image.file),
                context: getAutoBugContext(),
            };

            const result = await submitBugReport(payload);
            if (!result.ok) throw new Error("Submission failed");

            setStatus("success");
        } catch (error) {
            setStatus("error");
            setErrorMessage("Something went wrong sending your report. Please try again.");
        }
    };

    if (status === "success") {
        return (
            <div className="akd-bug-report__success">
                <i className="fa-solid fa-circle-check" aria-hidden="true" />
                <h3>Thanks for the report</h3>
                <p>We&rsquo;ve logged the details and will look into it.</p>
                <button type="button" className="akd-help-btn akd-help-btn--secondary" onClick={onDone}>
                    Done
                </button>
            </div>
        );
    }

    return (
        <form className="akd-bug-report" onSubmit={handleSubmit}>
            <p className="akd-bug-report__intro">
                Tell us what happened. We&rsquo;ll automatically include the device
                details to help us investigate.
            </p>

            <div className="akd-bug-report__field">
                <label htmlFor="bug-page">Page where you encountered the issue</label>
                <input id="bug-page" type="text" inputMode="url" autoComplete="off"
                    value={pageUrl}
                    onChange={(event) => setPageUrl(event.target.value.slice(0, MAX_PAGE_LENGTH))}
                    placeholder="e.g. /member/profile" maxLength={MAX_PAGE_LENGTH}
                    aria-describedby="bug-page-hint"
                />
                <span id="bug-page-hint" className="akd-bug-report__hint">
                    Optional. The page address or path where it happened.
                </span>
            </div>

            <div className="akd-bug-report__field">
                <label htmlFor="bug-description">Bug description</label>
                <textarea id="bug-description" value={description}
                    onChange={handleDescriptionChange}
                    placeholder="What happened? What did you expect to happen instead?"
                    rows={6} maxLength={MAX_DESCRIPTION} required
                    aria-describedby="bug-description-counter"
                />
                <span
                    id="bug-description-counter"
                    className={`akd-bug-report__counter${isNearLimit ? " is-near-limit" : ""}`}
                >
                    {description.length.toLocaleString()} / {MAX_DESCRIPTION.toLocaleString()}
                </span>
            </div>

            <BugImageUploader images={images} onChange={setImages} onError={setImageError} />
            {imageError && (
                <p className="akd-bug-report__error" role="alert">
                    {imageError}
                </p>
            )}

            {status === "error" && (
                <p className="akd-bug-report__error" role="alert">
                    {errorMessage}
                </p>
            )}

            <button
                type="submit"
                className="akd-help-btn akd-help-btn--primary"
                disabled={!canSubmit}
            >
                {status === "submitting" ? "Sending..." : "Submit report"}
            </button>
        </form>
    );
}