export interface Course {
  id: number;
  title: string;
  image: string;
  is_purchasable: boolean;
  regular_price: string;
  sale_price: string | null;
  total_course?: number;
  course_duration: string;
  last_updated: string;
  total_enrolled: number;
  plan_start_price?: string;
}

export interface CourseBundle {
  courses: Course[];
}
