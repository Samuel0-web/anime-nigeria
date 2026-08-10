import { useEffect } from "react";

/** Locks background scroll while `locked` is true, restoring it after. */
export default function useBodyScrollLock(locked) {
    useEffect(() => {
        if (!locked) return undefined;

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";

        return () => {
            document.body.style.overflow = previousOverflow;
        };
    }, [locked]);
}