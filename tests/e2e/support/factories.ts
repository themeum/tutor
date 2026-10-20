import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import path from 'node:path';

const pluginSlug = path.basename(process.cwd());
const factoryScript = `wp-content/plugins/${pluginSlug}/bin/e2e/factories.php`;
const resultsDir = path.join(process.cwd(), '.e2e');

type FactoryInput = Record<string, unknown>;

function runFactory<T>(factory: string, input: FactoryInput = {}): T {
  mkdirSync(resultsDir, { recursive: true });

  const resultFile = `${randomUUID()}.json`;
  const resultPath = path.join(resultsDir, resultFile);
  const payload = Buffer.from(
    JSON.stringify({
      factory,
      ...input,
      result_file: resultFile,
    }),
  ).toString('base64');

  const wpEnvArgs = process.env.E2E_WP_ENV_CONFIG
    ? ['--config', process.env.E2E_WP_ENV_CONFIG, 'run', 'cli', '--', 'wp', 'eval-file', factoryScript, payload]
    : ['run', 'cli', '--', 'wp', 'eval-file', factoryScript, payload];

  try {
    execFileSync('pnpm', ['exec', 'wp-env', ...wpEnvArgs], {
      cwd: process.cwd(),
      encoding: 'utf8',
      maxBuffer: 10 * 1024 * 1024,
      stdio: ['ignore', 'pipe', 'pipe'],
      timeout: 120_000,
    });
  } catch (error) {
    const failure = error as { stdout?: Buffer | string; stderr?: Buffer | string; message?: string };
    const stdout = failure.stdout?.toString() ?? '';
    const stderr = failure.stderr?.toString() ?? '';
    throw new Error(`wp-env factory "${factory}" failed.\n${stdout}\n${stderr}\n${failure.message ?? ''}`);
  }

  if (!existsSync(resultPath)) {
    throw new Error(`Factory "${factory}" did not write a result. Is the .e2e mapping present in .wp-env.json?`);
  }

  try {
    return JSON.parse(readFileSync(resultPath, 'utf8')) as T;
  } finally {
    rmSync(resultPath, { force: true });
  }
}

export interface CourseFactoryResult {
  id?: number;
  courseId: number;
  title: string;
  slug?: string;
  courseSlug: string;
  url: string;
  courseUrl: string;
  topicId: number;
  lessonId: number;
  lessonUrl: string;
  quizId?: number;
  quizUrl?: string;
  assignmentId?: number;
  assignmentUrl?: string;
  planId?: number;
  priceType?: 'free' | 'paid';
  price?: number;
}

export interface CourseFactoryInput {
  title?: string;
  price?: number;
  price_type?: 'free' | 'paid';
  instructor?: string;
  lessonTitle?: string;
  lessonContent?: string;
  status?: 'publish' | 'draft';
  is_public?: boolean;
  password?: string;
  product_id?: number;
  prerequisites?: number[];
  selling_option?: 'one_time' | 'subscription' | 'both';
  content_drip?: Record<string, unknown>;
  content_drip_type?: string;
  subscription_plan?: boolean | Record<string, unknown>;
  add_quiz?: boolean;
  quiz_title?: string;
  add_assignment?: boolean;
  assignment_title?: string;
}

export function createCourse(input: CourseFactoryInput = {}): CourseFactoryResult {
  return runFactory<CourseFactoryResult>('course', input as FactoryInput);
}

export function enrollStudent(input: {
  courseId: number;
  user?: string;
  username?: string;
  status?: 'completed' | 'pending';
}): {
  enrollmentId: number;
  userId: number;
  courseId: number;
} {
  return runFactory('enrollment', {
    ...input,
    user: input.user ?? input.username ?? 'student',
  });
}

export interface WcProductFactoryResult {
  productId: number;
  title: string;
  price: number;
}

export function createWcProduct(input: { title?: string; price?: number } = {}): WcProductFactoryResult {
  return runFactory<WcProductFactoryResult>('wc_product', input as FactoryInput);
}

export function setTutorOption(key: string, value: unknown): { success: boolean; previous_value: unknown } {
  return runFactory('set_option', { option_name: 'tutor_option', key, value });
}

export function enableAddon(name: string): { success: boolean } {
  return runFactory('enable_addon', { name });
}
