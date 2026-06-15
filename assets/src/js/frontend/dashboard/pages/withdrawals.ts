import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';
import { __ } from '@wordpress/i18n';

interface WithdrawalsFormProps {
  amount: string;
}

const withdrawals = () => {
  const { query, form, toast, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    query,
    form,
    toast,
    $el: null as HTMLElement | null,
    withdrawalRequestMutation: null as MutationState<AjaxResponse<string>> | null,

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
        (payload: Record<string, string>) => wpPost(endpoints.MAKE_AN_WITHDRAW, payload),
        {
          onSuccess: (data) => {
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
