import { error } from "./toast";

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

export async function api(url, options = {}) {
    const { body, headers = {}, ...restOptions } = options;

    let formattedBody = body;
    const defaultHeaders = {
        Accept: "application/json",
        ...headers
    };

    const isJsonPayload = body && typeof body === "object" && !(body instanceof FormData) &&
        !(body instanceof Blob);

    if (isJsonPayload) {
        formattedBody = JSON.stringify(body);

        if (!defaultHeaders["Content-Type"]) {
            defaultHeaders["Content-Type"] = "application/json";
        }
    }

    let response;

    try {
        response = await fetch(url, {
            ...restOptions,
            headers: {
                ...defaultHeaders,
                "X-CSRF-Token": csrfToken
            },
            body: formattedBody
        });
    } catch {
        const err = new Error("Network error");
        err.status = 0;
        throw err;
    }

    const text = await response.text();
    const data = text ? JSON.parse(text) : {};

    // Validation error
    if (response.status === 422) {
        return data;
    }

    // Everything else that's an error
    if (!response.ok) {
        const err = new Error(data.message || `HTTP ${response.status}`);
        err.status = response.status;
        err.data = data;
        throw err;
    }

    return data;
}

export function handleApiError(err, fallback = "Something went wrong.") {
    switch (err.status) {
        case 0:
            error("You're offline. Please check your internet connection.");
            break;
            
        case 403:
            error(err.data?.message || "Your session has expired. Please refresh the page.");
            break;

        case 429:
            error(err.data?.message || "You're doing that too often. Please wait a little before trying again.");
            break;

        case 500:
            error(err.data?.message || "Something went wrong. Please try again later.");
            break;

        default:
            error(err.data?.message || err.message || fallback);
    }
}