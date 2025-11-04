// Lesson Content Page
// Handles text-based lessons, interactive content, and reading progress

import { showNotification } from '@FrontendServices/notifications';
import { LearningAPI } from '../services/learning-api';
import { ProgressTracker } from '../services/progress-tracking';

export const initializeLessonContent = () => {
  // Initialize content interactions
  setupContentInteractions();
  
  // Initialize reading progress
  setupReadingProgress();
  
  // Initialize content tools
  setupContentTools();
  
  // Initialize attachments
  setupAttachments();
};

const setupContentInteractions = () => {
  const contentArea = document.querySelector('.lesson-content-area');
  if (!contentArea) return;
  
  // Setup text selection and highlighting
  setupTextHighlighting(contentArea);
  
  // Setup content navigation
  setupContentNavigation(contentArea);
  
  // Setup interactive elements
  setupInteractiveElements(contentArea);
  
  // Mark lesson as started
  markLessonAsStarted();
};

const setupTextHighlighting = (contentArea: Element) => {
  let isHighlighting = false;
  
  // Enable highlighting mode
  const highlightBtn = document.querySelector('.highlight-tool-btn');
  if (highlightBtn) {
    highlightBtn.addEventListener('click', () => {
      isHighlighting = !isHighlighting;
      contentArea.classList.toggle('highlighting-mode', isHighlighting);
      highlightBtn.classList.toggle('active', isHighlighting);
    });
  }
  
  // Handle text selection
  contentArea.addEventListener('mouseup', () => {
    if (!isHighlighting) return;
    
    const selection = window.getSelection();
    if (!selection || selection.isCollapsed) return;
    
    const selectedText = selection.toString().trim();
    if (selectedText.length > 0) {
      highlightSelectedText(selection);
      saveHighlight(selectedText, getSelectionPosition(selection));
    }
  });
  
  // Load existing highlights
  loadHighlights();
};

const highlightSelectedText = (selection: Selection) => {
  try {
    const range = selection.getRangeAt(0);
    const span = document.createElement('span');
    span.className = 'text-highlight';
    span.dataset.highlightId = generateHighlightId();
    
    // Add remove button
    const removeBtn = document.createElement('button');
    removeBtn.className = 'highlight-remove-btn';
    removeBtn.innerHTML = '×';
    removeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      removeHighlight(span);
    });
    
    range.surroundContents(span);
    span.appendChild(removeBtn);
    
    selection.removeAllRanges();
  } catch (error) {
    console.error('Failed to highlight text:', error);
  }
};

const setupContentNavigation = (contentArea: Element) => {
  // Setup table of contents
  generateTableOfContents(contentArea);
  
  // Setup scroll spy for navigation
  setupScrollSpy(contentArea);
  
  // Setup smooth scrolling for anchor links
  contentArea.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    if (target.tagName === 'A' && target.getAttribute('href')?.startsWith('#')) {
      event.preventDefault();
      const targetId = target.getAttribute('href')!.substring(1);
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }
    }
  });
};

const generateTableOfContents = (contentArea: Element) => {
  const headings = contentArea.querySelectorAll('h1, h2, h3, h4, h5, h6');
  if (headings.length === 0) return;
  
  const tocContainer = document.querySelector('.table-of-contents');
  if (!tocContainer) return;
  
  const tocList = document.createElement('ul');
  tocList.className = 'toc-list';
  
  headings.forEach((heading, index) => {
    // Add ID to heading if it doesn't have one
    if (!heading.id) {
      heading.id = `heading-${index}`;
    }
    
    const listItem = document.createElement('li');
    listItem.className = `toc-item toc-level-${heading.tagName.toLowerCase()}`;
    
    const link = document.createElement('a');
    link.href = `#${heading.id}`;
    link.textContent = heading.textContent;
    link.addEventListener('click', (e) => {
      e.preventDefault();
      heading.scrollIntoView({ behavior: 'smooth' });
    });
    
    listItem.appendChild(link);
    tocList.appendChild(listItem);
  });
  
  tocContainer.appendChild(tocList);
};

const setupScrollSpy = (contentArea: Element) => {
  const headings = contentArea.querySelectorAll('h1, h2, h3, h4, h5, h6');
  const tocLinks = document.querySelectorAll('.toc-list a');
  
  if (headings.length === 0 || tocLinks.length === 0) return;
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Remove active class from all TOC links
        tocLinks.forEach(link => link.classList.remove('active'));
        
        // Add active class to current heading's TOC link
        const activeLink = document.querySelector(`a[href="#${entry.target.id}"]`);
        if (activeLink) {
          activeLink.classList.add('active');
        }
      }
    });
  }, {
    rootMargin: '-20% 0px -70% 0px'
  });
  
  headings.forEach(heading => observer.observe(heading));
};

const setupReadingProgress = () => {
  let readingStartTime = Date.now();
  let totalReadingTime = 0;
  
  // Track reading progress based on scroll position
  const updateReadingProgress = () => {
    const scrollTop = window.pageYOffset;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = Math.min(100, (scrollTop / docHeight) * 100);
    
    // Update progress bar
    const progressBar = document.querySelector('.reading-progress-bar') as HTMLElement;
    if (progressBar) {
      progressBar.style.width = `${scrollPercent}%`;
    }
    
    // Save progress
    ProgressTracker.updateReadingProgress(getCurrentLessonId(), scrollPercent);
    
    // Mark as complete if user has read 90% or more
    if (scrollPercent >= 90) {
      markLessonAsComplete();
    }
  };
  
  // Throttled scroll handler
  let scrollTimeout: NodeJS.Timeout;
  window.addEventListener('scroll', () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(updateReadingProgress, 100);
  });
  
  // Track time spent reading
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      totalReadingTime += Date.now() - readingStartTime;
      ProgressTracker.addTimeSpent(getCurrentLessonId(), totalReadingTime);
    } else {
      readingStartTime = Date.now();
    }
  });
  
  // Periodic time tracking
  setInterval(() => {
    if (!document.hidden) {
      const sessionTime = Date.now() - readingStartTime;
      ProgressTracker.addTimeSpent(getCurrentLessonId(), sessionTime);
      readingStartTime = Date.now();
    }
  }, 30000);
};

const setupContentTools = () => {
  // Font size controls
  const fontSizeControls = document.querySelector('.font-size-controls');
  if (fontSizeControls) {
    const increaseBtn = fontSizeControls.querySelector('.font-increase');
    const decreaseBtn = fontSizeControls.querySelector('.font-decrease');
    const resetBtn = fontSizeControls.querySelector('.font-reset');
    
    if (increaseBtn) {
      increaseBtn.addEventListener('click', () => adjustFontSize(1));
    }
    if (decreaseBtn) {
      decreaseBtn.addEventListener('click', () => adjustFontSize(-1));
    }
    if (resetBtn) {
      resetBtn.addEventListener('click', () => resetFontSize());
    }
  }
  
  // Dark mode toggle
  const darkModeToggle = document.querySelector('.dark-mode-toggle');
  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', toggleDarkMode);
    
    // Load saved preference
    const isDarkMode = localStorage.getItem('lesson_dark_mode') === 'true';
    if (isDarkMode) {
      document.body.classList.add('dark-mode');
    }
  }
  
  // Print button
  const printBtn = document.querySelector('.print-lesson-btn');
  if (printBtn) {
    printBtn.addEventListener('click', () => {
      window.print();
    });
  }
  
  // Notes panel toggle
  const notesToggle = document.querySelector('.notes-panel-toggle');
  if (notesToggle) {
    notesToggle.addEventListener('click', toggleNotesPanel);
  }
};

const setupInteractiveElements = (contentArea: Element) => {
  // Setup expandable sections
  const expandableSections = contentArea.querySelectorAll('.expandable-section');
  expandableSections.forEach(section => {
    const header = section.querySelector('.section-header');
    const content = section.querySelector('.section-content');
    
    if (header && content) {
      header.addEventListener('click', () => {
        const isExpanded = section.classList.contains('expanded');
        section.classList.toggle('expanded', !isExpanded);
        content.style.display = isExpanded ? 'none' : 'block';
      });
    }
  });
  
  // Setup interactive quizzes/polls within content
  const interactiveElements = contentArea.querySelectorAll('.interactive-element');
  interactiveElements.forEach(element => {
    const type = element.dataset.type;
    
    switch (type) {
      case 'poll':
        setupPoll(element);
        break;
      case 'quiz':
        setupInlineQuiz(element);
        break;
      case 'checklist':
        setupChecklist(element);
        break;
    }
  });
};

const setupAttachments = () => {
  const attachmentsList = document.querySelector('.lesson-attachments');
  if (!attachmentsList) return;
  
  // Setup download tracking
  const downloadLinks = attachmentsList.querySelectorAll('.attachment-download');
  downloadLinks.forEach(link => {
    link.addEventListener('click', (event) => {
      const attachmentId = (event.target as HTMLElement).dataset.attachmentId;
      if (attachmentId) {
        trackAttachmentDownload(parseInt(attachmentId));
      }
    });
  });
};

const adjustFontSize = (delta: number) => {
  const contentArea = document.querySelector('.lesson-content-area') as HTMLElement;
  if (!contentArea) return;
  
  const currentSize = parseInt(getComputedStyle(contentArea).fontSize);
  const newSize = Math.max(12, Math.min(24, currentSize + delta));
  
  contentArea.style.fontSize = `${newSize}px`;
  localStorage.setItem('lesson_font_size', newSize.toString());
};

const resetFontSize = () => {
  const contentArea = document.querySelector('.lesson-content-area') as HTMLElement;
  if (!contentArea) return;
  
  contentArea.style.fontSize = '';
  localStorage.removeItem('lesson_font_size');
};

const toggleDarkMode = () => {
  const isDarkMode = document.body.classList.toggle('dark-mode');
  localStorage.setItem('lesson_dark_mode', isDarkMode.toString());
};

const toggleNotesPanel = () => {
  const notesPanel = document.querySelector('.lesson-notes-panel');
  if (notesPanel) {
    notesPanel.classList.toggle('visible');
  }
};

const setupPoll = (element: Element) => {
  const options = element.querySelectorAll('.poll-option');
  const submitBtn = element.querySelector('.poll-submit');
  
  options.forEach(option => {
    option.addEventListener('click', () => {
      options.forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
    });
  });
  
  if (submitBtn) {
    submitBtn.addEventListener('click', async () => {
      const selected = element.querySelector('.poll-option.selected');
      if (!selected) {
        showNotification('Please select an option', 'warning');
        return;
      }
      
      try {
        const pollId = element.dataset.pollId;
        const optionId = selected.dataset.optionId;
        
        await LearningAPI.submitPollResponse(parseInt(pollId!), parseInt(optionId!));
        
        // Show results
        showPollResults(element);
        showNotification('Response submitted!', 'success');
      } catch (error) {
        console.error('Failed to submit poll response:', error);
        showNotification('Failed to submit response', 'error');
      }
    });
  }
};

const setupInlineQuiz = (element: Element) => {
  const submitBtn = element.querySelector('.quiz-submit');
  
  if (submitBtn) {
    submitBtn.addEventListener('click', async () => {
      const answers = collectQuizAnswers(element);
      
      try {
        const quizId = element.dataset.quizId;
        const result = await LearningAPI.submitInlineQuiz(parseInt(quizId!), answers);
        
        showQuizResults(element, result);
      } catch (error) {
        console.error('Failed to submit quiz:', error);
        showNotification('Failed to submit quiz', 'error');
      }
    });
  }
};

const setupChecklist = (element: Element) => {
  const checkboxes = element.querySelectorAll('.checklist-item input[type="checkbox"]');
  
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      const itemId = (checkbox as HTMLInputElement).dataset.itemId;
      const isChecked = (checkbox as HTMLInputElement).checked;
      
      // Save checklist state
      saveChecklistState(itemId!, isChecked);
      
      // Update progress
      updateChecklistProgress(element);
    });
  });
  
  // Load saved states
  loadChecklistStates(element);
};

const markLessonAsStarted = async () => {
  try {
    const lessonId = getCurrentLessonId();
    await LearningAPI.markLessonStarted(lessonId);
  } catch (error) {
    console.error('Failed to mark lesson as started:', error);
  }
};

const markLessonAsComplete = async () => {
  try {
    const lessonId = getCurrentLessonId();
    const isAlreadyComplete = document.body.classList.contains('lesson-complete');
    
    if (!isAlreadyComplete) {
      await LearningAPI.completeLesson(lessonId);
      document.body.classList.add('lesson-complete');
      showNotification('Lesson completed!', 'success');
    }
  } catch (error) {
    console.error('Failed to mark lesson as complete:', error);
  }
};

// Highlight management functions
const saveHighlight = (text: string, position: any) => {
  const lessonId = getCurrentLessonId();
  const highlights = getStoredHighlights(lessonId);
  
  highlights.push({
    id: generateHighlightId(),
    text,
    position,
    timestamp: Date.now()
  });
  
  localStorage.setItem(`lesson_${lessonId}_highlights`, JSON.stringify(highlights));
};

const loadHighlights = () => {
  const lessonId = getCurrentLessonId();
  const highlights = getStoredHighlights(lessonId);
  
  // Apply highlights to content
  highlights.forEach(highlight => {
    // This would need more sophisticated implementation
    // to restore highlights based on position
  });
};

const removeHighlight = (highlightElement: HTMLElement) => {
  const highlightId = highlightElement.dataset.highlightId;
  const lessonId = getCurrentLessonId();
  const highlights = getStoredHighlights(lessonId);
  
  const updatedHighlights = highlights.filter(h => h.id !== highlightId);
  localStorage.setItem(`lesson_${lessonId}_highlights`, JSON.stringify(updatedHighlights));
  
  // Remove from DOM
  const parent = highlightElement.parentNode;
  if (parent) {
    parent.replaceChild(document.createTextNode(highlightElement.textContent || ''), highlightElement);
  }
};

const getStoredHighlights = (lessonId: number) => {
  const stored = localStorage.getItem(`lesson_${lessonId}_highlights`);
  return stored ? JSON.parse(stored) : [];
};

const generateHighlightId = () => {
  return `highlight_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
};

const getSelectionPosition = (selection: Selection) => {
  // Simplified position tracking
  const range = selection.getRangeAt(0);
  return {
    startOffset: range.startOffset,
    endOffset: range.endOffset,
    startContainer: range.startContainer.textContent,
    endContainer: range.endContainer.textContent
  };
};

// Utility functions
const getCurrentLessonId = (): number => {
  const lessonData = document.body.dataset.lessonId;
  return lessonData ? parseInt(lessonData) : 0;
};

const collectQuizAnswers = (quizElement: Element) => {
  const answers: any = {};
  
  const questions = quizElement.querySelectorAll('.quiz-question');
  questions.forEach(question => {
    const questionId = question.dataset.questionId;
    const inputs = question.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
      const inputElement = input as HTMLInputElement;
      if (inputElement.type === 'radio' || inputElement.type === 'checkbox') {
        if (inputElement.checked) {
          answers[questionId!] = inputElement.value;
        }
      } else {
        answers[questionId!] = inputElement.value;
      }
    });
  });
  
  return answers;
};

const showPollResults = (pollElement: Element) => {
  // Show poll results UI
  pollElement.classList.add('results-shown');
};

const showQuizResults = (quizElement: Element, results: any) => {
  // Show quiz results UI
  quizElement.classList.add('results-shown');
  
  const resultsContainer = quizElement.querySelector('.quiz-results');
  if (resultsContainer) {
    resultsContainer.innerHTML = `
      <div class="quiz-score">Score: ${results.score}%</div>
      <div class="quiz-feedback">${results.feedback}</div>
    `;
  }
};

const saveChecklistState = (itemId: string, isChecked: boolean) => {
  const lessonId = getCurrentLessonId();
  const key = `lesson_${lessonId}_checklist_${itemId}`;
  localStorage.setItem(key, isChecked.toString());
};

const loadChecklistStates = (checklistElement: Element) => {
  const lessonId = getCurrentLessonId();
  const checkboxes = checklistElement.querySelectorAll('input[type="checkbox"]');
  
  checkboxes.forEach(checkbox => {
    const itemId = (checkbox as HTMLInputElement).dataset.itemId;
    const key = `lesson_${lessonId}_checklist_${itemId}`;
    const isChecked = localStorage.getItem(key) === 'true';
    
    (checkbox as HTMLInputElement).checked = isChecked;
  });
  
  updateChecklistProgress(checklistElement);
};

const updateChecklistProgress = (checklistElement: Element) => {
  const checkboxes = checklistElement.querySelectorAll('input[type="checkbox"]');
  const checkedBoxes = checklistElement.querySelectorAll('input[type="checkbox"]:checked');
  
  const progress = (checkedBoxes.length / checkboxes.length) * 100;
  
  const progressBar = checklistElement.querySelector('.checklist-progress');
  if (progressBar) {
    (progressBar as HTMLElement).style.width = `${progress}%`;
  }
};

const trackAttachmentDownload = async (attachmentId: number) => {
  try {
    await LearningAPI.trackAttachmentDownload(attachmentId);
  } catch (error) {
    console.error('Failed to track attachment download:', error);
  }
};