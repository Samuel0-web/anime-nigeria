// Single source of truth for each mode's shape and color composition,
// shared by the full-size mode intros and the compact header indicator,
// so the two can never visually drift apart.

const SHAPE_PATHS = {
    triangle: <path d="M50 12 L92 84 H8 Z" />,
    diamond: <path d="M50 8 L92 50 L50 92 L8 50 Z" />,
    circle: <circle cx="50" cy="50" r="40" />,
    square: <rect x="10" y="10" width="80" height="80" rx="10" />,
};

export function ShapeGlyph({ shape, size = 24 }) {
    return (
        <svg viewBox="0 0 100 100" width={size} height={size} fill="currentColor" aria-hidden="true">
            {SHAPE_PATHS[shape]}
        </svg>
    );
}

export const QUIZ_SHAPES = [
    { key: "a", color: "red", shape: "triangle" },
    { key: "b", color: "blue", shape: "diamond" },
    { key: "c", color: "yellow", shape: "circle" },
    { key: "d", color: "green", shape: "square" },
];

export const TRUE_FALSE_SHAPES = [
    { key: "true", color: "teal", shape: "circle" },
    { key: "false", color: "purple", shape: "triangle" },
];