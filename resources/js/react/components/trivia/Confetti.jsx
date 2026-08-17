import { useEffect, useRef, useState } from "react";

const COLORS = ["#e8a3b3", "#DF9A1B", "#6bbfba", "#a88cd8", "#e0935f"];
const PRODUCTION_MS = 4200;
const SPAWN_INTERVAL_MS = 300;
const PIECES_PER_BATCH = 2;

function makePiece(id) {
    return {
        id,
        left: Math.random() * 100,
        duration: 2.4 + Math.random() * 1.4,
        rotation: Math.random() * 360,
        color: COLORS[id % COLORS.length],
        drift: (Math.random() - 0.5) * 90,
    };
}

// Bursts, keeps producing for a few seconds, then stops, existing pieces
// finish falling on their own CSS animation timers rather than being cut off.
export default function Confetti() {
    const [pieces, setPieces] = useState(() => Array.from({ length: 4 }, (_, i) => makePiece(i)));
    const nextIdRef = useRef(4);

    useEffect(() => {
        let elapsed = 0;
        const intervalId = setInterval(() => {
            elapsed += SPAWN_INTERVAL_MS;
            if (elapsed >= PRODUCTION_MS) {
                clearInterval(intervalId);
                return;
            }
            setPieces((prev) => [
                ...prev,
                ...Array.from({ length: PIECES_PER_BATCH }, () => makePiece(nextIdRef.current++)),
            ]);
        }, SPAWN_INTERVAL_MS);

        return () => clearInterval(intervalId);
    }, []);

    return (
        <div className="akd-trivia-confetti" aria-hidden="true">
            {pieces.map((piece) => (
                <span
                    key={piece.id}
                    className="akd-trivia-confetti__piece"
                    style={{
                        left: `${piece.left}%`,
                        backgroundColor: piece.color,
                        animationDuration: `${piece.duration}s`,
                        "--rotate-from": `${piece.rotation}deg`,
                        "--drift": `${piece.drift}px`,
                    }}
                />
            ))}
        </div>
    );
}