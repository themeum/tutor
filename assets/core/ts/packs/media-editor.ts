import { type TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { copyToClipboardMeta } from '@Core/ts/components/copy-to-clipboard';
import { fileUploaderMeta } from '@Core/ts/components/file-uploader';
import { wpEditorMeta } from '@Core/ts/components/wp-editor';
import { wpMediaServiceMeta } from '@Core/ts/services/WPMedia';

export const registerCoreMediaEditorPack = (registry: typeof TutorComponentRegistry): void => {
  registry.registerAll({
    components: [fileUploaderMeta, wpEditorMeta, copyToClipboardMeta],
    services: [wpMediaServiceMeta],
  });
};
