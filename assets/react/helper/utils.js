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
 * Extracts Tutor-Message header from response
 * 
 * @param {Response} response - Fetch Response object
 * @returns {string|undefined} Message from Tutor-Message header if exists, undefined otherwise 
 */
export function tutorHeaderMessage(response) {
    try {
        response.headers.get('Tutor-Message')
    } catch (error) {
        return undefined;
    }
}