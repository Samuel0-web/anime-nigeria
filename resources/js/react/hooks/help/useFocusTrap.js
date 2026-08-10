import { useEffect, useRef } from "react";

const FOCUSABLE_SELECTOR =
    'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Traps Tab/Shift+Tab focus within `containerRef` while `active` is true,
 * moves focus into the container when it activates, and restores focus to
 * whatever was previously focused when it deactivates.
 */
export default function useFocusTrap(containerRef, active) {
    const previouslyFocused = useRef(null);

    useEffect(() => {
        if (!active) return undefined;

        previouslyFocused.current = document.activeElement;
        const container = containerRef.current;

        const focusFirst = () => {
            const focusable = container?.querySelectorAll(FOCUSABLE_SELECTOR);
            focusable?.[0]?.focus();
        };
        const frame = requestAnimationFrame(focusFirst);

        const handleKeyDown = (event) => {
            if (event.key !== "Tab" || !container) return;

            const focusable = Array.from(
                container.querySelectorAll(FOCUSABLE_SELECTOR)
            ).filter((el) => el.offsetParent !== null);

            if (focusable.length === 0) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };

        document.addEventListener("keydown", handleKeyDown);

        return () => {
            cancelAnimationFrame(frame);
            document.removeEventListener("keydown", handleKeyDown);
            previouslyFocused.current?.focus?.();
        };
    }, [active, containerRef]);
}