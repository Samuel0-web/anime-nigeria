import QuizIntro from "./QuizIntro";
import TrueFalseIntro from "./TrueFalseIntro";
import DoublePoints from "../DoublePoints";

// Pure dispatcher. Adding a fourth mode later means adding one more case
// here and its own Intro component, nothing else in the Trivia state
// machine needs to know or care.
export default function ModeIntro({ mode, isMobile }) {
    if (mode === "true_false") return <TrueFalseIntro isMobile={isMobile} />;
    if (mode === "double_points") return <DoublePoints />;
    return <QuizIntro isMobile={isMobile} />;
}