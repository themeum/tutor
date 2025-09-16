import { useToast } from '@TutorShared/atoms/Toast';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import type { Prettify } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';

interface OrderSummary {
  id: number;
  title: string;
  image: string;
  regular_price: number;
  sale_price?: number;
  discount_price: string;
  coupon_code: string;
  item_meta_list?: {
    id: string;
    item_id: string;
    meta_key: string;
    meta_value: string;
  }[];
}

interface OrderCourse extends OrderSummary {
  type: 'course';
}

interface OrderBundle extends OrderSummary {
  type: 'course-bundle';
  total_courses: number;
}
interface OrderCoursePlan extends OrderSummary {
  type: 'course_plan';
  plan_info: {
    id: string;
    plan_name: string;
  };
}

export type OrderSummaryItem = Prettify<OrderCourse | OrderBundle | OrderCoursePlan>;

export type DiscountType = 'flat' | 'percentage';
export type PaymentStatus = 'paid' | 'unpaid' | 'failed' | 'partially-refunded' | 'refunded';
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
  created_at_readable: string;
  cancel_reason?: string;
}

export interface Discount {
  type: DiscountType;
  amount: number;
  reason: string;
  discounted_value: number;
}

interface SubscriptionFees {
  title: string;
  value: string;
}

export interface Order {
  id: number;
  payment_status: PaymentStatus;
  payment_method: string;
  payment_method_readable: string;
  payment_payloads: string | null;
  order_status: OrderStatus;
  order_type: string;
  student: Student;
  items: OrderSummaryItem[];
  subtotal_price: number;
  discount_amount: number;
  discount_reason: string;
  discount_type: DiscountType;
  tax_type?: string | null;
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
  coupon_code?: string | null;
  coupon_amount?: number | null;
  created_by: string;
  updated_by?: string;
  created_at_gmt: string;
  created_at_readable: string;
  updated_at_gmt?: string;
  updated_at_readable?: string;
  subscription_fees?: SubscriptionFees[];
}

interface Response {
  data: unknown;
  message: string;
  status_code: number;
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

const postAdminComment = (params: { order_id: number; comment: string }) => {
  return wpAjaxInstance.post<unknown, Response>(endpoints.ADMIN_COMMENT, params);
};

export const useAdminCommentMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();
  return useMutation({
    mutationFn: postAdminComment,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ['OrderDetails'] });
      showToast({ type: 'success', message: response.message });
    },
    onError: (error) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const markAsPaid = (params: { order_id: number; note: string }) => {
  return wpAjaxInstance.post<unknown, Response>(endpoints.ORDER_MARK_AS_PAID, params);
};

export const useMarkAsPaidMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();
  return useMutation({
    mutationFn: markAsPaid,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ['OrderDetails'] });
      showToast({ type: 'success', message: response.message });
    },
    onError: (error) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const refundOrder = (params: { order_id: number; reason: string; is_remove_enrolment: boolean }) => {
  return wpAjaxInstance.post<unknown, Response>(endpoints.ORDER_REFUND, params);
};

export const useRefundOrderMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: refundOrder,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ['OrderDetails'] });
      showToast({ type: 'success', message: response.message });
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

interface DiscountPayload {
  order_id: number;
  discount_type: 'flat' | 'percentage';
  discount_amount: number;
  discount_reason: string;
}
const addOrderDiscount = (payload: DiscountPayload) => {
  return wpAjaxInstance.post<unknown, Response>(endpoints.ADD_ORDER_DISCOUNT, payload);
};

export const useOrderDiscountMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: addOrderDiscount,
    onSuccess(response) {
      queryClient.invalidateQueries({ queryKey: ['OrderDetails'] });
      showToast({ type: 'success', message: response.message });
    },
    onError(error) {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const cancelOrder = (params: { order_id: number; cancel_reason: string }) => {
  return wpAjaxInstance.post<unknown, Response>(endpoints.ORDER_CANCEL, params);
};

export const useCancelOrderMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();
  return useMutation({
    mutationFn: cancelOrder,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ['OrderDetails'] });
      showToast({ type: 'success', message: response.message });
    },
    onError: (error) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
