import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { useProductsWithVariantsModal } from '@Components/modals/ProductsWithVariantsModal';
import { ProductWithVariant } from '@Components/modals/ProductsWithVariantsModal/ProductsWithVariants';
import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import ImageCard from '@Molecules/ImageCard';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';

import FormFieldWrapper from './FormFieldWrapper';

export interface Product {
  id: number;
  name: string;
  image: string;
  total_variants: number;
  variants: number[];
}
interface FormSelectProductsWithVariantsProps extends FormControllerProps<Product[] | undefined> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  helpText?: string;
}

const FormSelectProductsWithVariants = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  helpText,
}: FormSelectProductsWithVariantsProps) => {
  const t = useTranslation();
  const { showProductsWithVariantsModal } = useProductsWithVariantsModal();

  const handleRemoveSelectedProducts = (id: number) => {
    const updatedValue = (field.value || []).filter((item) => item.id !== id);
    field.onChange(updatedValue);
  };

  const handleSelectProducts = (selectedProducts: Map<string, ProductWithVariant>) => {
    const previousSelectedProducts = new Map((field.value ?? []).map((product) => [product.id, product]));

    const selectedProductsObject = [...selectedProducts.values()].reduce<Record<string, Product>>(
      (products, product) => {
        const { product_id, variant_id, state, name, image, total_variants = 0, variantIds } = product;
        const isChecked = state === 'checked' || state === 'indeterminate';
        previousSelectedProducts.delete(product_id);

        if (isDefined(variantIds) || !isChecked) {
          return products;
        }

        if (!(product_id in products)) {
          const newProduct: Product = {
            id: product_id,
            name,
            image,
            variants: [],
            total_variants: 0,
          };

          if (isDefined(variant_id)) {
            newProduct.total_variants = total_variants;
            newProduct.variants.push(variant_id);
          }
          return { ...products, [product_id]: newProduct };
        }

        if (product_id in products && isDefined(variant_id)) {
          const existingProduct = products[product_id];

          return {
            ...products,
            [product_id]: { ...existingProduct, total_variants, variants: [...existingProduct.variants, variant_id] },
          };
        }

        return products;
      },
      {},
    );

    field.onChange([...Object.values(selectedProductsObject), ...previousSelectedProducts.values()]);
  };

  const showModalHandler = () => {
    const selectedProductIds = (field.value ?? []).flatMap((product) => {
      return product.total_variants > 0
        ? product.variants.map((variantId) => `${product.id}+${variantId}`)
        : product.id.toString();
    });

    showProductsWithVariantsModal({
      selectedProductIds,
      onSelectProducts: handleSelectProducts,
    });
  };

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      helpText={helpText}
    >
      {() => {
        return (
          <div css={styles.inputWrapper}>
            {!!field.value?.length ? (
              <>
                <p css={typography.body()}>{t('COM_SPPAGEBUILDER_STORE_COUPON_SELECT_PRODUCTS')}</p>

                <div css={styles.productWrapper}>
                  {field.value.map((product) => {
                    return (
                      <div key={product.id} css={styles.productItem}>
                        <div css={styles.productImageTitle}>
                          <div css={styles.productImage}>
                            <ImageCard name={product.name} path={product.image} />
                          </div>
                          <h5 css={typography.heading6('medium')}>{product.name}</h5>
                        </div>

                        <div css={styles.productVariants}>
                          {product.total_variants && product.total_variants > 0
                            ? t('COM_SPPAGEBUILDER_STORE_COUPON_VARIANTS_SELECTED', {
                                total: product.total_variants,
                                selected: product.variants.length,
                              })
                            : ''}
                        </div>

                        <button
                          type="button"
                          css={styleUtils.resetButton}
                          onClick={() => handleRemoveSelectedProducts(product.id)}
                        >
                          <SVGIcon name="trash" width={16} height={16} />
                        </button>
                      </div>
                    );
                  })}
                </div>

                <Button
                  icon={<SVGIcon name="pencil" height={16} width={16} />}
                  variant={ButtonVariant.plain}
                  onClick={showModalHandler}
                >
                  {t('COM_SPPAGEBUILDER_STORE_COUPON_EDIT_PRODUCTS')}
                </Button>
              </>
            ) : (
              <Button
                variant={ButtonVariant.plainMonochrome}
                icon={<SVGIcon name="plusCircle" width={19} height={19} />}
                disabled={disabled}
                onClick={showModalHandler}
                buttonCss={styles.addCategoryButton}
              >
                <span>{t('COM_SPPAGEBUILDER_STORE_COUPON_SELECT_PRODUCTS')}</span>
              </Button>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormSelectProductsWithVariants;

const styles = {
  inputWrapper: css`
    width: 100%;
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorPalate.border.neutral};
    box-shadow: ${shadow.input};
    padding: ${spacing[10]} ${spacing[16]};
    background-color: ${colorPalate.surface.default};
  `,
  addCategoryButton: css`
    &,
    :hover {
      color: ${colorPalate.text.default};
    }
  `,
  productWrapper: css`
    padding: ${spacing[8]} 0;
  `,
  productItem: css`
    display: flex;
    align-items: center;
    padding: ${spacing[8]} 0;
    box-shadow: ${shadow.underline};

    &:last-child {
      box-shadow: none;
    }
  `,
  productImageTitle: css`
    display: flex;
    align-items: center;
    gap: ${spacing[10]};
  `,
  productImage: css`
    > div {
      width: 40px;
      height: 40px;
    }
  `,
  productVariants: css`
    ${typography.body()}
    flex: auto;
    text-align: center;
    color: ${colorPalate.text.neutral};
  `,
};
