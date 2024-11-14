// Path to the local JSON file
import countries from '../../../json/countries.json';
const { __ } = wp.i18n;

const billing_country = document.querySelector('[name=billing_country]');
if (billing_country) {
    billing_country.addEventListener('change', (e) => {
        const countryName = e.target.value;
        const states = countries.find(country => country.name === countryName)?.states;
        const stateDropdown = document.querySelector('[name=billing_state]');
    
        // Clear current state options
  
        if (states && states.length > 0) {
            stateDropdown.innerHTML = ``;
            // Populate state dropdown with new states
            states.forEach(function(state) {
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateDropdown.appendChild(option);
            });
        } else {
            stateDropdown.innerHTML = `<option value="">${__( 'N/A', 'tutor')}</option>`;
        }
    });
}
