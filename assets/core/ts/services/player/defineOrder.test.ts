import { describe, expect, it, vi } from 'vitest';

// Records the host element's registration state at the moment the skin module is evaluated.
const state = vi.hoisted(() => ({ hostDefinedWhenSkinLoads: null as boolean | null }));

vi.mock('@videojs/html/video/skin', () => {
  state.hostDefinedWhenSkinLoads = Boolean(customElements.get('tutor-video-player'));
  return {};
});

describe('player engine define order', () => {
  it('defines tutor-video-player before the skin module evaluates', async () => {
    await import('./index');

    // The skin's controls resolve the player store once, at upgrade; a host class that
    // registers after the skin leaves every control unbound.
    expect(state.hostDefinedWhenSkinLoads).toBe(true);
  });
});
