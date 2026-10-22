export const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8888';

export const credentials = {
  admin: {
    username: process.env.E2E_ADMIN_USERNAME ?? 'admin',
    password: process.env.E2E_ADMIN_PASSWORD ?? 'password',
  },
  instructor: {
    username: process.env.E2E_INSTRUCTOR_USERNAME ?? 'instructor',
    password: process.env.E2E_INSTRUCTOR_PASSWORD ?? 'password',
  },
  student: {
    username: process.env.E2E_STUDENT_USERNAME ?? 'student',
    password: process.env.E2E_STUDENT_PASSWORD ?? 'password',
  },
  student2: {
    username: process.env.E2E_STUDENT2_USERNAME ?? 'student2',
    password: process.env.E2E_STUDENT2_PASSWORD ?? 'password',
  },
  manager: {
    username: process.env.E2E_MANAGER_USERNAME ?? 'manager',
    password: process.env.E2E_MANAGER_PASSWORD ?? 'password',
  },
} as const;

export type Role = keyof typeof credentials;

export const roles = Object.keys(credentials) as Role[];
