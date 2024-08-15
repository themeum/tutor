export interface Course {
  id: number;
  title: string;
  image: '';
  author: string;
  regular_price: string;
  sale_price: string | null;
}

export interface Enrollment {
  courses: Course[];
  students: [];
  status: string;
  subscription: string;
}
