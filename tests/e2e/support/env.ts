const password = process.env.E2E_PASSWORD ?? 'password';

export const baseURL = process.env.E2E_BASE_URL ?? process.env.SITE_URL ?? 'http://localhost:8888';

export const credentials = {
  admin: { username: 'admin', password },
  instructor: { username: 'instructor', password },
  student: { username: 'student', password },
  student2: { username: 'student2', password },
  manager: { username: 'manager', password },
} as const;

export type Role = keyof typeof credentials;

export const roles = Object.keys(credentials) as Role[];
