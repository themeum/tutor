import { type accordionMeta } from '@Core/ts/components/accordion';
import { type buttonMeta } from '@Core/ts/components/button';
import { type fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { type formMeta } from '@Core/ts/components/form';
import { type iconMeta } from '@Core/ts/components/icon';
import { type popoverMeta } from '@Core/ts/components/popover';
import { type selectDropdownMeta } from '@Core/ts/components/select-dropdown';
import { type stepperDropdownMeta } from '@Core/ts/components/stepper-dropdown';
import { type tabsMeta } from '@Core/ts/components/tabs';

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
  form: ExtractComponent<typeof formMeta>;
  stepperDropdown: ExtractComponent<typeof stepperDropdownMeta>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
