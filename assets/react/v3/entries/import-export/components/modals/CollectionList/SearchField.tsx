import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';

import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import type { Filter } from '@TutorShared/hooks/usePaginatedTable';

interface FilterFormValues {
  search: string;
}

interface SearchFieldProps {
  onFilterItems: (filter: Filter) => void;
  initialSearchValue?: string;
}

const SearchField = ({ onFilterItems, initialSearchValue }: SearchFieldProps) => {
  const actionsForm = useFormWithGlobalError<FilterFormValues>({ defaultValues: { search: initialSearchValue || '' } });
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
          placeholder={__('Search...', 'tutor')}
          showVerticalBar={false}
        />
      )}
    />
  );
};

export default SearchField;
