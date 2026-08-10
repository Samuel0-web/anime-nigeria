/**
 * Submits a bug report.
 *
 * No backend endpoint exists yet, so this logs the assembled payload and
 * resolves after a short delay to simulate a network request. Swap the body
 * of this function for a real API call (e.g. via the existing `api.js`
 * module) once an endpoint is available — callers don't need to change.
 *
 * @param {{ description: string, images: File[], context: object }} payload
 * @returns {Promise<{ ok: boolean }>}
 */
export async function submitBugReport(payload) {
    console.log("Bug report submitted (mock)", payload);

    await new Promise((resolve) => setTimeout(resolve, 900));

    return { ok: true };
}