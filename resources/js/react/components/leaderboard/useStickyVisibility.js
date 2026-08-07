import { useEffect, useRef, useState } from "react";

/**
 * Watches a single target element (either the podium or the current user's
 * row — whichever is relevant) and reports whether it's currently in view.
 * Also flags a ~1s "pulse" window on the transition from off-screen back
 * into view, per the brief's "briefly intensify the highlight" requirement.
 *
 * Deliberately skips the pulse on first mount — only a genuine scroll-back-
 * into-view should trigger it, not the initial render.
 */
export default function useStickyVisibility(targetRef,
    { rootMargin = "0px", threshold = 0.4, enabled = true } = {}
) {
    const [isTargetVisible, setIsTargetVisible] = useState(true);
    const [isPulsing, setIsPulsing] = useState(false);
    const hasObservedRef = useRef(false);
    const pulseTimeoutRef = useRef(null);

    useEffect(() => {
        if (!enabled) return undefined;
        const node = targetRef.current;
        if (!node) return undefined;

        const observer = new IntersectionObserver(
            ([entry]) => {
                const nowVisible = entry.isIntersecting;

                setIsTargetVisible((prevVisible) => {
                    if (hasObservedRef.current && !prevVisible && nowVisible) {
                        setIsPulsing(true);
                        if (pulseTimeoutRef.current) window.clearTimeout(pulseTimeoutRef.current);
                        pulseTimeoutRef.current = window.setTimeout(() => setIsPulsing(false), 1000);
                    }
                    return nowVisible;
                });

                hasObservedRef.current = true;
            },
            { rootMargin, threshold }
        );

        observer.observe(node);

        return () => {
            observer.disconnect();
            if (pulseTimeoutRef.current) window.clearTimeout(pulseTimeoutRef.current);
        };
    }, [targetRef, rootMargin, threshold, enabled]);

    return { isTargetVisible, isPulsing };
}