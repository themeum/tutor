import { describe, expect, it, vi } from 'vitest';

describe('TutorComponentRegistry sharing', () => {
  it('reuses the instance published on window across separately loaded bundles', async () => {
    const first = (await import('@Core/ts/ComponentRegistry')).TutorComponentRegistry;

    expect(window.TutorComponentRegistry).toBe(first);

    vi.resetModules();
    const second = (await import('@Core/ts/ComponentRegistry')).TutorComponentRegistry;

    expect(second).toBe(first);
  });
});
