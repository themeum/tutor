import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { existsSync, mkdirSync, readFileSync, rmSync } from 'node:fs';
import path from 'node:path';

const pluginSlug = path.basename(process.cwd());
const factoryScript = `wp-content/plugins/${pluginSlug}/bin/e2e/factories.php`;
const resultsDir = path.join(process.cwd(), '.e2e');

export type FactoryInput = Record<string, unknown>;

export function runFactory<T>(factory: string, input: FactoryInput = {}, script = factoryScript): T {
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
    ? ['--config', process.env.E2E_WP_ENV_CONFIG, 'run', 'cli', '--', 'wp', 'eval-file', script, payload]
    : ['run', 'cli', '--', 'wp', 'eval-file', script, payload];

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
  membershipPlanId?: number;
  priceType?: 'free' | 'paid';
  price?: number;
}

export interface CourseFactoryInput {
  title?: string;
  author?: string;
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
  membership_plan?: boolean | Record<string, unknown>;
  add_quiz?: boolean;
  quiz_title?: string;
  add_assignment?: boolean;
  assignment_title?: string;
  lesson_meta?: Record<string, unknown>;
  course_meta?: Record<string, unknown>;
}

export function createCourse(input: CourseFactoryInput = {}): CourseFactoryResult {
  return runFactory<CourseFactoryResult>('course', input as FactoryInput);
}

export interface CourseState {
  courseId: number;
  title: string;
  content: string;
  status: string;
  postDate: string;
  postDateGmt: string;
  slug: string;
  url: string;
  author: { id: number; login: string; name: string } | null;
  password: string;
  priceType: string;
  price: string;
  salePrice: string;
  productId: string;
  sellingOption: string;
  subscriptionPlans: Array<{
    id: string;
    plan_name: string;
    regular_price: string;
    recurring_value: string;
    recurring_interval: string;
    recurring_limit: string;
    is_enabled: string;
  }>;
  isPublic: boolean;
  level: string;
  qna: string;
  settings: Record<string, unknown> | string;
  benefits: string;
  requirements: string;
  audience: string;
  materials: string;
  duration: { hours?: string; minutes?: string } | string;
  attachments: number[] | string;
  prerequisites: number[] | string;
  certificate: string;
  thumbnailId: number;
  thumbnailUrl: string | false;
  video: Record<string, unknown> | string;
  taxOnSingle: string;
  taxOnSubscription: string;
  comingSoon: boolean;
  curriculumPreview: boolean;
  comingSoonThumbnailId: number;
  categories: Array<{ id: number; name: string }>;
  tags: Array<{ id: number; name: string }>;
  contents: Array<{
    id: number;
    title: string;
    summary: string;
    children: Array<{
      id: number;
      type: string;
      title: string;
      content: string;
      url: string;
      meta: Record<string, unknown>;
    }>;
  }>;
}

export function getCourse(courseId: number): CourseState {
  return runFactory<CourseState>('get_course', { course_id: courseId });
}

export interface LessonFactoryResult {
  lessonId: number;
  lessonUrl: string;
  title: string;
}

export function createLesson(input: {
  course_id: number;
  topic_id: number;
  title?: string;
  content?: string;
  status?: 'publish' | 'draft';
  meta?: Record<string, unknown>;
}): LessonFactoryResult {
  return runFactory<LessonFactoryResult>('lesson', input as FactoryInput);
}

export function setPostMeta(input: {
  post_id: number;
  key: string;
  value?: unknown;
  add?: boolean;
  delete?: boolean;
}): { postId: number; key: string; previous: unknown; value: unknown } {
  return runFactory('post_meta', input as FactoryInput);
}

export function setUserMeta(input: { user: string; key: string; value?: unknown; add?: boolean; delete?: boolean }): {
  userId: number;
  key: string;
  previous: unknown;
  value: unknown;
} {
  return runFactory('user_meta', input as FactoryInput);
}

export function createUser(input: { user: string; role?: string; email?: string; name?: string; password?: string }): {
  userId: number;
  user: string;
  email: string;
  role: string;
  existing: boolean;
} {
  return runFactory('user', input as FactoryInput);
}

export function completeCourse(input: { course_id: number; user?: string }): {
  courseId: number;
  userId: number;
  completed: boolean;
} {
  return runFactory('course_complete', input as FactoryInput);
}

export function createNotification(input: {
  user?: string;
  type?: string;
  title?: string;
  content?: string;
  post_id?: number;
  topic_url?: string;
}): { notificationId: number; userId: number } {
  return runFactory('notification', input as FactoryInput);
}

export function deleteNotification(notificationId: number): { notificationId: number; deleted: boolean } {
  return runFactory('notification', { action: 'delete', notification_id: notificationId });
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

export function completeTutorOrder(orderId: number): {
  orderId: number;
  orderStatus: string;
  paymentStatus: string;
} {
  return runFactory('tutor_order_complete', { order_id: orderId });
}

export function completeWcOrder(orderId: number): { orderId: number; status: string } {
  return runFactory('wc_order_complete', { order_id: orderId });
}

export function clearWooCommerceCart(userId = 0): { success: boolean } {
  return runFactory('wc_clear_cart', { user_id: userId });
}

export function setOption(optionName: string, value: unknown): { success: boolean; previous_value: unknown } {
  return runFactory('set_option', { option_name: optionName, value });
}

export function setTutorOption(key: string, value: unknown): { success: boolean; previous_value: unknown } {
  return runFactory('set_option', { option_name: 'tutor_option', key, value });
}

export function enableAddon(name: string): { success: boolean } {
  return runFactory('enable_addon', { name });
}

export interface AddonStatesResult {
  config: Record<string, { is_enable?: number } & Record<string, unknown>>;
  states: Record<string, boolean>;
  count: number;
}

export function getAddonStates(): AddonStatesResult {
  return runFactory<AddonStatesResult>('get_addon_states');
}

export interface AddonStateResult {
  name: string;
  key: string;
  previous: number | null;
  enabled: 0 | 1;
  config: Record<string, { is_enable?: number }>;
  count: number;
}

export function setAddonState(name: string, enabled: boolean): AddonStateResult {
  return runFactory<AddonStateResult>('set_addon_state', { name, enabled });
}

export function setAddonBaseline(
  basenames: string[],
  enabled: boolean,
  except: string[] = [],
): { success: boolean; count: number } {
  return runFactory('set_addon_baseline', { basenames, enabled, except });
}

export interface SnapshotInput {
  postTypes?: string[];
  tables?: string[];
  commentTypes?: string[];
  options?: string[];
}

export interface SnapshotResult {
  postTypes: Record<string, number>;
  tables: Record<string, number | null>;
  commentTypes: Record<string, number>;
  options: Record<string, unknown>;
}

export function snapshot(input: SnapshotInput): SnapshotResult {
  return runFactory<SnapshotResult>('snapshot', input as FactoryInput);
}

export interface DebugLogResult {
  path: string;
  lines: string[];
  size: number;
}

export function getDebugLog(lines = 200): DebugLogResult {
  return runFactory<DebugLogResult>('get_debug_log', { lines });
}

export function clearDebugLog(): { success: boolean } {
  return runFactory('clear_debug_log');
}

export interface MailMessage {
  to: string[];
  subject: string;
  message: string;
  headers: string[];
  attachments: string[];
  time: number;
}

export function getMailLog(): { success: boolean; messages: MailMessage[] } {
  return runFactory('mail_log', { action: 'get' });
}

export function clearMailLog(): { success: boolean } {
  return runFactory('mail_log', { action: 'clear' });
}

export function setHttpStub(stubs: Record<string, unknown>): { success: boolean } {
  return runFactory('set_http_stub', { stubs });
}

export function getPlugins(): { active: string[]; installed: string[] } {
  return runFactory('get_plugins');
}

export function addCapability(user: string, cap: string): { success: boolean } {
  return runFactory('add_capability', { user, cap });
}

export function removeCapability(user: string, cap: string): { success: boolean } {
  return runFactory('remove_capability', { user, cap });
}

export function getCron(contains = ''): { hooks: Array<{ hook: string; timestamp: number; count: number }> } {
  return runFactory('get_cron', { contains });
}
