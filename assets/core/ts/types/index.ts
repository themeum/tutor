import { type accordionMeta } from '@Core/ts/components/accordion';
import { type fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { type iconMeta } from '@Core/ts/components/icon';
import { type popoverMeta } from '@Core/ts/components/popover';
import { type tabsMeta } from '@Core/ts/components/tabs';
import { type timeInputMeta } from '@Core/ts/components/time-input';

import { type FormService } from '@Core/ts/services/Form';
import { type ModalService } from '@Core/ts/services/Modal';
import { type QueryService } from '@Core/ts/services/Query';
import { type ToastService } from '@Core/ts/services/toast/Toast';
import { type WPMediaService } from '@Core/ts/services/WPMedia';
import { type wpGet, type wpPost, type wpPostForm } from '@Core/ts/utils/api';
import { type createPriceFormatter, type formatPrice } from '@Core/ts/utils/currency';
import type endpoints from '@Core/ts/utils/endpoints';

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
  data: T;
}

export type LazyComponentLoader = () => Promise<AlpineComponentMeta>;

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type ExtractComponent<T extends AlpineComponentMeta<any>> = T['component'];

export interface TutorCore {
  fileUploader: ExtractComponent<typeof fileUploaderMeta>;
  tabs: ExtractComponent<typeof tabsMeta>;
  icon: ExtractComponent<typeof iconMeta>;
  popover: ExtractComponent<typeof popoverMeta>;
  accordion: ExtractComponent<typeof accordionMeta>;
  form: FormService;
  timeInput: ExtractComponent<typeof timeInputMeta>;
  toast: ToastService;
  query: QueryService;
  modal: ModalService;
  wpMedia: WPMediaService;
  api: {
    wpPost: typeof wpPost;
    wpPostForm: typeof wpPostForm;
    wpGet: typeof wpGet;
  };
  endpoints: typeof endpoints;
  currency: {
    createPriceFormatter: typeof createPriceFormatter;
    formatPrice: typeof formatPrice;
  };
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
