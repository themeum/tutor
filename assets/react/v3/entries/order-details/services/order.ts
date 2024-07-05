import { useQuery } from '@tanstack/react-query';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { Prettify } from '@Utils/types';

interface OrderSummary {
  id: number;
  title: string;
  image?: string;
  discounted_price?: number;
  regular_price: number;
  discount?: {
    name: string;
    value: number;
  };
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
export type PaymentStatus = 'pending' | 'paid' | 'failed' | 'partially-refunded' | 'refunded';
export type OrderStatus = 'active' | 'cancelled';
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
}

export interface Tax {
  rate: number;
  taxable_amount: number;
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
  order_status: OrderStatus;
  student: Student;
  note?: string;
  courses: OrderSummaryItem[];
  subtotal_price: number;
  discount?: Discount;
  tax?: Tax;
  total_price: number;
  refunds: Refund[];
  net_total_price: number;
  activities: Activity[];
  user: string;
  created_at: string;
  updated_at?: string;
}

const mockOrderData: Order = {
  id: 49583,
  payment_status: 'pending',
  order_status: 'active',
  student: {
    id: 1,
    name: 'Hedy Lammar',
    email: 'heddy@example.com',
    phone: '(094) 294893249',
    image: '',
    billing_address: {
      address: 'Santiago Nuetro',
      city: 'Caro, Caroa 2',
      state: 'California',
      country: 'United States',
      zip_code: '23434',
      phone: '(094) 13294884938',
    },
  },
  note: `I'm a student. Along with my university fee I'm unable to pay full payment. please give me a discount for my enrollment.`,
  courses: [
    {
      id: 1,
      title: 'Tutor LMS For Beginners Part II: Progress Your Webflow Skills',
      image: '',
      type: 'course',
      regular_price: 140,
      discounted_price: 120,
    },
    {
      id: 2,
      title: 'Frontend Courses',
      type: 'bundle',
      regular_price: 140,
      discounted_price: 120,
      total_courses: 4,
      discount: {
        name: 'Special Discount',
        value: 20,
      },
    },
    {
      id: 3,
      title: 'Backend Guru',
      image: '',
      type: 'bundle',
      regular_price: 150,
      discounted_price: 140,
      total_courses: 4,
      discount: {
        name: 'Promotional discount',
        value: 10,
      },
    },
  ],
  subtotal_price: 380,
  // discount: {
  //   type: 'percentage',
  //   amount: 10,
  //   reason: 'Special discount',
  //   discounted_value: 38,
  // },
  total_price: 342,
  refunds: [
    {
      id: 1,
      amount: 150,
      reason: 'Manual refund',
    },
    {
      id: 2,
      amount: 100,
    },
  ],
  net_total_price: 92,
  activities: [
    {
      id: 3,
      date: '2023/11/28 18:02:00',
      message: 'Partially refunded for no reason',
      order_id: 1,
      type: 'partially-refunded',
    },
    {
      id: 2,
      date: '2023/11/20 23:32:00',
      message: 'Order received by Admin',
      order_id: 1,
      type: 'order-placed',
    },
    {
      id: 1,
      date: '2023/11/17 08:30:00',
      message: 'Order placed by Hedy Lammar',
      order_id: 1,
      type: 'order-placed',
    },
  ],
  user: 'Hedy Lamarr',
  created_at: '02/16/2024 10:00:00',
  updated_at: '02/16/2024 10:00:00',
};

const getOrderDetails = (orderId: number) => {
  return mockOrderData;
  // biome-ignore lint/correctness/noUnreachable: <will be implemented later>
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
