// Quiz Interface Page
// Handles quiz taking, question navigation, and submission

import { showNotification } from '@FrontendServices/notifications';
import { LearningAPI } from '../services/learning-api';

export const initializeQuizInterface = () => {
  // Initialize quiz state
  setupQuizState();
  
  // Initialize question navigation
  setupQuestionNavigation();
  
  // Initialize timer
  setupQuizTimer();
  
  // Initialize auto-save
  setupAutoSave();
  
  // Initialize submission
  setupQuizSubmission();
};

interface QuizState {
  quizId: number;
  currentQuestion: number;
  totalQuestions: number;
  answers: Record<number, any>;
  timeLimit?: number;
  startTime: number;
  isSubmitted: boolean;
}

let quizState: QuizState;

const setupQuizState = () => {
  const quizData = document.body.dataset;
  
  quizState = {
    quizId: parseInt(quizData.quizId || '0'),
    currentQuestion: 1,
    totalQuestions: parseInt(quizData.totalQuestions || '0'),
    answers: {},
    timeLimit: quizData.timeLimit ? parseInt(quizData.timeLimit) * 60 : undefined, // Convert to seconds
    startTime: Date.now(),
    isSubmitted: false
  };
  
  // Load saved answers if any
  loadSavedAnswers();
  
  // Update UI
  updateQuizProgress();
  updateNavigationButtons();
};

const setupQuestionNavigation = () => {
  // Previous/Next buttons
  const prevBtn = document.querySelector('.quiz-prev-btn');
  const nextBtn = document.querySelector('.quiz-next-btn');
  
  if (prevBtn) {
    prevBtn.addEventListener('click', () => navigateToQuestion(quizState.currentQuestion - 1));
  }
  
  if (nextBtn) {
    nextBtn.addEventListener('click', () => navigateToQuestion(quizState.currentQuestion + 1));
  }
  
  // Question number buttons
  const questionNumbers = document.querySelectorAll('.question-number-btn');
  questionNumbers.forEach((btn, index) => {
    btn.addEventListener('click', () => navigateToQuestion(index + 1));
  });
  
  // Keyboard navigation
  document.addEventListener('keydown', (event) => {
    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
      return; // Don't handle navigation when typing
    }
    
    switch (event.key) {
      case 'ArrowLeft':
        event.preventDefault();
        navigateToQuestion(quizState.currentQuestion - 1);
        break;
      case 'ArrowRight':
        event.preventDefault();
        navigateToQuestion(quizState.currentQuestion + 1);
        break;
    }
  });
};

const setupQuizTimer = () => {
  if (!quizState.timeLimit) return;
  
  const timerDisplay = document.querySelector('.quiz-timer');
  if (!timerDisplay) return;
  
  const updateTimer = () => {
    const elapsed = Math.floor((Date.now() - quizState.startTime) / 1000);
    const remaining = Math.max(0, quizState.timeLimit! - elapsed);
    
    if (remaining === 0) {
      // Time's up - auto submit
      handleTimeUp();
      return;
    }
    
    // Update display
    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    // Add warning classes
    if (remaining <= 300) { // 5 minutes
      timerDisplay.classList.add('warning');
    }
    if (remaining <= 60) { // 1 minute
      timerDisplay.classList.add('critical');
    }
  };
  
  // Update timer every second
  const timerInterval = setInterval(() => {
    if (quizState.isSubmitted) {
      clearInterval(timerInterval);
      return;
    }
    updateTimer();
  }, 1000);
  
  // Initial update
  updateTimer();
};

const setupAutoSave = () => {
  // Auto-save answers every 30 seconds
  setInterval(() => {
    if (!quizState.isSubmitted) {
      saveCurrentAnswers();
    }
  }, 30000);
  
  // Save on page unload
  window.addEventListener('beforeunload', (event) => {
    if (!quizState.isSubmitted && hasUnsavedChanges()) {
      saveCurrentAnswers();
      event.preventDefault();
      event.returnValue = 'You have unsaved quiz answers. Are you sure you want to leave?';
    }
  });
  
  // Save when answer changes
  document.addEventListener('change', (event) => {
    const target = event.target as HTMLInputElement;
    if (target.matches('.quiz-answer-input')) {
      saveAnswerForCurrentQuestion();
      updateQuestionStatus();
    }
  });
};

const setupQuizSubmission = () => {
  const submitBtn = document.querySelector('.quiz-submit-btn');
  if (!submitBtn) return;
  
  submitBtn.addEventListener('click', handleQuizSubmission);
  
  // Review mode toggle
  const reviewBtn = document.querySelector('.quiz-review-btn');
  if (reviewBtn) {
    reviewBtn.addEventListener('click', enterReviewMode);
  }
};

const navigateToQuestion = (questionNumber: number) => {
  if (questionNumber < 1 || questionNumber > quizState.totalQuestions) {
    return;
  }
  
  // Save current answer before navigating
  saveAnswerForCurrentQuestion();
  
  // Hide current question
  const currentQuestionEl = document.querySelector(`.quiz-question[data-question="${quizState.currentQuestion}"]`);
  if (currentQuestionEl) {
    currentQuestionEl.classList.remove('active');
  }
  
  // Show target question
  const targetQuestionEl = document.querySelector(`.quiz-question[data-question="${questionNumber}"]`);
  if (targetQuestionEl) {
    targetQuestionEl.classList.add('active');
    
    // Focus first input
    const firstInput = targetQuestionEl.querySelector('input, textarea, select') as HTMLElement;
    if (firstInput) {
      firstInput.focus();
    }
  }
  
  // Update state
  quizState.currentQuestion = questionNumber;
  
  // Update UI
  updateQuizProgress();
  updateNavigationButtons();
  updateQuestionNumbers();
  
  // Load saved answer for this question
  loadAnswerForCurrentQuestion();
};

const saveAnswerForCurrentQuestion = () => {
  const questionEl = document.querySelector(`.quiz-question[data-question="${quizState.currentQuestion}"]`);
  if (!questionEl) return;
  
  const questionId = parseInt(questionEl.dataset.questionId || '0');
  const questionType = questionEl.dataset.questionType;
  
  let answer: any = null;
  
  switch (questionType) {
    case 'multiple_choice':
    case 'true_false':
      const selectedRadio = questionEl.querySelector('input[type="radio"]:checked') as HTMLInputElement;
      answer = selectedRadio ? selectedRadio.value : null;
      break;
      
    case 'multiple_select':
      const selectedCheckboxes = questionEl.querySelectorAll('input[type="checkbox"]:checked');
      answer = Array.from(selectedCheckboxes).map(cb => (cb as HTMLInputElement).value);
      break;
      
    case 'short_answer':
    case 'essay':
      const textInput = questionEl.querySelector('input[type="text"], textarea') as HTMLInputElement;
      answer = textInput ? textInput.value.trim() : null;
      break;
      
    case 'fill_in_blank':
      const blanks = questionEl.querySelectorAll('.fill-blank-input');
      answer = Array.from(blanks).map(blank => (blank as HTMLInputElement).value.trim());
      break;
  }
  
  if (answer !== null && answer !== '' && !(Array.isArray(answer) && answer.length === 0)) {
    quizState.answers[questionId] = answer;
  } else {
    delete quizState.answers[questionId];
  }
};

const loadAnswerForCurrentQuestion = () => {
  const questionEl = document.querySelector(`.quiz-question[data-question="${quizState.currentQuestion}"]`);
  if (!questionEl) return;
  
  const questionId = parseInt(questionEl.dataset.questionId || '0');
  const questionType = questionEl.dataset.questionType;
  const savedAnswer = quizState.answers[questionId];
  
  if (!savedAnswer) return;
  
  switch (questionType) {
    case 'multiple_choice':
    case 'true_false':
      const radioToCheck = questionEl.querySelector(`input[type="radio"][value="${savedAnswer}"]`) as HTMLInputElement;
      if (radioToCheck) {
        radioToCheck.checked = true;
      }
      break;
      
    case 'multiple_select':
      if (Array.isArray(savedAnswer)) {
        savedAnswer.forEach(value => {
          const checkboxToCheck = questionEl.querySelector(`input[type="checkbox"][value="${value}"]`) as HTMLInputElement;
          if (checkboxToCheck) {
            checkboxToCheck.checked = true;
          }
        });
      }
      break;
      
    case 'short_answer':
    case 'essay':
      const textInput = questionEl.querySelector('input[type="text"], textarea') as HTMLInputElement;
      if (textInput) {
        textInput.value = savedAnswer;
      }
      break;
      
    case 'fill_in_blank':
      if (Array.isArray(savedAnswer)) {
        const blanks = questionEl.querySelectorAll('.fill-blank-input');
        blanks.forEach((blank, index) => {
          if (savedAnswer[index]) {
            (blank as HTMLInputElement).value = savedAnswer[index];
          }
        });
      }
      break;
  }
};

const updateQuizProgress = () => {
  const progressBar = document.querySelector('.quiz-progress-bar') as HTMLElement;
  const progressText = document.querySelector('.quiz-progress-text');
  
  if (progressBar) {
    const progress = (quizState.currentQuestion / quizState.totalQuestions) * 100;
    progressBar.style.width = `${progress}%`;
  }
  
  if (progressText) {
    progressText.textContent = `Question ${quizState.currentQuestion} of ${quizState.totalQuestions}`;
  }
};

const updateNavigationButtons = () => {
  const prevBtn = document.querySelector('.quiz-prev-btn') as HTMLButtonElement;
  const nextBtn = document.querySelector('.quiz-next-btn') as HTMLButtonElement;
  
  if (prevBtn) {
    prevBtn.disabled = quizState.currentQuestion === 1;
  }
  
  if (nextBtn) {
    nextBtn.disabled = quizState.currentQuestion === quizState.totalQuestions;
    nextBtn.textContent = quizState.currentQuestion === quizState.totalQuestions ? 'Review' : 'Next';
  }
};

const updateQuestionNumbers = () => {
  const questionNumbers = document.querySelectorAll('.question-number-btn');
  
  questionNumbers.forEach((btn, index) => {
    const questionNumber = index + 1;
    const questionId = getQuestionIdByNumber(questionNumber);
    
    btn.classList.remove('current', 'answered', 'unanswered');
    
    if (questionNumber === quizState.currentQuestion) {
      btn.classList.add('current');
    } else if (quizState.answers[questionId]) {
      btn.classList.add('answered');
    } else {
      btn.classList.add('unanswered');
    }
  });
};

const updateQuestionStatus = () => {
  updateQuestionNumbers();
  
  // Update submit button state
  const submitBtn = document.querySelector('.quiz-submit-btn') as HTMLButtonElement;
  if (submitBtn) {
    const answeredCount = Object.keys(quizState.answers).length;
    const allAnswered = answeredCount === quizState.totalQuestions;
    
    submitBtn.disabled = answeredCount === 0;
    submitBtn.textContent = allAnswered ? 'Submit Quiz' : `Submit (${answeredCount}/${quizState.totalQuestions} answered)`;
  }
};

const handleQuizSubmission = async () => {
  // Save current answer
  saveAnswerForCurrentQuestion();
  
  // Validate required questions
  const unansweredRequired = getUnansweredRequiredQuestions();
  if (unansweredRequired.length > 0) {
    const proceed = confirm(
      `You have ${unansweredRequired.length} unanswered required questions. ` +
      'Do you want to review them before submitting?'
    );
    
    if (!proceed) {
      // Navigate to first unanswered question
      navigateToQuestion(unansweredRequired[0]);
      return;
    }
  }
  
  // Show confirmation dialog
  const confirmed = confirm(
    'Are you sure you want to submit your quiz? ' +
    'You will not be able to change your answers after submission.'
  );
  
  if (!confirmed) return;
  
  try {
    // Show loading state
    const submitBtn = document.querySelector('.quiz-submit-btn') as HTMLButtonElement;
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Calculate time taken
    const timeTaken = Math.floor((Date.now() - quizState.startTime) / 1000);
    
    // Submit quiz
    const result = await LearningAPI.submitQuiz(quizState.quizId, {
      answers: quizState.answers,
      time_taken: timeTaken
    });
    
    // Mark as submitted
    quizState.isSubmitted = true;
    
    // Clear saved answers
    clearSavedAnswers();
    
    // Show results
    showQuizResults(result);
    
    showNotification('Quiz submitted successfully!', 'success');
    
  } catch (error) {
    console.error('Failed to submit quiz:', error);
    showNotification('Failed to submit quiz. Please try again.', 'error');
    
    // Reset button state
    const submitBtn = document.querySelector('.quiz-submit-btn') as HTMLButtonElement;
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  }
};

const handleTimeUp = () => {
  showNotification('Time is up! Your quiz will be submitted automatically.', 'warning');
  
  setTimeout(() => {
    handleQuizSubmission();
  }, 3000);
};

const enterReviewMode = () => {
  document.body.classList.add('quiz-review-mode');
  
  // Show all questions
  const questions = document.querySelectorAll('.quiz-question');
  questions.forEach(question => {
    question.classList.add('visible');
  });
  
  // Hide navigation
  const navigation = document.querySelector('.quiz-navigation');
  if (navigation) {
    navigation.style.display = 'none';
  }
  
  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const showQuizResults = (result: any) => {
  // Hide quiz interface
  const quizInterface = document.querySelector('.quiz-interface');
  if (quizInterface) {
    quizInterface.style.display = 'none';
  }
  
  // Show results
  const resultsContainer = document.querySelector('.quiz-results');
  if (resultsContainer) {
    resultsContainer.innerHTML = `
      <div class="quiz-results-content">
        <div class="results-header">
          <h2>Quiz Complete!</h2>
          <div class="score-display">
            <div class="score-circle">
              <span class="score-percentage">${result.percentage}%</span>
            </div>
            <div class="score-details">
              <p>Score: ${result.score} out of ${result.total_points}</p>
              <p>Correct: ${result.correct_answers} out of ${result.total_questions}</p>
              <p class="pass-status ${result.passed ? 'passed' : 'failed'}">
                ${result.passed ? 'Passed' : 'Failed'}
              </p>
            </div>
          </div>
        </div>
        
        <div class="results-actions">
          <button class="btn btn-primary continue-btn">Continue Learning</button>
          ${result.can_retake ? '<button class="btn btn-secondary retake-btn">Retake Quiz</button>' : ''}
          <button class="btn btn-outline review-answers-btn">Review Answers</button>
        </div>
        
        ${result.feedback ? `<div class="quiz-feedback">${result.feedback}</div>` : ''}
      </div>
    `;
    
    resultsContainer.style.display = 'block';
    
    // Add event listeners
    const continueBtn = resultsContainer.querySelector('.continue-btn');
    if (continueBtn) {
      continueBtn.addEventListener('click', () => {
        window.location.href = result.next_lesson_url || '/dashboard/courses';
      });
    }
    
    const retakeBtn = resultsContainer.querySelector('.retake-btn');
    if (retakeBtn) {
      retakeBtn.addEventListener('click', () => {
        window.location.reload();
      });
    }
    
    const reviewBtn = resultsContainer.querySelector('.review-answers-btn');
    if (reviewBtn) {
      reviewBtn.addEventListener('click', () => {
        showAnswerReview(result);
      });
    }
  }
};

const showAnswerReview = (result: any) => {
  // Implementation for showing detailed answer review
  console.log('Showing answer review:', result);
};

// Utility functions
const saveCurrentAnswers = () => {
  const key = `quiz_${quizState.quizId}_answers`;
  localStorage.setItem(key, JSON.stringify({
    answers: quizState.answers,
    currentQuestion: quizState.currentQuestion,
    startTime: quizState.startTime
  }));
};

const loadSavedAnswers = () => {
  const key = `quiz_${quizState.quizId}_answers`;
  const saved = localStorage.getItem(key);
  
  if (saved) {
    try {
      const data = JSON.parse(saved);
      quizState.answers = data.answers || {};
      quizState.currentQuestion = data.currentQuestion || 1;
      quizState.startTime = data.startTime || Date.now();
    } catch (error) {
      console.error('Failed to load saved answers:', error);
    }
  }
};

const clearSavedAnswers = () => {
  const key = `quiz_${quizState.quizId}_answers`;
  localStorage.removeItem(key);
};

const hasUnsavedChanges = (): boolean => {
  return Object.keys(quizState.answers).length > 0;
};

const getUnansweredRequiredQuestions = (): number[] => {
  const unanswered: number[] = [];
  
  const requiredQuestions = document.querySelectorAll('.quiz-question[data-required="true"]');
  requiredQuestions.forEach((question, index) => {
    const questionId = parseInt(question.getAttribute('data-question-id') || '0');
    if (!quizState.answers[questionId]) {
      unanswered.push(index + 1); // Question numbers are 1-based
    }
  });
  
  return unanswered;
};

const getQuestionIdByNumber = (questionNumber: number): number => {
  const questionEl = document.querySelector(`.quiz-question[data-question="${questionNumber}"]`);
  return questionEl ? parseInt(questionEl.getAttribute('data-question-id') || '0') : 0;
};