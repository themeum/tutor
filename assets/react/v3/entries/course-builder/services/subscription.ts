import { useQuery } from '@tanstack/react-query';

export type DurationUnit = 'day' | 'week' | 'month' | 'year';

export interface RecurringSubscription {
  pricing_option: 'recurring';
  repeat_every: number;
  repeat_unit: DurationUnit;
}

export interface LifetimeSubscription {
  pricing_option: 'one-time-purchase';
}

export type Subscription = {
  id: number;
  title: string;
  trial: number;
  trial_unit: DurationUnit;
  lifetime: number;
  lifetime_unit: DurationUnit | 'until_cancellation';
  price: number;
  charge_enrolment_fee: boolean;
  enrolment_fee: number;
  enable_trial: boolean;
  do_not_provide_certificate: boolean;
  has_sale: boolean;
  sale_price: number;
  schedule_sale_price: boolean;
  schedule_start_date: string;
  schedule_start_time: string;
  schedule_end_date: string;
  schedule_end_time: string;
} & (RecurringSubscription | LifetimeSubscription);

export const defaultSubscription: Subscription = {
  id: 0,
  title: 'New Subscription',
  pricing_option: 'recurring',
  repeat_every: 1,
  repeat_unit: 'month',
  lifetime: 1,
  lifetime_unit: 'month',
  price: 0,
  charge_enrolment_fee: false,
  enrolment_fee: 0,
  enable_trial: false,
  do_not_provide_certificate: false,
  trial: 0,
  trial_unit: 'day',
  has_sale: false,
  sale_price: 0,
  schedule_sale_price: false,
  schedule_start_date: '',
  schedule_start_time: '',
  schedule_end_date: '',
  schedule_end_time: '',
};

const mockSubscriptions: Subscription[] = [
  {
    id: 1,
    title: 'Monthly Subscription',
    pricing_option: 'recurring',
    repeat_every: 3,
    repeat_unit: 'month',
    lifetime: 3,
    lifetime_unit: 'month',
    price: 100,
    charge_enrolment_fee: false,
    enrolment_fee: 0,
    enable_trial: false,
    do_not_provide_certificate: false,
    trial: 15,
    trial_unit: 'day',
    has_sale: false,
    sale_price: 0,
    schedule_sale_price: true,
    schedule_start_date: '',
    schedule_start_time: '',
    schedule_end_date: '',
    schedule_end_time: '',
  },
  {
    id: 2,
    title: 'Yearly Subscription',
    pricing_option: 'recurring',
    repeat_every: 1,
    repeat_unit: 'year',
    trial: 10,
    trial_unit: 'day',
    lifetime: 3,
    lifetime_unit: 'year',
    price: 100,
    charge_enrolment_fee: false,
    enrolment_fee: 0,
    enable_trial: false,
    do_not_provide_certificate: false,
    has_sale: false,
    sale_price: 0,
    schedule_sale_price: true,
    schedule_start_date: '',
    schedule_start_time: '',
    schedule_end_date: '',
    schedule_end_time: '',
  },
  {
    id: 3,
    title: 'Lifetime Subscription',
    pricing_option: 'one-time-purchase',
    trial: 5,
    trial_unit: 'day',
    lifetime: -1,
    lifetime_unit: 'until_cancellation',
    price: 100,
    charge_enrolment_fee: true,
    enrolment_fee: 0,
    enable_trial: true,
    do_not_provide_certificate: false,
    has_sale: false,
    sale_price: 0,
    schedule_sale_price: true,
    schedule_start_date: '',
    schedule_start_time: '',
    schedule_end_date: '',
    schedule_end_time: '',
  },
];

const getCourseSubscriptions = (courseId: number) => {
  // @TODO: will be implemented later.
  return new Promise<Subscription[]>((resolve) => {
    setTimeout(() => {
      resolve(mockSubscriptions);
    }, 500);
  });
};

export const useCourseSubscriptionsQuery = (courseId: number) => {
  return useQuery({
    queryKey: ['SubscriptionsByCourseId', courseId],
    queryFn: () => getCourseSubscriptions(courseId),
  });
};
