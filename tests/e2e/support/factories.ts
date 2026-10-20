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

  try {
    execFileSync('pnpm', ['exec', 'wp-env', 'run', 'cli', '--', 'wp', 'eval-file', factoryScript, payload], {
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
  id: number;
  courseId: number;
  title: string;
  slug: string;
  courseSlug: string;
  url: string;
  courseUrl: string;
  topicId: number;
  lessonId: number;
  lessonUrl: string;
  priceType?: 'free' | 'paid';
  price?: number;
}

export function createCourse(input: {
  title: string;
  price?: number;
  price_type?: 'free' | 'paid';
  instructor?: string;
  lessonTitle?: string;
  lessonContent?: string;
  status?: 'publish' | 'draft';
}): CourseFactoryResult {
  return runFactory<CourseFactoryResult>('course', input);
}

export function enrollStudent(input: { courseId: number; username: string; status?: 'completed' | 'pending' }): {
  enrollmentId: number;
  userId: number;
  courseId: number;
} {
  return runFactory('enrollment', input);
}
