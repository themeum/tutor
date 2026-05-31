import { type accordionMeta } from '@Core/ts/components/accordion';
import { type buttonMeta } from '@Core/ts/components/button';
import { type fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { type iconMeta } from '@Core/ts/components/icon';
import { type popoverMeta } from '@Core/ts/components/popover';
import { type selectDropdownMeta } from '@Core/ts/components/select-dropdown';
import { type tabsMeta } from '@Core/ts/components/tabs';
import { type timeInputMeta } from '@Core/ts/components/time-input';

import { type FormService } from '@Core/ts/services/Form';
import { type ModalService } from '@Core/ts/services/Modal';
import { type QueryService } from '@Core/ts/services/Query';
import { type ToastService } from '@Core/ts/services/Toast';
import { type WPMediaService } from '../services/WPMedia';

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

export interface AjaxResponse<T = unknown> {
  status_code: number;
  success: boolean;
  message: string;
  data?: T;
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
  form: FormService;
  timeInput: ExtractComponent<typeof timeInputMeta>;
  toast: ToastService;
  query: QueryService;
  modal: ModalService;
  wpMedia: WPMediaService;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
