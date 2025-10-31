// eslint-disable-next-line @typescript-eslint/no-explicit-any
export interface AlpineComponentMeta<TProps = any> {
  name: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  component: (props?: TProps) => Record<string, any>;
  global?: boolean;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type ExtractComponent<T extends AlpineComponentMeta<any>> = T['component'];

import { type buttonMeta } from '@Core/components/button';
import { type accordionMeta } from '@Core/components/accordion';

export interface TutorCore {
  button: ExtractComponent<typeof buttonMeta>;
  accordion: ExtractComponent<typeof accordionMeta>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
