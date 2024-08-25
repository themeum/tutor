import SVGIcon from '@Atoms/SVGIcon';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import { useDebounce } from '@Hooks/useDebounce';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { Filter } from '@Hooks/usePaginatedTable';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';

interface FilterFormValues {
	search: string;
}

interface SearchFieldProps {
	onFilterItems: (filter: Filter) => void;
}

const SearchField = ({ onFilterItems }: SearchFieldProps) => {
	const actionsForm = useFormWithGlobalError<FilterFormValues>({ defaultValues: { search: '' } });
	const searchValue = useDebounce(actionsForm.watch('search'));

	useEffect(() => {
		onFilterItems({
			...(searchValue.length > 0 && { search: searchValue }),
		});
	}, [onFilterItems, searchValue]);

	return (
		<Controller
			control={actionsForm.control}
			name="search"
			render={(controllerProps) => (
				<FormInputWithContent
					{...controllerProps}
					content={<SVGIcon name="search" width={24} height={24} />}
					placeholder={__('Search...')}
					showVerticalBar={false}
				/>
			)}
		/>
	);
};

export default SearchField;
