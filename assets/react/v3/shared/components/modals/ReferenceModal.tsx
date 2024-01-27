import Button, { ButtonVariant } from '@Atoms/Button';
import FormInput from '@Components/fields/FormInput';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import React from 'react';
import { Controller } from 'react-hook-form';

import { ModalProps } from './Modal';
import ModalWrapper from './ModalWrapper';

interface ReferenceModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

// @TODO: will be removed later
const ReferenceModal = ({ closeModal, title }: ReferenceModalProps) => {
  const form = useFormWithGlobalError<{ url: string }>();
  return (
    <ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={{ width: '620px' }}>
        <div css={{ padding: '20px' }}>
          <Controller
            control={form.control}
            name="url"
            render={(props) => <FormInput {...props} label="Images, YouTube, or Vimeo URL" placeholder="https://" />}
          />
        </div>
        <hr css={{ padding: 0, margin: 0 }} />
        <div css={{ display: 'flex', padding: '16px', justifyContent: 'end', gap: 8 }}>
          <Button variant={ButtonVariant.secondary} onClick={() => closeModal({ action: 'CLOSE' })}>
            Close
          </Button>
          <Button variant={ButtonVariant.primary} onClick={() => closeModal({ action: 'CONFIRM' })}>
            Add
          </Button>
        </div>
      </div>
    </ModalWrapper>
  );
};

export default ReferenceModal;
