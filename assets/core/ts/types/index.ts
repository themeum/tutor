import { type buttonMeta } from '@Core/components/button';
import { type iconMeta } from '@Core/components/icon';
import { type tabsMeta } from '@Core/components/tabs';
import { type selectDropdownMeta } from '@Core/components/select-dropdown';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export interface AlpineComponentMeta<TProps = any> {
  name: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  component: (props: TProps) => Record<string, any>;
  global?: boolean;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type ExtractComponent<T extends AlpineComponentMeta<any>> = T['component'];

export interface TutorCore {
  button: ExtractComponent<typeof buttonMeta>;
  tabs: ExtractComponent<typeof tabsMeta>;
  icon: ExtractComponent<typeof iconMeta>;
  selectDropdown: ExtractComponent<typeof selectDropdownMeta>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
