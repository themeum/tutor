import type { AlpineComponentMeta } from '@Core/ts/types';

interface QuizSummarySidebarConfig {
  firstQuestionId?: string | number;
}

const QUESTION_ID_PREFIX = 'question-';
const SUMMARY_HEADER_SELECTOR = '.tutor-quiz-summary-header';
const QUESTION_ID_FALLBACK_SELECTOR = '[data-question-id="%s"]';
const QUESTION_ID_PREFIX_FALLBACK_SELECTOR = `[data-question-id="${QUESTION_ID_PREFIX}%s"]`;
const SIDEBAR_ITEM_SELECTOR = '[data-question-id="%s"]';
const HASH_PATTERN = new RegExp(`^#${QUESTION_ID_PREFIX}(\\d+)$`);
const QUESTION_SCROLL_GAP = 16;

const quizSummarySidebar = (config: QuizSummarySidebarConfig = {}) => ({
  activeQuestionId: String(config.firstQuestionId ?? ''),
  $el: null as HTMLElement | null,

  init() {
    const hashQuestionId = this.getQuestionIdFromHash(window.location.hash);

    if (hashQuestionId && this.hasQuestionItem(hashQuestionId)) {
      this.activeQuestionId = hashQuestionId;
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

    return !!this.$el.querySelector(SIDEBAR_ITEM_SELECTOR.replace('%s', questionId));
  },

  setActiveQuestion(questionId: string | number) {
    const resolvedId = String(questionId || '');

    if (!resolvedId) {
      return;
    }

    this.activeQuestionId = resolvedId;
    history.replaceState(null, '', `#${QUESTION_ID_PREFIX}${resolvedId}`);
    this.scrollToQuestionAnswer(resolvedId);
  },

  scrollToQuestionAnswer(questionId: string | number) {
    const resolvedId = String(questionId || '');

    if (!resolvedId) {
      return;
    }

    const answerElement =
      document.getElementById(`${QUESTION_ID_PREFIX}${resolvedId}`) ||
      document.querySelector(QUESTION_ID_PREFIX_FALLBACK_SELECTOR.replace('%s', resolvedId)) ||
      document.querySelector(QUESTION_ID_FALLBACK_SELECTOR.replace('%s', resolvedId));

    if (answerElement instanceof HTMLElement) {
      const summaryHeader = document.querySelector(SUMMARY_HEADER_SELECTOR);
      const headerOffset =
        summaryHeader instanceof HTMLElement
          ? summaryHeader.getBoundingClientRect().top + summaryHeader.offsetHeight
          : 0;
      const scrollTop = answerElement.getBoundingClientRect().top + window.scrollY - headerOffset - QUESTION_SCROLL_GAP;

      window.scrollTo({
        top: Math.max(0, scrollTop),
        behavior: 'smooth',
      });
    }
  },
});

export const quizSummarySidebarMeta: AlpineComponentMeta = {
  name: 'quizSummarySidebar',
  component: quizSummarySidebar,
};
