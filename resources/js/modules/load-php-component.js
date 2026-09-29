export async function loadPhpComponent(url) {
    const response = await fetch(url);

    if (!response.ok) {
        throw new Error(`Failed to load component: ${url}`);
    }

    const parsed = new DOMParser().parseFromString(await response.text(), 'text/html');
    const component = parsed.body.firstElementChild;

    if (!component) {
        throw new Error(`Component response is empty: ${url}`);
    }

    return document.importNode(component, true);
}