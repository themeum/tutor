// Assignment View Page
// Handles assignment display, file uploads, and submission

import { showNotification } from '@FrontendServices/notifications';
import { validateForm } from '@FrontendServices/validation';
import { LearningAPI } from '../services/learning-api';

export const initializeAssignmentView = () => {
  // Initialize assignment interface
  setupAssignmentInterface();
  
  // Initialize file upload
  setupFileUpload();
  
  // Initialize submission form
  setupSubmissionForm();
  
  // Initialize auto-save
  setupAutoSave();
};

const setupAssignmentInterface = () => {
  // Load assignment details
  loadAssignmentDetails();
  
  // Setup assignment actions
  setupAssignmentActions();
  
  // Check submission status
  checkSubmissionStatus();
};

const setupFileUpload = () => {
  const fileInput = document.querySelector('#assignment-files') as HTMLInputElement;
  const dropZone = document.querySelector('.file-drop-zone');
  const fileList = document.querySelector('.uploaded-files-list');
  
  if (!fileInput || !dropZone) return;
  
  let uploadedFiles: File[] = [];
  
  // File input change handler
  fileInput.addEventListener('change', (event) => {
    const files = (event.target as HTMLInputElement).files;
    if (files) {
      handleFileSelection(Array.from(files));
    }
  });
  
  // Drag and drop handlers
  dropZone.addEventListener('dragover', (event) => {
    event.preventDefault();
    dropZone.classList.add('drag-over');
  });
  
  dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
  });
  
  dropZone.addEventListener('drop', (event) => {
    event.preventDefault();
    dropZone.classList.remove('drag-over');
    
    const files = event.dataTransfer?.files;
    if (files) {
      handleFileSelection(Array.from(files));
    }
  });
  
  // Click to upload
  dropZone.addEventListener('click', () => {
    fileInput.click();
  });
  
  const handleFileSelection = (files: File[]) => {
    const validFiles = files.filter(file => validateFile(file));
    
    if (validFiles.length !== files.length) {
      showNotification('Some files were rejected due to size or type restrictions', 'warning');
    }
    
    validFiles.forEach(file => {
      if (!uploadedFiles.find(f => f.name === file.name && f.size === file.size)) {
        uploadedFiles.push(file);
        addFileToList(file);
      }
    });
    
    updateFileInput();
  };
  
  const validateFile = (file: File): boolean => {
    const maxSize = getMaxFileSize();
    const allowedTypes = getAllowedFileTypes();
    
    if (file.size > maxSize) {
      showNotification(`File "${file.name}" is too large. Maximum size is ${formatFileSize(maxSize)}`, 'error');
      return false;
    }
    
    if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
      showNotification(`File type "${file.type}" is not allowed for "${file.name}"`, 'error');
      return false;
    }
    
    return true;
  };
  
  const addFileToList = (file: File) => {
    if (!fileList) return;
    
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';
    fileItem.innerHTML = `
      <div class="file-info">
        <div class="file-name">${file.name}</div>
        <div class="file-size">${formatFileSize(file.size)}</div>
      </div>
      <button class="file-remove-btn" type="button" aria-label="Remove file">×</button>
    `;
    
    // Remove file handler
    const removeBtn = fileItem.querySelector('.file-remove-btn');
    if (removeBtn) {
      removeBtn.addEventListener('click', () => {
        uploadedFiles = uploadedFiles.filter(f => f !== file);
        fileItem.remove();
        updateFileInput();
      });
    }
    
    fileList.appendChild(fileItem);
  };
  
  const updateFileInput = () => {
    // Create new FileList (can't modify the original)
    const dataTransfer = new DataTransfer();
    uploadedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
    
    // Update UI
    const fileCount = document.querySelector('.file-count');
    if (fileCount) {
      fileCount.textContent = `${uploadedFiles.length} file(s) selected`;
    }
  };
};

const setupSubmissionForm = () => {
  const form = document.querySelector('#assignment-submission-form') as HTMLFormElement;
  if (!form) return;
  
  form.addEventListener('submit', handleSubmission);
  
  // Setup text editor if present
  setupTextEditor();
  
  // Load draft if exists
  loadDraft();
};

const setupTextEditor = () => {
  const textArea = document.querySelector('#assignment-text') as HTMLTextAreaElement;
  if (!textArea) return;
  
  // Auto-resize textarea
  textArea.addEventListener('input', () => {
    textArea.style.height = 'auto';
    textArea.style.height = textArea.scrollHeight + 'px';
  });
  
  // Character count
  const charCount = document.querySelector('.char-count');
  if (charCount) {
    const updateCharCount = () => {
      const count = textArea.value.length;
      charCount.textContent = `${count} characters`;
    };
    
    textArea.addEventListener('input', updateCharCount);
    updateCharCount(); // Initial count
  }
  
  // Word count
  const wordCount = document.querySelector('.word-count');
  if (wordCount) {
    const updateWordCount = () => {
      const words = textArea.value.trim().split(/\s+/).filter(word => word.length > 0);
      wordCount.textContent = `${words.length} words`;
    };
    
    textArea.addEventListener('input', updateWordCount);
    updateWordCount(); // Initial count
  }
};

const setupAutoSave = () => {
  let autoSaveTimeout: NodeJS.Timeout;
  
  const autoSave = () => {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
      saveDraft();
    }, 2000); // Auto-save after 2 seconds of inactivity
  };
  
  // Auto-save on text changes
  const textArea = document.querySelector('#assignment-text') as HTMLTextAreaElement;
  if (textArea) {
    textArea.addEventListener('input', autoSave);
  }
  
  // Save on page unload
  window.addEventListener('beforeunload', () => {
    saveDraft();
  });
};

const setupAssignmentActions = () => {
  // Download assignment instructions
  const downloadBtn = document.querySelector('.download-instructions-btn');
  if (downloadBtn) {
    downloadBtn.addEventListener('click', downloadInstructions);
  }
  
  // Print assignment
  const printBtn = document.querySelector('.print-assignment-btn');
  if (printBtn) {
    printBtn.addEventListener('click', () => {
      window.print();
    });
  }
  
  // Ask question about assignment
  const questionBtn = document.querySelector('.ask-question-btn');
  if (questionBtn) {
    questionBtn.addEventListener('click', openQuestionModal);
  }
};

const handleSubmission = async (event: Event) => {
  event.preventDefault();
  
  const form = event.target as HTMLFormElement;
  
  // Validate form
  const validation = validateForm(form);
  if (!validation.isValid) {
    showNotification('Please fix the form errors before submitting', 'error');
    return;
  }
  
  // Check if assignment text or files are provided
  const textArea = form.querySelector('#assignment-text') as HTMLTextAreaElement;
  const fileInput = form.querySelector('#assignment-files') as HTMLInputElement;
  
  const hasText = textArea && textArea.value.trim().length > 0;
  const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;
  
  if (!hasText && !hasFiles) {
    showNotification('Please provide either text submission or upload files', 'error');
    return;
  }
  
  // Confirm submission
  const confirmed = confirm(
    'Are you sure you want to submit this assignment? ' +
    'You will not be able to modify it after submission.'
  );
  
  if (!confirmed) return;
  
  try {
    // Show loading state
    const submitBtn = form.querySelector('.submit-btn') as HTMLButtonElement;
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    // Prepare form data
    const formData = new FormData(form);
    
    // Submit assignment
    const result = await LearningAPI.submitAssignment(getAssignmentId(), formData);
    
    // Clear draft
    clearDraft();
    
    // Show success message
    showNotification('Assignment submitted successfully!', 'success');
    
    // Update UI to show submitted state
    showSubmissionSuccess(result);
    
  } catch (error) {
    console.error('Failed to submit assignment:', error);
    showNotification('Failed to submit assignment. Please try again.', 'error');
    
    // Reset button state
    const submitBtn = form.querySelector('.submit-btn') as HTMLButtonElement;
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  }
};

const loadAssignmentDetails = async () => {
  try {
    const assignmentId = getAssignmentId();
    const assignment = await LearningAPI.getAssignment(assignmentId);
    
    // Update assignment details in UI
    updateAssignmentDetails(assignment);
    
  } catch (error) {
    console.error('Failed to load assignment details:', error);
    showNotification('Failed to load assignment details', 'error');
  }
};

const checkSubmissionStatus = async () => {
  try {
    const assignmentId = getAssignmentId();
    const submission = await LearningAPI.getAssignmentSubmission(assignmentId);
    
    if (submission) {
      showExistingSubmission(submission);
    }
    
  } catch (error) {
    // No existing submission or error - this is fine
    console.log('No existing submission found');
  }
};

const saveDraft = () => {
  const textArea = document.querySelector('#assignment-text') as HTMLTextAreaElement;
  if (!textArea) return;
  
  const draft = {
    text: textArea.value,
    timestamp: Date.now()
  };
  
  const assignmentId = getAssignmentId();
  localStorage.setItem(`assignment_${assignmentId}_draft`, JSON.stringify(draft));
  
  // Show draft saved indicator
  const draftIndicator = document.querySelector('.draft-saved-indicator');
  if (draftIndicator) {
    draftIndicator.textContent = 'Draft saved';
    draftIndicator.classList.add('visible');
    
    setTimeout(() => {
      draftIndicator.classList.remove('visible');
    }, 2000);
  }
};

const loadDraft = () => {
  const assignmentId = getAssignmentId();
  const saved = localStorage.getItem(`assignment_${assignmentId}_draft`);
  
  if (saved) {
    try {
      const draft = JSON.parse(saved);
      const textArea = document.querySelector('#assignment-text') as HTMLTextAreaElement;
      
      if (textArea && draft.text) {
        textArea.value = draft.text;
        
        // Show draft loaded message
        const draftTime = new Date(draft.timestamp).toLocaleString();
        showNotification(`Draft loaded from ${draftTime}`, 'info');
      }
    } catch (error) {
      console.error('Failed to load draft:', error);
    }
  }
};

const clearDraft = () => {
  const assignmentId = getAssignmentId();
  localStorage.removeItem(`assignment_${assignmentId}_draft`);
};

const showSubmissionSuccess = (result: any) => {
  // Hide submission form
  const form = document.querySelector('#assignment-submission-form');
  if (form) {
    form.style.display = 'none';
  }
  
  // Show success message
  const successContainer = document.querySelector('.submission-success');
  if (successContainer) {
    successContainer.innerHTML = `
      <div class="success-content">
        <div class="success-icon">✓</div>
        <h3>Assignment Submitted Successfully!</h3>
        <p>Your assignment has been submitted and is now under review.</p>
        
        <div class="submission-details">
          <p><strong>Submitted:</strong> ${new Date().toLocaleString()}</p>
          <p><strong>Submission ID:</strong> ${result.submission_id}</p>
        </div>
        
        <div class="next-steps">
          <p>You will be notified when your assignment has been graded.</p>
          <button class="btn btn-primary continue-btn">Continue Learning</button>
        </div>
      </div>
    `;
    
    successContainer.style.display = 'block';
    
    // Add continue button handler
    const continueBtn = successContainer.querySelector('.continue-btn');
    if (continueBtn) {
      continueBtn.addEventListener('click', () => {
        window.location.href = result.next_lesson_url || '/dashboard/courses';
      });
    }
  }
};

const showExistingSubmission = (submission: any) => {
  // Hide submission form
  const form = document.querySelector('#assignment-submission-form');
  if (form) {
    form.style.display = 'none';
  }
  
  // Show existing submission
  const submissionContainer = document.querySelector('.existing-submission');
  if (submissionContainer) {
    submissionContainer.innerHTML = `
      <div class="submission-content">
        <h3>Your Submission</h3>
        
        <div class="submission-status ${submission.status}">
          <span class="status-label">${getStatusLabel(submission.status)}</span>
        </div>
        
        <div class="submission-details">
          <p><strong>Submitted:</strong> ${new Date(submission.submitted_at).toLocaleString()}</p>
          ${submission.grade ? `<p><strong>Grade:</strong> ${submission.grade}/${submission.total_marks}</p>` : ''}
        </div>
        
        ${submission.submission_text ? `
          <div class="submission-text">
            <h4>Text Submission:</h4>
            <div class="text-content">${submission.submission_text}</div>
          </div>
        ` : ''}
        
        ${submission.attachments && submission.attachments.length > 0 ? `
          <div class="submission-files">
            <h4>Submitted Files:</h4>
            <ul class="file-list">
              ${submission.attachments.map(file => `
                <li>
                  <a href="${file.url}" target="_blank">${file.name}</a>
                  <span class="file-size">(${formatFileSize(file.size)})</span>
                </li>
              `).join('')}
            </ul>
          </div>
        ` : ''}
        
        ${submission.feedback ? `
          <div class="instructor-feedback">
            <h4>Instructor Feedback:</h4>
            <div class="feedback-content">${submission.feedback}</div>
          </div>
        ` : ''}
      </div>
    `;
    
    submissionContainer.style.display = 'block';
  }
};

const updateAssignmentDetails = (assignment: any) => {
  // Update title
  const titleElement = document.querySelector('.assignment-title');
  if (titleElement) {
    titleElement.textContent = assignment.title;
  }
  
  // Update description
  const descriptionElement = document.querySelector('.assignment-description');
  if (descriptionElement) {
    descriptionElement.innerHTML = assignment.description;
  }
  
  // Update instructions
  const instructionsElement = document.querySelector('.assignment-instructions');
  if (instructionsElement) {
    instructionsElement.innerHTML = assignment.instructions;
  }
  
  // Update due date
  const dueDateElement = document.querySelector('.assignment-due-date');
  if (dueDateElement && assignment.due_date) {
    const dueDate = new Date(assignment.due_date);
    dueDateElement.textContent = `Due: ${dueDate.toLocaleString()}`;
    
    // Add warning if due soon
    const now = new Date();
    const timeDiff = dueDate.getTime() - now.getTime();
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    if (daysDiff <= 1 && daysDiff > 0) {
      dueDateElement.classList.add('due-soon');
    } else if (daysDiff <= 0) {
      dueDateElement.classList.add('overdue');
    }
  }
  
  // Update file requirements
  const fileReqElement = document.querySelector('.file-requirements');
  if (fileReqElement) {
    fileReqElement.innerHTML = `
      <p><strong>Maximum file size:</strong> ${formatFileSize(assignment.max_file_size)}</p>
      <p><strong>Allowed file types:</strong> ${assignment.allowed_file_types.join(', ')}</p>
    `;
  }
};

const downloadInstructions = async () => {
  try {
    const assignmentId = getAssignmentId();
    const downloadUrl = await LearningAPI.getAssignmentInstructionsDownload(assignmentId);
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `assignment-${assignmentId}-instructions.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
  } catch (error) {
    console.error('Failed to download instructions:', error);
    showNotification('Failed to download instructions', 'error');
  }
};

const openQuestionModal = () => {
  // Implementation for opening question/discussion modal
  console.log('Opening question modal');
};

// Utility functions
const getAssignmentId = (): number => {
  const assignmentData = document.body.dataset.assignmentId;
  return assignmentData ? parseInt(assignmentData) : 0;
};

const getMaxFileSize = (): number => {
  const sizeData = document.body.dataset.maxFileSize;
  return sizeData ? parseInt(sizeData) : 10 * 1024 * 1024; // Default 10MB
};

const getAllowedFileTypes = (): string[] => {
  const typesData = document.body.dataset.allowedFileTypes;
  return typesData ? typesData.split(',') : [];
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getStatusLabel = (status: string): string => {
  const labels = {
    'submitted': 'Submitted',
    'graded': 'Graded',
    'returned': 'Returned for Revision'
  };
  return labels[status as keyof typeof labels] || status;
};