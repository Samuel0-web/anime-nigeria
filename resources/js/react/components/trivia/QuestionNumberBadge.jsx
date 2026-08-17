export default function QuestionNumberBadge({ number, variant = "circle" }) {
    if (variant === "capsule") {
        return (
            <div className="akd-trivia-capsule">
                {String(number).padStart(2, "0")}
            </div>
        );
    }

    return (
        <div className="akd-trivia-number-badge">
            {`(${String(number).padStart(2, "0")})`}
        </div>
    );
}