import { __ } from '@wordpress/i18n';
import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';

interface WithdrawalsFormProps {
  amount: string;
}

const withdrawals = () => {
  const query = window.TutorCore.query;
  const form = window.TutorCore.form;
  const toast = window.TutorCore.toast;

  return {
    query,
    form,
    toast,
    $el: null as HTMLElement | null,
    withdrawalRequestMutation: null as MutationState<TutorMutationResponse<string>> | null,

    async handleWithdrawalFormSubmit(data: WithdrawalsFormProps, formId: string) {
      const payload: Record<string, string> = { amount: data.amount };
      await this.withdrawalRequestMutation?.mutate(payload);
      this.form.reset(formId);
    },

    init() {
      if (!this.$el) {
        return;
      }

      this.handleWithdrawalFormSubmit = this.handleWithdrawalFormSubmit.bind(this);

      this.withdrawalRequestMutation = this.query.useMutation(
        (payload) => wpAjaxInstance.post(endpoints.MAKE_AN_WITHDRAW, payload),
        {
          onSuccess: (data: TutorMutationResponse<string>) => {
            this.toast.success(data?.message ?? __('Withdrawal request submitted successfully', 'tutor'));
            window.location.reload();
          },
          onError: (error: Error) => {
            this.toast.error(convertToErrorMessage(error));
          },
        },
      );
    },
  };
};

const withdrawalsMeta = {
  name: 'withdrawals',
  component: withdrawals,
};

export const initializeWithdrawals = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: withdrawalsMeta,
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
