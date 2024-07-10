import { useToast } from '@Atoms/Toast';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { Prettify } from '@Utils/types';
import { __ } from '@wordpress/i18n';

interface OrderSummary {
  id: number;
  title: string;
  image: string;
  regular_price: number;
  sale_price?: number;
}

interface OrderCourse extends OrderSummary {
  type: 'course';
}

interface OrderBundle extends OrderSummary {
  type: 'bundle';
  total_courses: number;
}

export type OrderSummaryItem = Prettify<OrderCourse | OrderBundle>;

export type DiscountType = 'flat' | 'percentage';
export type PaymentStatus =  'paid' | 'unpaid' | 'failed' | 'partially-refunded' | 'refunded';
export type OrderStatus = 'incomplete' | 'completed' | 'cancelled' | 'trash';
export type ActivityType =
  | 'order-placed'
  | 'refunded'
  | 'partially-refunded'
  | 'comment'
  | 'mark-as-paid'
  | 'payment-failed'
  | 'cancelled';
export interface Address {
  country: string;
  state: string;
  city: string;
  zip_code: string;
  phone: string;
  address: string;
}
export interface Student {
  id: number;
  name: string;
  email: string;
  phone: string;
  image: string;
  billing_address: Partial<Address>;
}

export interface Refund {
  id: number;
  amount: number;
  reason?: string;
  date?: string;
}


export interface Activity {
  id: number;
  order_id: number;
  type: ActivityType;
  message: string;
  date: string;
}

export interface Discount {
  type: DiscountType;
  amount: number;
  reason: string;
  discounted_value: number;
}

export interface Order {
  id: number;
  payment_status: PaymentStatus;
  payment_method: string;
  payment_payloads: string | null;
  order_status: OrderStatus;
  student: Student;
  courses: OrderSummaryItem[];
  subtotal_price: number;
  discount_amount: number;
  discount_reason: string;
  discount_type: DiscountType;
  tax_rate?: number;
  tax_amount?: number;
  total_price: number;
  net_payment: number;
  fees?: number | null;
  earnings?: number | null;
  note?: string;
  refunds?: Refund[];
  transaction_id?: string | null;
  activities?: Activity[];
  coupon_code?: string|null;
  created_by: string;
  updated_by?: string;
  created_at_gmt: string;
  updated_at_gmt?: string;
}

const getOrderDetails = (orderId: number) => {
  return wpAjaxInstance
    .get<Order>(endpoints.ORDER_DETAILS, { params: { order_id: orderId } })
    .then((response) => response.data);
};

export const useOrderDetailsQuery = (orderId: number) => {
  return useQuery({
    enabled: !!orderId,
    queryKey: ['OrderDetails', orderId],
    queryFn: () => getOrderDetails(orderId),
  });
};

const postAdminComment = (params: {order_id: number; comment: string;}) => {
  return wpAjaxInstance.post(endpoints.ADMIN_COMMENT, params);
}

export const useAdminCommentMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();
  return useMutation({
    mutationFn: postAdminComment,
    onSuccess: () => {
      queryClient.invalidateQueries({queryKey: ['OrderDetails']});
      showToast({type: 'success', message: __('Comment added successfully.')});
    },
    onError: (error) => {
      showToast({type: 'danger', message: error.message});
    }
  });
}