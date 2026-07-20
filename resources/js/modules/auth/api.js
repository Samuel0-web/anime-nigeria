import { error } from "../toast";

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

export async function api(url, options = {}) {
    const { body, headers = {}, ...restOptions } = options;

    let formattedBody = body;
    const defaultHeaders = {
        Accept: "application/json",
        ...headers
    };

    const isJsonPayload =
        body &&
        typeof body === "object" &&
        !(body instanceof FormData) &&
        !(body instanceof Blob);

    if (isJsonPayload) {
        formattedBody = JSON.stringify(body);

        if (!defaultHeaders["Content-Type"]) {
            defaultHeaders["Content-Type"] = "application/json";
        }
    }

    const response = await fetch(url, {
        ...restOptions,
        headers: {
            ...defaultHeaders,
            "X-CSRF-Token": csrfToken
        },
        body: formattedBody
    });

    const data = await response.json().catch(() => ({}));

    // Validation error
    if (response.status === 422) {
        return data;
    }

    // Everything else that's an error
    if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}`);
    }

    return data;
}

export function handleApiError(err, fallback = "Something went wrong.") {
    console.error(err);
    error(err.message || fallback);
}