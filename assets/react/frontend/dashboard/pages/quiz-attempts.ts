// Quiz Attempts Page
// Handles quiz history, results viewing, and retake functionality

import { showNotification } from '@FrontendServices/notifications';
import { DashboardAPI } from '../services/dashboard-api';

export const initializeQuizAttempts = () => {
  // Setup quiz actions
  setupQuizActions();
  
  // Load quiz attempts
  loadQuizAttempts();
  
  // Setup result viewing
  setupResultViewing();
};

const setupQuizActions = () => {
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    
    if (target.matches('.retake-quiz-btn')) {
      handleRetakeQuiz(target);
    } else if (target.matches('.view-result-btn')) {
      handleViewResult(target);
    }
  });
};

const handleRetakeQuiz = async (button: HTMLElement) => {
  const quizId = button.dataset.quizId;
  const attemptId = button.dataset.attemptId;
  
  if (!quizId) return;
  
  try {
    const canRetake = await DashboardAPI.checkQuizRetakeEligibility(parseInt(quizId));
    
    if (canRetake.allowed) {
      if (confirm('Are you sure you want to retake this quiz? Your previous score may be replaced.')) {
        window.location.href = `/quiz/${quizId}/attempt`;
      }
    } else {
      showNotification(canRetake.message || 'You cannot retake this quiz', 'warning');
    }
  } catch (error) {
    console.error('Failed to check retake eligibility:', error);
    showNotification('Failed to check quiz eligibility', 'error');
  }
};

const handleViewResult = async (button: HTMLElement) => {
  const attemptId = button.dataset.attemptId;
  
  if (!attemptId) return;
  
  try {
    const result = await DashboardAPI.getQuizResult(parseInt(attemptId));
    displayQuizResult(result);
  } catch (error) {
    console.error('Failed to load quiz result:', error);
    showNotification('Failed to load quiz result', 'error');
  }
};

const displayQuizResult = (result: any) => {
  // Create modal or expand section to show detailed results
  const modal = document.createElement('div');
  modal.className = 'quiz-result-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h3>Quiz Result</h3>
        <button class="close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="score-summary">
          <div class="score">${result.score}%</div>
          <div class="status ${result.passed ? 'passed' : 'failed'}">
            ${result.passed ? 'Passed' : 'Failed'}
          </div>
        </div>
        <div class="attempt-details">
          <p>Questions: ${result.correct_answers}/${result.total_questions}</p>
          <p>Time Taken: ${formatDuration(result.time_taken)}</p>
          <p>Attempt Date: ${formatDate(result.attempt_date)}</p>
        </div>
      </div>
    </div>
  `;
  
  // Add close functionality
  modal.querySelector('.close-btn')?.addEventListener('click', () => {
    modal.remove();
  });
  
  // Add to DOM
  document.body.appendChild(modal);
  
  // Close on outside click
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.remove();
    }
  });
};

const setupResultViewing = () => {
  // Setup expandable result sections
  document.querySelectorAll('.quiz-attempt-item').forEach(item => {
    const expandBtn = item.querySelector('.expand-details-btn');
    if (expandBtn) {
      expandBtn.addEventListener('click', () => {
        const details = item.querySelector('.attempt-details');
        if (details) {
          details.classList.toggle('expanded');
        }
      });
    }
  });
};

const loadQuizAttempts = async () => {
  try {
    const attempts = await DashboardAPI.getQuizAttempts();
    updateQuizAttemptsList(attempts);
  } catch (error) {
    console.error('Failed to load quiz attempts:', error);
    showNotification('Failed to load quiz attempts', 'error');
  }
};

const updateQuizAttemptsList = (attempts: any[]) => {
  const container = document.querySelector('.quiz-attempts-list');
  if (!container) return;
  
  // Update DOM with quiz attempts data
  // Group by quiz, show latest attempt prominently
};

const formatDuration = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  
  if (hours > 0) {
    return `${hours}h ${minutes}m ${secs}s`;
  } else if (minutes > 0) {
    return `${minutes}m ${secs}s`;
  } else {
    return `${secs}s`;
  }
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
};