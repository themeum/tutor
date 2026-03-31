import { type AlpineComponentMeta, type ServiceMeta, type TutorCore } from '@Core/ts/types';
import { makeFirstCharacterUpperCase } from '@TutorShared/utils/util';
import { type Alpine } from 'alpinejs';

interface RegisterAllOptions {
  components?: AlpineComponentMeta[];
  services?: ServiceMeta[];
}

type RegistryType = 'component' | 'service';

interface GetOptions {
  name: string;
  type: RegistryType;
}

interface RegisterOptions {
  type: RegistryType;
  meta: AlpineComponentMeta | ServiceMeta;
}

class Registry {
  private components = new Map<string, AlpineComponentMeta>();
  private services = new Map<string, ServiceMeta>();

  register({ type, meta }: RegisterOptions): void {
    if (type === 'component') {
      const componentMeta = meta as AlpineComponentMeta;
      if (!this.components.has(componentMeta.name)) {
        this.components.set(componentMeta.name, componentMeta);
      }
    } else {
      const serviceMeta = meta as ServiceMeta;
      if (!this.services.has(serviceMeta.name)) {
        this.services.set(serviceMeta.name, serviceMeta);
        this.exposeToWindow({ type: 'service', items: [serviceMeta] });
      }
    }
  }

  registerAll({ components = [], services = [] }: RegisterAllOptions): void {
    for (const component of components) {
      this.register({ type: 'component', meta: component });
    }
    for (const service of services) {
      this.register({ type: 'service', meta: service });
    }
  }

  get<T = unknown>({ name, type }: GetOptions): AlpineComponentMeta | T | undefined {
    const map = type === 'component' ? this.components : this.services;
    const item = map.get(name);
    return type === 'service' ? ((item as ServiceMeta)?.instance as T) : (item as AlpineComponentMeta);
  }

  has({ name, type }: GetOptions): boolean {
    return type === 'component' ? this.components.has(name) : this.services.has(name);
  }

  private exposeToWindow({ type, items }: { type: RegistryType; items: (AlpineComponentMeta | ServiceMeta)[] }): void {
    if (typeof window === 'undefined') return;

    const TutorCore: TutorCore = window.TutorCore || {};

    for (const meta of items) {
      if (type === 'service') {
        TutorCore[meta.name] = (meta as ServiceMeta).instance;
        continue;
      }

      if ((meta as AlpineComponentMeta).global) {
        TutorCore[meta.name] = (meta as AlpineComponentMeta).component;
      }
    }

    window.TutorCore = TutorCore;
  }

  exposeComponents(componentNames?: string[]): void {
    const components = componentNames
      ? Array.from(this.components.values()).filter((m) => componentNames.includes(m.name))
      : Array.from(this.components.values());

    this.exposeToWindow({ type: 'component', items: components });
  }

  initWithAlpine(Alpine: Alpine): void {
    for (const meta of Array.from(this.components.values())) {
      Alpine.data(`tutor${makeFirstCharacterUpperCase(meta.name)}`, meta.component);
    }

    this.exposeToWindow({
      type: 'component',
      items: Array.from(this.components.values()),
    });
  }
}

export const TutorComponentRegistry = new Registry();
