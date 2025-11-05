import { type Alpine } from 'alpinejs';

import { type AlpineComponentMeta, type TutorCore } from '@Core/ts/types';
import { makeFirstCharacterUpperCase } from '@TutorShared/utils/util';

class Registry {
  private components = new Map<string, AlpineComponentMeta>();

  register(meta: AlpineComponentMeta): void {
    if (this.components.has(meta.name)) {
      return;
    }
    this.components.set(meta.name, meta);
  }

  registerAll(metas: AlpineComponentMeta[]): void {
    metas.forEach((meta) => this.register(meta));
  }

  get(name: string): AlpineComponentMeta | undefined {
    return this.components.get(name);
  }

  has(name: string): boolean {
    return this.components.has(name);
  }

  getAll(): string[] {
    return Array.from(this.components.keys());
  }

  /**
   * Expose components marked as global to window.TutorCore
   */
  private exposeGlobals(): void {
    if (typeof window === 'undefined') return;

    const TutorCore: TutorCore = window.TutorCore || {};

    this.components.forEach((meta) => {
      if (meta.global) {
        TutorCore[meta.name] = meta.component;
      }
    });

    window.TutorCore = TutorCore;
  }

  /**
   * Manually expose all or specific components to window.TutorCore
   */
  exposeToWindow(componentNames?: string[]): void {
    if (typeof window === 'undefined') return;

    const TutorCore: TutorCore = window.TutorCore || {};

    const componentsToExpose = componentNames
      ? Array.from(this.components.values()).filter((meta) => componentNames.includes(meta.name))
      : Array.from(this.components.values());

    componentsToExpose.forEach((meta) => {
      TutorCore[meta.name] = meta.component;
    });

    window.TutorCore = TutorCore;
  }

  initWithAlpine(Alpine: Alpine): void {
    this.components.forEach((meta) => {
      Alpine.data(`tutor${makeFirstCharacterUpperCase(meta.name)}`, meta.component);
    });

    this.exposeGlobals();
  }
}

export const TutorComponentRegistry = new Registry();
