import Trivia from "./pages/Trivia";
import Leaderboard from "./pages/Leaderboard";
import HelpCentre from "./pages/HelpCentre";

export default function App() {
    const root = document.getElementById("react-root");
    const page = root?.dataset.page;

    switch (page) {
        case "leaderboard":
            return <Leaderboard />;
        case "help-centre":
            return <HelpCentre />;
        case "trivia":
            return <Trivia />;
        default:
            return null;
    }
}