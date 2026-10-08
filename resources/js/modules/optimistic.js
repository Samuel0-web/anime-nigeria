// resources/js/modules/optimistic.js
// One consistent pattern for optimistic UI.
//
//   apply()            change the UI now; return whatever rollback needs
//   request()          the server call (resolves to the api() payload)
//   commit(res, snap)  reconcile with the server's answer
//   rollback(snap)     undo apply() when the server refuses or the network fails
//
// Use it when the outcome is predictable and undoing it is practical (toggles, reordering,
// archive/restore, simple deletes). Do NOT use it when the server result decides what the UI
// shows, when failure needs real correction, or when an irreversible action would be claimed
// as done before the server agrees: show a pending state for those instead.
import { error as notifyError } from './toast';
import { handleApiError } from './api';

/** Runs tasks strictly one after another, so rapid taps reach the server in order. */
export function createSerialQueue() {
    let tail = Promise.resolve();

    return {
        run(task) {
            const next = tail.then(task, task);
            tail = next.catch(() => {});
            return next;
        },
    };
}

function firstMessage(result) {
    if (result?.message) return result.message;
    const first = result?.errors ? Object.values(result.errors)[0] : null;
    return typeof first === 'string' ? first : null;
}

export async function optimistic({ apply, request, commit, rollback, errorMessage = 'Something went wrong.', queue = null, onError = null }) {
    const snapshot = apply();

    const run = async () => {
        try {
            const result = await request();

            if (result && result.success === false) {
                rollback(snapshot);
                if (onError) onError(result); else notifyError(firstMessage(result) || errorMessage);
                return { ok: false, result };
            }

            commit?.(result, snapshot);
            return { ok: true, result };
        } catch (err) {
            rollback(snapshot);
            if (onError) onError(err); else handleApiError(err, errorMessage);
            return { ok: false, error: err };
        }
    };

    return queue ? queue.run(run) : run();
}