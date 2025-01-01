import Show from '@/v3/shared/controls/Show';
import { type MembershipFormData } from '../services/memberships';
import For from '@/v3/shared/controls/For';
import CategoryItem from './CategoryItem';
import { __, sprintf } from '@wordpress/i18n';
import SVGIcon from '@/v3/shared/atoms/SVGIcon';
import { useModal } from '@/v3/shared/components/modals/Modal';
import Button from '@/v3/shared/atoms/Button';
import CourseCategorySelectModal from '@/v3/shared/components/modals/CourseCategorySelectModal';
import { css } from '@emotion/react';
import { borderRadius, colorTokens } from '@/v3/shared/config/styles';
import { type UseFormReturn } from 'react-hook-form';

interface CategoriesProps {
  form: UseFormReturn<MembershipFormData>;
}

export default function Categories({ form }: CategoriesProps) {
  const { showModal } = useModal();
  const categories = form.watch('categories');

  return (
    <>
      <Show when={categories.length}>
        <div css={styles.categoriesWrapper}>
          <For each={categories}>
            {(category) => (
              <CategoryItem
                title={category.title}
                subTitle={sprintf(__('%s Courses', 'tutor'), category.total_courses)}
                image={category.image}
                handleDeleteClick={() => {
                  form.setValue(
                    'categories',
                    categories.filter((item) => item.id !== category.id),
                    { shouldDirty: true },
                  );
                }}
              />
            )}
          </For>
        </div>
      </Show>

      <Button
        variant="tertiary"
        isOutlined
        buttonCss={styles.addCategoriesButton}
        icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}
        onClick={() => {
          showModal({
            component: CourseCategorySelectModal,
            props: {
              title: __('Selected items', 'tutor'),
              type: 'categories',
              form,
            },
            closeOnOutsideClick: true,
            depthIndex: 9999,
          });
        }}
      >
        {__('Add Categories', 'tutor')}
      </Button>
    </>
  );
}

const styles = {
  categoriesWrapper: css`
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};
  `,
  addCategoriesButton: css`
    width: fit-content;
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.text.brand};

    svg,
    :active svg {
      color: ${colorTokens.text.brand} !important;
    }
  `,
};
