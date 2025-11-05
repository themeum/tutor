import { type accordionMeta } from '@Core/components/accordion';
import { type buttonMeta } from '@Core/components/button';
import { type fileUploaderMeta } from '@Core/components/file-uploader';
import { type iconMeta } from '@Core/components/icon';
import { type popoverMeta } from '@Core/components/popover';
import { type tabsMeta } from '@Core/components/tabs';
import { type selectDropdownMeta } from '@Core/components/select-dropdown';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export interface AlpineComponentMeta<TProps = any> {
  name: string;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  component: (props: TProps) => Record<string, any>;
  global?: boolean;
}

export interface ServiceMeta<T = unknown> {
  name: string;
  instance: T;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type ExtractComponent<T extends AlpineComponentMeta<any>> = T['component'];

export interface TutorCore {
  button: ExtractComponent<typeof buttonMeta>;
  fileUploader: ExtractComponent<typeof fileUploaderMeta>;
  tabs: ExtractComponent<typeof tabsMeta>;
  icon: ExtractComponent<typeof iconMeta>;
  selectDropdown: ExtractComponent<typeof selectDropdownMeta>;
  popover: ExtractComponent<typeof popoverMeta>;
  accordion: ExtractComponent<typeof accordionMeta>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
