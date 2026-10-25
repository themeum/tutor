import type { Reporter, TestCase, TestResult, TestStep } from '@playwright/test/reporter';

const isStep = (step: TestStep) => step.category === 'pw:api' || step.category === 'expect';

/** Print the current test and its top-level action as soon as it starts. */
export default class ProgressReporter implements Reporter {
  onTestBegin = (test: TestCase): void => {
    process.stdout.write(`\n▶ ${test.titlePath().filter(Boolean).join(' › ')}\n`);
  };

  onStepBegin = (_test: TestCase, _result: TestResult, step: TestStep): void => {
    if (!isStep(step) || (step.parent && isStep(step.parent))) {
      return;
    }
    process.stdout.write(`  · ${step.title}\n`);
  };

  onTestEnd = (test: TestCase, result: TestResult): void => {
    const mark = result.status === 'passed' ? '✓' : '✗';
    process.stdout.write(`${mark} ${test.title} (${result.duration}ms)\n`);
  };
}
