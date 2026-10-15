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

export function isMobileDevice() {
	return /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}
