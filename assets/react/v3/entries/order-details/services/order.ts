interface OrderSummary {
  id: number;
  title: string;
  image?: string;
  discounted_price?: string;
  regular_price: string;
  discount?: {
    name: string;
    value: string;
  };
}

interface OrderCourse extends OrderSummary {
  type: 'course';
}

interface OrderBundle extends OrderSummary {
  type: 'bundle';
  total_courses: number;
}

export type OrderSummaryItem = OrderCourse | OrderBundle;

export interface PaymentInformation {
  label: string;
  information: string | string[];
  price: string;
}