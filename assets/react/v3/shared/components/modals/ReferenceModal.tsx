import FormInput from '@Components/fields/FormInput';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { Controller } from 'react-hook-form';

import type { ModalProps } from './Modal';
import ModalWrapper from './ModalWrapper';

interface ReferenceModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

// @TODO: will be removed later
const ReferenceModal = ({ closeModal, icon, title, subtitle, actions }: ReferenceModalProps) => {
  const form = useFormWithGlobalError<{ url: string }>();
  return (
    <ModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      icon={icon}
      title={title}
      subtitle={subtitle}
      actions={actions}
    >
      <div css={{ width: '1472px' }}>
        <div css={{ padding: '20px' }}>
          <Controller
            control={form.control}
            name="url"
            render={(props) => <FormInput {...props} label="Images, YouTube, or Vimeo URL" placeholder="https://" />}
          />
        </div>
        <hr css={{ padding: 0, margin: 0 }} />
      </div>
    </ModalWrapper>
  );
};

export default ReferenceModal;
