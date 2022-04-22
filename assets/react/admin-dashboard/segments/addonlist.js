let addonsData = _tutorobject.addons_data;

const addonsList = document.getElementById('tutor-free-addons');
const searchBar = document.getElementById('free-addons-search');
let freeAddonsList = _tutorobject.addons_data || [];
let searchString = '';
const emptyStateImg = `${_tutorobject.tutor_url}assets/images/addon-empty-state.svg`;

if (null !== searchBar) {
	searchBar.addEventListener('input', (e) => {
		searchString = e.target.value.toLowerCase();
		const filteredAddons = freeAddonsList.filter((addon) => {
			return addon.name.toLowerCase().includes(searchString);
		});

		if (filteredAddons.length > 0) {
			displayAddons(filteredAddons);
		} else {
			emptySearch();
		}
	});
}

const emptySearch = () => {
	const nothingFound = `
			<div class="tutor-addons-card tutor-p-32">
			<div class="tutor-d-flex tutor-flex-column tutor-justify-center tutor-text-center">
				<div class="tutor-mb-32">
					<img src=${emptyStateImg} alt="Empty State Illustration" />
				</div>
				<div class="tutor-fs-6 tutor-color-secondary">No Addons Found!</div>
			</div>
		</div>`;
	if (null !== addonsList) {
		addonsList.innerHTML = nothingFound;
	}
};

const displayAddons = (addons) => {
	const htmlString = addons
		.map((addon) => {
			const { name, url, description } = addon;
			return `
            <div class="tutor-col-lg-6 tutor-col-xl-4 tutor-col-xxl-3 tutor-mb-32">
				<div class="tutor-card tutor-card-md tutor-addon-card">
					<div class="tooltip-wrap tutor-lock-tooltip">
						<span class="tooltip-txt tooltip-top">Available in Pro</span>
					</div>
					<div class="tutor-card-body">
						<div class="tutor-addon-logo tutor-mb-32">
							<div class="tutor-ratio tutor-ratio-1x1">
								<img src="${url}" alt="${name}" /> 
							</div>
						</div>
						<div class="tutor-addon-title tutor-fs-6 tutor-fw-medium tutor-color-black tutor-mb-20">
							${name}
						</div>
						<div class="tutor-addon-description tutor-fs-7 tutor-color-secondary">
							${description}
						</div>
					</div>
                </div>
            </div>`;
		})
		.join('');
	if (null !== addonsList) {
		addons.length < 3 ? addonsList.classList.add('is-less-items') : addonsList.classList.remove('is-less-items');
		addonsList.innerHTML = htmlString;
	}
};

displayAddons(freeAddonsList);
