import type { AlpineComponentMeta } from '@Core/ts/types';

interface QuizSummarySidebarConfig {
  firstQuestionId?: string | number;
}

const HASH_PATTERN = /^#question-(\d+)$/;

const quizSummarySidebar = (config: QuizSummarySidebarConfig = {}) => ({
  activeQuestionId: String(config.firstQuestionId ?? ''),
  $el: null as HTMLElement | null,

  init() {
    const hashQuestionId = this.getQuestionIdFromHash(window.location.hash);

    if (hashQuestionId && this.hasQuestionItem(hashQuestionId)) {
      this.activeQuestionId = hashQuestionId;
      window.requestAnimationFrame(() => this.scrollToQuestionAnswer(hashQuestionId));
    }
  },

  getQuestionIdFromHash(hash: string): string | null {
    const hashMatch = hash.match(HASH_PATTERN);
    return hashMatch ? hashMatch[1] : null;
  },

  hasQuestionItem(questionId: string): boolean {
    if (!questionId || !this.$el) {
      return false;
    }

    return !!this.$el.querySelector(`[data-question-id="${questionId}"]`);
  },

  setActiveQuestion(questionId: string | number) {
    const resolvedId = String(questionId || '');

    if (!resolvedId) {
      return;
    }

    this.activeQuestionId = resolvedId;
    history.replaceState(null, '', `#question-${resolvedId}`);
    this.scrollToQuestionAnswer(resolvedId);
  },

  scrollToQuestionAnswer(questionId: string | number) {
    const resolvedId = String(questionId || '');

    if (!resolvedId) {
      return;
    }

    const answerElement =
      document.getElementById(`question-${resolvedId}`) ||
      document.querySelector(`[data-question-id="question-${resolvedId}"]`) ||
      document.querySelector(`[data-question-id="${resolvedId}"]`);

    if (answerElement instanceof HTMLElement) {
      answerElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  },
});

export const quizSummarySidebarMeta: AlpineComponentMeta = {
  name: 'quizSummarySidebar',
  component: quizSummarySidebar,
};
