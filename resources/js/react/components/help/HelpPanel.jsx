import { useEffect, useId, useRef, useState } from "react";
import useFocusTrap from "../../hooks/help/useFocusTrap";
import useBodyScrollLock from "../../hooks/help/useBodyScrollLock";

const EXIT_DURATION = 280;
const DRAG_CLOSE_THRESHOLD = 120;

/**
 * Responsive panel used for the article viewer and the bug report form.
 * Renders as a right-side drawer on desktop and a bottom sheet on mobile
 * (see .akd-help-panel in _help-centre.scss) — same component, the layout
 * split is handled by CSS media queries so both are genuinely designed for
 * their breakpoint rather than one being a squeezed copy of the other.
 *
 * Stays mounted briefly after `open` becomes false so the exit transition
 * can play before unmounting.
 */
export default function HelpPanel({ open, onClose, title, children }) {
    const [mounted, setMounted] = useState(open);
    const [visible, setVisible] = useState(false);
    const panelRef = useRef(null);
    const dragStartY = useRef(null);
    const titleId = useId();

    // Trap focus only once the panel has actually mounted and rendered —
    // gating on `open` alone would fire this effect a render too early,
    // before panelRef has any content to focus.
    useFocusTrap(panelRef, mounted && open);
    useBodyScrollLock(mounted);

    useEffect(() => {
        let openFrame;
        let openFrame2;
        let closeTimer;

        if (open) {
            setMounted(true);
            setVisible(false);

            openFrame = requestAnimationFrame(() => {
                openFrame2 = requestAnimationFrame(() => {
                    setVisible(true);
                });
            });
        } else if (mounted) {
            setVisible(false);

            closeTimer = setTimeout(() => {
                setMounted(false);
            }, EXIT_DURATION);
        }

        return () => {
            cancelAnimationFrame(openFrame);
            cancelAnimationFrame(openFrame2);
            clearTimeout(closeTimer);
        };

        // `mounted` intentionally excluded.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    useEffect(() => {
        if (!open) return undefined;

        const handleKeyDown = (event) => {
            if (event.key === "Escape") onClose();
        };

        document.addEventListener("keydown", handleKeyDown);
        return () => document.removeEventListener("keydown", handleKeyDown);
    }, [open, onClose]);

    const handleDragStart = (event) => {
        dragStartY.current = event.touches[0].clientY;
    };

    const handleDragMove = (event) => {
        if (dragStartY.current === null || !panelRef.current) return;
        const delta = event.touches[0].clientY - dragStartY.current;

        if (delta > 0) {
            panelRef.current.style.transform = `translateY(${delta}px)`;
        }
    };

    const handleDragEnd = (event) => {
        if (dragStartY.current === null || !panelRef.current) return;
        const delta = event.changedTouches[0].clientY - dragStartY.current;
        dragStartY.current = null;
        panelRef.current.style.transform = "";

        if (delta > DRAG_CLOSE_THRESHOLD) onClose();
    };

    if (!mounted) return null;

    return (
        <div className={`akd-help-panel-layer${visible ? " is-visible" : ""}`}>
            <div className="akd-help-panel__backdrop" onClick={onClose} aria-hidden="true" />
            <div ref={panelRef} className="akd-help-panel" role="dialog" aria-modal="true"
                aria-labelledby={titleId}
            >
                <div className="akd-help-panel__handle" onTouchStart={handleDragStart}
                    onTouchMove={handleDragMove} onTouchEnd={handleDragEnd} aria-hidden="true"
                />
                <header className="akd-help-panel__header">
                    <h2 id={titleId} className="akd-help-panel__title">{title}</h2>

                    <button type="button" className="akd-help-panel__close" onClick={onClose}
                        aria-label="Close"
                    >
                        <i className="fa-solid fa-xmark" aria-hidden="true" />
                    </button>
                </header>

                <div className="akd-help-panel__body">{children}</div>
            </div>
        </div>
    );
}