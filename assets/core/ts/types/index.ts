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
import { type fileUploaderMeta } from '@Core/components/file-uploader';

export interface TutorCore {
  button: ExtractComponent<typeof buttonMeta>;
  fileUploader: ExtractComponent<typeof fileUploaderMeta>;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  [key: string]: any;
}
