import { useToast } from '@Atoms/Toast';
import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import { ErrorResponse } from '@Utils/form';
import { PaginatedParams, PaginatedResult } from '@Utils/types';
import { transformParams } from '@Utils/util';

export type CouponType = 'code' | 'automatic';

export type CouponStatus = 'active' | 'inactive' | 'trash';

export interface Course {
	id: number;
	title: string;
	image: '';
	author: string;
	regular_price: number;
	regular_price_formatted: string;
}

export interface CourseCategory {
	id: number;
	title: string;
	image: '';
	number_of_courses: number;
}

export type CouponAppliesTo =
	| 'all_courses_and_bundles'
	| 'all_courses'
	| 'all_bundles'
	| 'specific_courses'
	| 'specific_bundles'
	| 'specific_category';

export interface Coupon {
	id?: number;
	coupon_status: CouponStatus;
	coupon_type: CouponType;
	coupon_title: string;
	coupon_code: string;
	user_name: string;
	discount_type: 'percentage' | 'flat';
	discount_amount: string;
	applies_to: CouponAppliesTo;
	courses?: Course[];
	categories?: CourseCategory[];
	bundles?: Course[];
	usage_limit_status: boolean;
	total_usage_limit?: string;
	per_user_limit_status?: boolean;
	per_user_usage_limit?: string;
	coupon_uses?: number;
	purchase_requirement: 'no_minimum' | 'minimum_purchase' | 'minimum_quantity';
	purchase_requirement_value: string;
	start_date: string;
	start_time: string;
	is_end_enabled: boolean;
	end_date: string;
	end_time: string;
}

export interface CouponPayload {
	id?: number;
	coupon_status: CouponStatus;
	coupon_type: CouponType;
	coupon_title: string;
	coupon_code: string;
	discount_type: 'percentage' | 'flat';
	discount_amount: string;
	applies_to: CouponAppliesTo;
	applies_to_items?: Course[] | CourseCategory[];
	total_usage_limit?: string;
	per_user_usage_limit?: string;
	purchase_requirement: 'no_minimum' | 'minimum_purchase' | 'minimum_quantity';
	purchase_requirement_value?: string;
	start_date_gmt: string;
	expire_date_gmt?: string;
}

export const couponInitialValue: Coupon = {
	coupon_status: 'active',
	coupon_type: 'code',
	coupon_title: '',
	coupon_code: '',
	user_name: '',
	discount_type: 'percentage',
	discount_amount: '',
	applies_to: 'all_courses_and_bundles',
	courses: [],
	categories: [],
	bundles: [],
	usage_limit_status: false,
	total_usage_limit: '',
	per_user_limit_status: false,
	per_user_usage_limit: '',
	purchase_requirement: 'no_minimum',
	purchase_requirement_value: '',
	start_date: '',
	start_time: '',
	is_end_enabled: false,
	end_date: '',
	end_time: '',
};

export const mockCouponData: Coupon = {
	id: 11211,
	coupon_status: 'active',
	coupon_type: 'code',
	coupon_title: 'Winter sale',
	coupon_code: 'WINTER24',
	user_name: 'John Doe',
	discount_type: 'flat',
	discount_amount: 20,
	applies_to: 'specific_bundles',
	courses: [
		{
			id: 1,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 2,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 3,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 4,
			title: 'Soccer for Beginners',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
	],
	categories: [
		{
			id: 1,
			title: 'Soccer',
			image: '',
			number_of_courses: 10,
		},
		{
			id: 2,
			title: 'Basketball',
			image: '',
			number_of_courses: 5,
		},
		{
			id: 3,
			title: 'Tennis',
			image: '',
			number_of_courses: 15,
		},
		{
			id: 4,
			title: 'Volleyball',
			image: '',
			number_of_courses: 8,
		},
		{
			id: 5,
			title: 'Swimming',
			image: '',
			number_of_courses: 12,
		},
	],
	bundles: [
		{
			id: 1,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 2,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 3,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
		{
			id: 4,
			title: 'Soccer Bundle',
			author: 'Soccer Skills',
			image: '',
			regular_price: 120,
			regular_price_formatted: '$120.00',
		},
	],
	usage_limit_status: true,
	total_usage_limit: 100,
	per_user_limit_status: false,
	per_user_usage_limit: null,
	coupon_uses: 10,
	purchase_requirement: 'minimum_purchase',
	purchase_requirement_value: 200,
	start_date: '2024/02/16',
	start_time: '10:00:00',
	is_end_enabled: true,
	end_date: '2024/02/16',
	end_time: '10:00:00',
	created_at: '02/16/2024 10:00:00',
	updated_at: '02/16/2024 10:00:00',
};

const getCouponDetails = (couponId: number) => {
	return mockCouponData;
	// biome-ignore lint/correctness/noUnreachable: <will be implemented later>
	// return wpAjaxInstance
	// 	.get<Coupon>(endpoints.COUPON_DETAILS, { params: { coupon_id: couponId } })
	// 	.then((response) => response.data);
};

export const useCouponDetailsQuery = (couponId: number) => {
	return useQuery({
		enabled: !!couponId,
		queryKey: ['CouponDetails', couponId],
		queryFn: () => getCouponDetails(couponId),
	});
};

interface CouponResponse {
	id: number;
	message: string;
	status_code: number;
}

const createCoupon = (payload: CouponPayload) => {
	return authApiInstance.post<CouponPayload, CouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_coupon_create',
		...payload,
	});
};

export const useCreateCouponMutation = () => {
	const { showToast } = useToast();

	return useMutation({
		mutationFn: createCoupon,
		onSuccess: (response) => {
			showToast({ type: 'success', message: response.message });
		},
		onError: (error: ErrorResponse) => {
			showToast({ type: 'danger', message: error.response.data.message });
		},
	});
};

const updateCoupon = (payload: Coupon) => {
	return authApiInstance.post<Coupon, CouponResponse>(endpoints.ADMIN_AJAX, {
		action: 'tutor_coupon_update',
		...payload,
	});
};

export const useUpdateCouponMutation = () => {
	const { showToast } = useToast();
	const queryClient = useQueryClient();

	return useMutation({
		mutationFn: updateCoupon,
		onSuccess: (response) => {
			showToast({ type: 'success', message: response.message });
			queryClient.invalidateQueries({
				queryKey: ['CouponDetails', response.id],
			});
		},
		onError: (error: ErrorResponse) => {
			showToast({ type: 'danger', message: error.response.data.message });
		},
	});
};

const getCourseList = (params: PaginatedParams) => {
	return authApiInstance.get<PaginatedResult<Course>>(endpoints.COURSE_LIST, {
		params: transformParams(params),
	});
};

export const useCourseListQuery = (params: PaginatedParams) => {
	return useQuery({
		queryKey: ['CourseList', params],
		placeholderData: keepPreviousData,
		queryFn: () => {
			return {
				results: mockCouponData.courses ?? [],
				totalItems: mockCouponData.courses?.length ?? 0,
				totalPages: 1,
			};
			return getCourseList(params).then((res) => {
				return res.data;
			});
		},
	});
};

const getCategoryList = (params: PaginatedParams) => {
	return authApiInstance.get<PaginatedResult<CourseCategory>>(endpoints.CATEGORY_LIST, {
		params: transformParams(params),
	});
};

export const useCategoryListQuery = (params: PaginatedParams) => {
	return useQuery({
		queryKey: ['CourseList', params],
		placeholderData: keepPreviousData,
		queryFn: () => {
			return {
				results: mockCouponData.categories ?? [],
				totalItems: mockCouponData.categories?.length ?? 0,
				totalPages: 1,
			};
			return getCategoryList(params).then((res) => {
				return res.data;
			});
		},
	});
};
