// Assignments Page
// Handles assignment submissions and file uploads

import { showNotification } from '@FrontendServices/notifications';
import { validateForm } from '@FrontendServices/validation';
import { DashboardAPI } from '../services/dashboard-api';

export const initializeAssignments = () => {
  // Setup assignment submission forms
  setupAssignmentForms();
  
  // Setup file upload handlers
  setupFileUploads();
  
  // Load assignments
  loadAssignments();
};

const setupAssignmentForms = () => {
  document.querySelectorAll('.assignment-form').forEach(form => {
    form.addEventListener('submit', handleAssignmentSubmission);
  });
};

const handleAssignmentSubmission = async (event: Event) => {
  event.preventDefault();
  
  const form = event.target as HTMLFormElement;
  const formData = new FormData(form);
  
  // Validate form
  const validation = validateForm(form);
  if (!validation.isValid) {
    showNotification('Please fix the form errors', 'error');
    return;
  }
  
  try {
    const assignmentId = form.dataset.assignmentId;
    await DashboardAPI.submitAssignment(parseInt(assignmentId!), formData);
    showNotification('Assignment submitted successfully', 'success');
    
    // Update UI to show submitted state
    updateAssignmentStatus(assignmentId!, 'submitted');
  } catch (error) {
    console.error('Failed to submit assignment:', error);
    showNotification('Failed to submit assignment', 'error');
  }
};

const setupFileUploads = () => {
  document.querySelectorAll('.file-upload-input').forEach(input => {
    input.addEventListener('change', handleFileUpload);
  });
};

const handleFileUpload = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const files = input.files;
  
  if (!files || files.length === 0) return;
  
  // Validate file types and sizes
  for (const file of files) {
    if (!validateFile(file)) {
      showNotification(`Invalid file: ${file.name}`, 'error');
      input.value = '';
      return;
    }
  }
  
  // Update UI to show selected files
  updateFileList(input, files);
};

const validateFile = (file: File): boolean => {
  const maxSize = 10 * 1024 * 1024; // 10MB
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword'];
  
  if (file.size > maxSize) {
    showNotification('File size must be less than 10MB', 'error');
    return false;
  }
  
  if (!allowedTypes.includes(file.type)) {
    showNotification('File type not allowed', 'error');
    return false;
  }
  
  return true;
};

const updateFileList = (input: HTMLInputElement, files: FileList) => {
  const container = input.closest('.file-upload-container');
  const fileList = container?.querySelector('.file-list');
  
  if (!fileList) return;
  
  fileList.innerHTML = '';
  for (const file of files) {
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';
    fileItem.textContent = `${file.name} (${formatFileSize(file.size)})`;
    fileList.appendChild(fileItem);
  }
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const loadAssignments = async () => {
  try {
    const assignments = await DashboardAPI.getAssignments();
    updateAssignmentsList(assignments);
  } catch (error) {
    console.error('Failed to load assignments:', error);
    showNotification('Failed to load assignments', 'error');
  }
};

const updateAssignmentsList = (assignments: any[]) => {
  const container = document.querySelector('.assignments-list');
  if (!container) return;
  
  // Update DOM with assignments data
};

const updateAssignmentStatus = (assignmentId: string, status: string) => {
  const assignmentCard = document.querySelector(`[data-assignment-id="${assignmentId}"]`);
  if (assignmentCard) {
    assignmentCard.classList.add(`status-${status}`);
    // Update status indicator
  }
};