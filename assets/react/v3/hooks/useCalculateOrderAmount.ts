import { OrderFormValues } from '@Services/order';
import { calculateDiscount } from '@Utils/util';
import { useWatch } from 'react-hook-form';

import { FormWithGlobalErrorType } from './useFormWithGlobalError';

export const useCalculateOrderAmount = (form: FormWithGlobalErrorType<OrderFormValues>) => {
  const products = useWatch({ control: form.control, name: 'order_products' });
  const discountType = useWatch({ control: form.control, name: 'discount_type' });
  const discountValue = useWatch({ control: form.control, name: 'discount_value' });
  const discountReason = useWatch({ control: form.control, name: 'discount_reason' });
  const shipping = useWatch({ control: form.control, name: 'shipping' });

  const subtotal = products.reduce(
    (subTotal, product) => subTotal + (product.discounted_price ?? 0) * product.quantity,
    0,
  );
  const calculatedDiscount = calculateDiscount({
    discount: { type: discountType, amount: discountValue },
    total: subtotal,
  });
  const shippingAmount = shipping?.price || 0;
  const total = subtotal + shippingAmount - calculatedDiscount;

  return { discount: calculatedDiscount, discountReason, shipping, subtotal, total };
};
