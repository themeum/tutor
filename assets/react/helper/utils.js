const { __ } = wp.i18n;

export async function fetchCountriesData() {
    try {
        const response = await fetch(`${_tutorobject.tutor_url}/assets/json/countries.json`);

        if (!response.ok) {
            throw new Error(`Failed to fetch countries: ${response.status} ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Error fetching countries:', error);
        return [];
    }
}

/**
 * Get Tutor-Message header value from a fetch Response.
 *
 * @param {Response} response - Fetch response object
 * @returns {string|null} - The Tutor-Message header value or null if not set
 */
export async function getTutorMessage(response) {
    const defaultErrorMessage = __('Something went wrong, please try again', 'tutor-pro');
    
    if (!(response instanceof Response)) {
        return defaultErrorMessage;
    }

    const res = await response.json();
    if (res && res.message) {
        console.log('log', res.message);
        return res.message;
    }

    return defaultErrorMessage;
}