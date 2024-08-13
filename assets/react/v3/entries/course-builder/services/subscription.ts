import { useToast } from '@Atoms/Toast';
import { DateFormats } from '@Config/constants';
import { wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { AxiosResponse } from 'axios';
import { format, parseISO } from 'date-fns';
import type { TutorMutationResponse } from './course';
import type { ID } from './curriculum';

export type DurationUnit = 'hour' | 'day' | 'week' | 'month' | 'year';

export type Subscription = {
  id: string;
  payment_type: 'onetime' | 'recurring';
  plan_type: 'course' | 'category' | 'full_site';
  assign_id: string; // course_id, category_id, or 0 for full site
  plan_name: string;
  recurring_value: string;
  recurring_interval: Omit<DurationUnit, 'hour'>;
  is_recommended: '0' | '1';
  regular_price: string;
  sale_price: string;
  sale_price_from: string; // start date
  sale_price_to: string; // end date
  plan_duration: string; // 0 for until canceled
  provide_certificate: '0' | '1';
  enrollment_fee: string;
  trial_value: string;
  trial_interval: DurationUnit;
};

export interface SubscriptionFormData
  extends Omit<Subscription, 'provide_certificate' | 'sale_price_from' | 'sale_price_to' | 'is_recommended'> {
  charge_enrollment_fee: boolean;
  enable_free_trial: boolean;
  offer_sale_price: boolean;
  schedule_sale_price: boolean;
  is_recommended: boolean;
  do_not_provide_certificate: boolean;
  sale_price_from_date: string;
  sale_price_from_time: string;
  sale_price_to_date: string;
  sale_price_to_time: string;
}

export const defaultSubscriptionFormData: SubscriptionFormData = {
  id: '0',
  payment_type: 'recurring',
  plan_type: 'course',
  assign_id: '0',
  plan_name: '',
  recurring_value: '1',
  recurring_interval: 'month',
  is_recommended: false,
  regular_price: '0',
  sale_price: '0',
  sale_price_from_date: '',
  sale_price_from_time: '',
  sale_price_to_date: '',
  sale_price_to_time: '',
  plan_duration: 'Until cancelled',
  do_not_provide_certificate: false,
  enrollment_fee: '0',
  trial_value: '0',
  trial_interval: 'day',
  charge_enrollment_fee: false,
  enable_free_trial: false,
  offer_sale_price: false,
  schedule_sale_price: false,
};

const convertPlanLengthToDays = (recurring_interval: Omit<DurationUnit, 'hour'>, length: number): string => {
  switch (recurring_interval) {
    case 'day':
      return String(length);
    case 'week':
      return String(length * 7);
    case 'month':
      return String(length * 30);
    case 'year':
      return String(length * 365);
    default:
      return '0';
  }
};

const convertDaysToPlanLength = (days: number, recurring_interval: Omit<DurationUnit, 'hour'>): string => {
  switch (recurring_interval) {
    case 'day':
      return String(days);
    case 'week':
      return String(days / 7);
    case 'month':
      return String(days / 30);
    case 'year':
      return String(days / 365);
    default:
      return '0';
  }
};

export const convertSubscriptionToFormData = (subscription: Subscription): SubscriptionFormData => {
  return {
    id: subscription.id,
    payment_type: subscription.payment_type ?? 'recurring',
    plan_type: subscription.plan_type ?? 'course',
    assign_id: subscription.assign_id,
    plan_name: subscription.plan_name ?? '',
    recurring_value: subscription.recurring_value ?? '0',
    recurring_interval: subscription.recurring_interval ?? 'month',
    is_recommended: !!Number(subscription.is_recommended),
    regular_price: subscription.regular_price ?? '0',
    plan_duration: subscription.plan_duration ?? 'Until cancelled',
    enrollment_fee: subscription.enrollment_fee ?? '0',
    trial_value: subscription.trial_value ?? '0',
    trial_interval: subscription.trial_interval ?? 'day',
    sale_price: subscription.sale_price ?? '0',
    charge_enrollment_fee: !!Number(subscription.enrollment_fee),
    enable_free_trial: !!Number(subscription.trial_value),
    offer_sale_price: !!Number(subscription.sale_price),
    schedule_sale_price: !!Number(subscription.sale_price_from),
    do_not_provide_certificate: !Number(subscription.provide_certificate),
    sale_price_from_date: subscription.sale_price_from
      ? format(parseISO(subscription.sale_price_from), DateFormats.yearMonthDay)
      : '',
    sale_price_from_time: subscription.sale_price_from
      ? format(parseISO(subscription.sale_price_from), DateFormats.hoursMinutes)
      : '',
    sale_price_to_date: subscription.sale_price_to
      ? format(parseISO(subscription.sale_price_to), DateFormats.yearMonthDay)
      : '',
    sale_price_to_time: subscription.sale_price_to
      ? format(parseISO(subscription.sale_price_to), DateFormats.hoursMinutes)
      : '',
  };
};

export const convertFormDataToSubscription = (formData: SubscriptionFormData): SubscriptionPayload => {
  return {
    ...(formData.id && String(formData.id) !== '0' && { id: formData.id }),
    payment_type: formData.payment_type,
    plan_type: formData.plan_type,
    assign_id: formData.assign_id,
    plan_name: formData.plan_name,
    ...(formData.payment_type === 'recurring' && {
      recurring_value: formData.recurring_value,
      recurring_interval: formData.recurring_interval,
    }),
    regular_price: formData.regular_price,
    plan_duration: formData.plan_duration === 'Until cancelled' ? '0' : formData.plan_duration,
    is_recommended: formData.is_recommended ? '1' : '0',
    ...(formData.charge_enrollment_fee && { enrollment_fee: formData.enrollment_fee }),
    ...(formData.enable_free_trial && { trial_value: formData.trial_value, trial_interval: formData.trial_interval }),
    sale_price: formData.offer_sale_price ? formData.sale_price : '0',
    ...(formData.schedule_sale_price && {
      sale_price_from: format(
        new Date(`${formData.sale_price_from_date} ${formData.sale_price_from_time}`),
        DateFormats.yearMonthDayHourMinuteSecond,
      ),
      sale_price_to: format(
        new Date(`${formData.sale_price_to_date} ${formData.sale_price_to_time}`),
        DateFormats.yearMonthDayHourMinuteSecond,
      ),
    }),

    provide_certificate: formData.do_not_provide_certificate ? '0' : '1',
  };
};

export type SubscriptionPayload = {
  id?: string; // only for update
  payment_type: 'onetime' | 'recurring';
  plan_type: 'course' | 'category' | 'full_site';
  assign_id: string; // course_id, category_id, or 0 for full site
  plan_name: string;
  recurring_value?: string;
  recurring_interval?: Omit<DurationUnit, 'hour'>;
  regular_price: string;
  sale_price?: string;
  sale_price_from?: string; // start date
  sale_price_to?: string; // end date
  plan_duration: string; // 30, 60, 90, 120, 365 and 0 for until canceled
  provide_certificate: '0' | '1';
  is_recommended: '0' | '1';
  enrollment_fee?: string;
  trial_value?: string;
  trial_interval?: DurationUnit;
};

const getCourseSubscriptions = (courseId: number) => {
  return wpAjaxInstance.post<string, AxiosResponse<Subscription[]>>(endpoints.GET_SUBSCRIPTIONS_LIST, {
    course_id: courseId,
  });
};

export const useCourseSubscriptionsQuery = (courseId: number) => {
  return useQuery({
    queryKey: ['SubscriptionsList', courseId],
    queryFn: () => getCourseSubscriptions(courseId).then((response) => response.data),
  });
};

const saveCourseSubscription = (courseId: number, subscription: SubscriptionPayload) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<ID>>(endpoints.SAVE_SUBSCRIPTION, {
    course_id: courseId,
    ...(subscription.id && { id: subscription.id }),
    ...subscription,
  });
};

export const useSaveCourseSubscriptionMutation = (courseId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscription: SubscriptionPayload) => saveCourseSubscription(courseId, subscription),
    onSuccess: (response) => {
      if (response.data) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['SubscriptionsList', courseId],
        });
      }
    },
  });
};

const deleteCourseSubscription = (courseId: number, subscriptionId: number) => {
  return wpAjaxInstance.post<
    {
      course_id: number;
      id: number;
    },
    TutorMutationResponse<ID>
  >(endpoints.DELETE_SUBSCRIPTION, {
    course_id: courseId,
    id: subscriptionId,
  });
};

export const useDeleteCourseSubscriptionMutation = (courseId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscriptionId: number) => deleteCourseSubscription(courseId, subscriptionId),
    onSuccess: (response, subscriptionId) => {
      if (response.status_code === 200) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.setQueryData(['SubscriptionsList', courseId], (data: Subscription[]) => {
          return data.filter((item) => item.id !== String(subscriptionId));
        });
      }
    },
  });
};

const duplicateCourseSubscription = (courseId: number, subscriptionId: number) => {
  return wpAjaxInstance.post<
    {
      course_id: number;
      id: number;
    },
    TutorMutationResponse<ID>
  >(endpoints.DUPLICATE_SUBSCRIPTION, {
    course_id: courseId,
    id: subscriptionId,
  });
};

export const useDuplicateCourseSubscriptionMutation = (courseId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscriptionId: number) => duplicateCourseSubscription(courseId, subscriptionId),
    onSuccess: (response) => {
      if (response.data) {
        showToast({
          message: response.message,
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['SubscriptionsList', courseId],
        });
      }
    },
  });
};

const sortCourseSubscriptions = (courseId: number, subscriptionIds: number[]) => {
  return wpAjaxInstance.post<
    {
      course_id: number;
      plan_ids: number[];
    },
    TutorMutationResponse<ID>
  >(endpoints.SORT_SUBSCRIPTION, {
    course_id: courseId,
    plan_ids: subscriptionIds,
  });
};

export const useSortCourseSubscriptionsMutation = (courseId: number) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (subscriptionIds: number[]) => sortCourseSubscriptions(courseId, subscriptionIds),
    onSuccess: (response, payload) => {
      if (response.status_code === 200) {
        queryClient.setQueryData(['SubscriptionsList', courseId], (data: Subscription[]) => {
          const sortedIds = payload.map((id) => String(id));

          return data.sort((a, b) => sortedIds.indexOf(a.id) - sortedIds.indexOf(b.id));
        });
      }
    },
    onError: (error) => {
      showToast({
        message: error.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['SubscriptionsList', courseId],
      });
    },
  });
};
