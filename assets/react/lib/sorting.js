window.addEventListener('DOMContentLoaded', function () {
	const getCellValue = (row, columnIndex) => {
		const cell = row.children[columnIndex];
		if (!cell) return '';
		return cell.innerText || cell.textContent;
	};

	const isValidDate = (value) => {
		const date = new Date(value);
		return !isNaN(date.getTime());
	};

	const parsePrice = (value) => {
		if (typeof value !== 'string') return NaN;

		const symbol = (_tutorobject?.tutor_currency?.symbol || '$').trim();
		if (!value.includes(symbol)) return NaN;

		const numeric = value.replace(/[^\d.,-]+/g, '').replace(/,/g, '');
		return parseFloat(numeric);
	};

	const comparer = (columnIndex, ascending) => {
		return (rowA, rowB) => {
			const valueA = getCellValue(ascending ? rowA : rowB, columnIndex).trim();
			const valueB = getCellValue(ascending ? rowB : rowA, columnIndex).trim();

			// Try to parse as date
			if (isValidDate(valueA) && isValidDate(valueB)) {
				return new Date(valueA) - new Date(valueB);
			}

			// Try to parse as price
			const priceA = parsePrice(valueA);
			const priceB = parsePrice(valueB);
			const bothArePrices = !isNaN(priceA) && !isNaN(priceB);
			if (bothArePrices) {
				return priceA - priceB;
			}

			const numberA = parseFloat(valueA);
			const numberB = parseFloat(valueB);
			if (!isNaN(numberA) && !isNaN(numberB)) {
				return numberA - numberB;
			};

			// Fallback: string comparison
			return valueA.localeCompare(valueB, undefined, { sensitivity: 'base' });
		};
	};

	document.querySelectorAll('.tutor-table-rows-sorting').forEach((th) =>
		th.addEventListener('click', (e) => {
			const table = th.closest('table');
			const tbody = table.querySelector('tbody');

			const currentTarget = e.currentTarget;
			const icon = currentTarget.querySelector('.a-to-z-sort-icon');
			// If a-to-z icon
			if (icon) {
				// swap class name to change icon
				if (icon.classList.contains('tutor-icon-ordering-a-z')) {
					icon.classList.remove('tutor-icon-ordering-a-z');
					icon.classList.add('tutor-icon-ordering-z-a');
				} else {
					icon.classList.remove('tutor-icon-ordering-z-a');
					icon.classList.add('tutor-icon-ordering-a-z');
				}
			} else {
				// swap class name to change icon
				// Order up-down-icon
				const icon = currentTarget.querySelector('.up-down-icon');
				if (icon.classList.contains('tutor-icon-order-down')) {
					icon.classList.remove('tutor-icon-order-down');
					icon.classList.add('tutor-icon-order-up');
				} else {
					icon.classList.remove('tutor-icon-order-up');
					icon.classList.add('tutor-icon-order-down');
				}
			}
			Array.from(tbody.querySelectorAll('tr:not(.tutor-do-not-sort)'))
				.sort(comparer(Array.from(th.parentNode.children).indexOf(th), (this.asc = !this.asc)))
				.forEach((tr) => tbody.appendChild(tr));
		})
	);
});
