// Discussion Page
// Handles course discussions, Q&A, and community interactions

import { showNotification } from '@FrontendServices/notifications';
import { validateForm } from '@FrontendServices/validation';
import { LearningAPI } from '../services/learning-api';

export const initializeDiscussion = () => {
  // Initialize discussion interface
  setupDiscussionInterface();
  
  // Initialize new discussion form
  setupNewDiscussionForm();
  
  // Initialize reply functionality
  setupReplyFunctionality();
  
  // Initialize search and filters
  setupSearchAndFilters();
};

const setupDiscussionInterface = () => {
  // Load discussions
  loadDiscussions();
  
  // Setup discussion actions
  setupDiscussionActions();
  
  // Setup infinite scroll
  setupInfiniteScroll();
  
  // Setup real-time updates
  setupRealTimeUpdates();
};

const setupDiscussionActions = () => {
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    
    if (target.matches('.like-discussion-btn')) {
      handleLikeDiscussion(target);
    } else if (target.matches('.reply-btn')) {
      handleReplyClick(target);
    } else if (target.matches('.resolve-btn')) {
      handleResolveDiscussion(target);
    } else if (target.matches('.report-btn')) {
      handleReportContent(target);
    } else if (target.matches('.edit-btn')) {
      handleEditContent(target);
    } else if (target.matches('.delete-btn')) {
      handleDeleteContent(target);
    }
  });
};

const setupNewDiscussionForm = () => {
  const form = document.querySelector('#new-discussion-form') as HTMLFormElement;
  if (!form) return;
  
  form.addEventListener('submit', handleNewDiscussion);
  
  // Setup rich text editor
  setupRichTextEditor();
  
  // Setup file attachments
  setupDiscussionAttachments();
  
  // Auto-save draft
  setupDiscussionDraft();
};

const setupRichTextEditor = () => {
  const textArea = document.querySelector('#discussion-content') as HTMLTextAreaElement;
  if (!textArea) return;
  
  // Basic formatting buttons
  const formatButtons = document.querySelectorAll('.format-btn');
  formatButtons.forEach(btn => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      const format = (event.target as HTMLElement).dataset.format;
      applyFormatting(textArea, format!);
    });
  });
  
  // Auto-resize
  textArea.addEventListener('input', () => {
    textArea.style.height = 'auto';
    textArea.style.height = textArea.scrollHeight + 'px';
  });
  
  // Character count
  const charCount = document.querySelector('.char-count');
  if (charCount) {
    textArea.addEventListener('input', () => {
      charCount.textContent = `${textArea.value.length} characters`;
    });
  }
};

const setupDiscussionAttachments = () => {
  const fileInput = document.querySelector('#discussion-attachments') as HTMLInputElement;
  const attachmentsList = document.querySelector('.attachments-list');
  
  if (!fileInput || !attachmentsList) return;
  
  fileInput.addEventListener('change', (event) => {
    const files = (event.target as HTMLInputElement).files;
    if (!files) return;
    
    Array.from(files).forEach(file => {
      if (validateAttachment(file)) {
        addAttachmentToList(file, attachmentsList);
      }
    });
  });
};

const setupReplyFunctionality = () => {
  // Reply form submission
  document.addEventListener('submit', (event) => {
    const form = event.target as HTMLFormElement;
    if (form.matches('.reply-form')) {
      event.preventDefault();
      handleReplySubmission(form);
    }
  });
  
  // Cancel reply
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    if (target.matches('.cancel-reply-btn')) {
      cancelReply(target);
    }
  });
};

const setupSearchAndFilters = () => {
  const searchInput = document.querySelector('#discussion-search') as HTMLInputElement;
  const filterSelect = document.querySelector('#discussion-filter') as HTMLSelectElement;
  const sortSelect = document.querySelector('#discussion-sort') as HTMLSelectElement;
  
  let searchTimeout: NodeJS.Timeout;
  
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterDiscussions();
      }, 300);
    });
  }
  
  if (filterSelect) {
    filterSelect.addEventListener('change', filterDiscussions);
  }
  
  if (sortSelect) {
    sortSelect.addEventListener('change', filterDiscussions);
  }
};

const setupInfiniteScroll = () => {
  let isLoading = false;
  let hasMore = true;
  let currentPage = 1;
  
  const loadMoreDiscussions = async () => {
    if (isLoading || !hasMore) return;
    
    isLoading = true;
    showLoadingIndicator();
    
    try {
      const discussions = await LearningAPI.getDiscussions(getCurrentCourseId(), {
        page: currentPage + 1,
        ...getCurrentFilters()
      });
      
      if (discussions.length === 0) {
        hasMore = false;
      } else {
        appendDiscussions(discussions);
        currentPage++;
      }
    } catch (error) {
      console.error('Failed to load more discussions:', error);
      showNotification('Failed to load more discussions', 'error');
    } finally {
      isLoading = false;
      hideLoadingIndicator();
    }
  };
  
  // Intersection Observer for infinite scroll
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      loadMoreDiscussions();
    }
  }, { threshold: 0.1 });
  
  const sentinel = document.querySelector('.discussions-sentinel');
  if (sentinel) {
    observer.observe(sentinel);
  }
};

const setupRealTimeUpdates = () => {
  // Poll for new discussions and replies every 30 seconds
  setInterval(async () => {
    try {
      const latestDiscussion = getLatestDiscussionTimestamp();
      const updates = await LearningAPI.getDiscussionUpdates(getCurrentCourseId(), latestDiscussion);
      
      if (updates.length > 0) {
        handleRealTimeUpdates(updates);
      }
    } catch (error) {
      console.error('Failed to check for updates:', error);
    }
  }, 30000);
};

const handleNewDiscussion = async (event: Event) => {
  event.preventDefault();
  
  const form = event.target as HTMLFormElement;
  const validation = validateForm(form);
  
  if (!validation.isValid) {
    showNotification('Please fix the form errors', 'error');
    return;
  }
  
  try {
    const submitBtn = form.querySelector('.submit-btn') as HTMLButtonElement;
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';
    
    const formData = new FormData(form);
    const discussion = await LearningAPI.createDiscussion(getCurrentCourseId(), formData);
    
    // Add to discussions list
    prependDiscussion(discussion);
    
    // Clear form
    form.reset();
    clearDiscussionDraft();
    
    showNotification('Discussion posted successfully!', 'success');
    
    // Reset button
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
    
  } catch (error) {
    console.error('Failed to create discussion:', error);
    showNotification('Failed to post discussion', 'error');
    
    const submitBtn = form.querySelector('.submit-btn') as HTMLButtonElement;
    submitBtn.disabled = false;
    submitBtn.textContent = 'Post Discussion';
  }
};

const handleReplyClick = (button: HTMLElement) => {
  const discussionId = button.dataset.discussionId;
  const discussionItem = document.querySelector(`[data-discussion-id="${discussionId}"]`);
  
  if (!discussionItem) return;
  
  // Check if reply form already exists
  let replyForm = discussionItem.querySelector('.reply-form');
  
  if (replyForm) {
    // Focus existing form
    const textArea = replyForm.querySelector('textarea') as HTMLTextAreaElement;
    if (textArea) textArea.focus();
    return;
  }
  
  // Create reply form
  replyForm = document.createElement('div');
  replyForm.className = 'reply-form-container';
  replyForm.innerHTML = `
    <form class="reply-form" data-discussion-id="${discussionId}">
      <div class="form-group">
        <textarea 
          name="content" 
          placeholder="Write your reply..." 
          required
          class="reply-textarea"
        ></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Post Reply</button>
        <button type="button" class="btn btn-secondary cancel-reply-btn">Cancel</button>
      </div>
    </form>
  `;
  
  // Insert after discussion content
  const repliesContainer = discussionItem.querySelector('.discussion-replies');
  if (repliesContainer) {
    repliesContainer.insertBefore(replyForm, repliesContainer.firstChild);
  }
  
  // Focus textarea
  const textArea = replyForm.querySelector('textarea') as HTMLTextAreaElement;
  if (textArea) {
    textArea.focus();
    
    // Auto-resize
    textArea.addEventListener('input', () => {
      textArea.style.height = 'auto';
      textArea.style.height = textArea.scrollHeight + 'px';
    });
  }
};

const handleReplySubmission = async (form: HTMLFormElement) => {
  const validation = validateForm(form);
  
  if (!validation.isValid) {
    showNotification('Please enter your reply', 'error');
    return;
  }
  
  try {
    const submitBtn = form.querySelector('[type="submit"]') as HTMLButtonElement;
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';
    
    const discussionId = parseInt(form.dataset.discussionId!);
    const formData = new FormData(form);
    
    const reply = await LearningAPI.createReply(discussionId, formData);
    
    // Add reply to discussion
    addReplyToDiscussion(discussionId, reply);
    
    // Remove reply form
    form.closest('.reply-form-container')?.remove();
    
    showNotification('Reply posted successfully!', 'success');
    
  } catch (error) {
    console.error('Failed to post reply:', error);
    showNotification('Failed to post reply', 'error');
    
    const submitBtn = form.querySelector('[type="submit"]') as HTMLButtonElement;
    submitBtn.disabled = false;
    submitBtn.textContent = 'Post Reply';
  }
};

const handleLikeDiscussion = async (button: HTMLElement) => {
  const discussionId = parseInt(button.dataset.discussionId!);
  const isLiked = button.classList.contains('liked');
  
  try {
    button.disabled = true;
    
    if (isLiked) {
      await LearningAPI.unlikeDiscussion(discussionId);
      button.classList.remove('liked');
      updateLikeCount(button, -1);
    } else {
      await LearningAPI.likeDiscussion(discussionId);
      button.classList.add('liked');
      updateLikeCount(button, 1);
    }
    
  } catch (error) {
    console.error('Failed to toggle like:', error);
    showNotification('Failed to update like', 'error');
  } finally {
    button.disabled = false;
  }
};

const handleResolveDiscussion = async (button: HTMLElement) => {
  const discussionId = parseInt(button.dataset.discussionId!);
  const isResolved = button.classList.contains('resolved');
  
  try {
    if (isResolved) {
      await LearningAPI.unresolveDiscussion(discussionId);
      button.classList.remove('resolved');
      button.textContent = 'Mark as Resolved';
    } else {
      await LearningAPI.resolveDiscussion(discussionId);
      button.classList.add('resolved');
      button.textContent = 'Resolved';
    }
    
    // Update discussion item
    const discussionItem = button.closest('.discussion-item');
    if (discussionItem) {
      discussionItem.classList.toggle('resolved', !isResolved);
    }
    
  } catch (error) {
    console.error('Failed to toggle resolution:', error);
    showNotification('Failed to update resolution status', 'error');
  }
};

const loadDiscussions = async () => {
  try {
    showLoadingIndicator();
    
    const discussions = await LearningAPI.getDiscussions(getCurrentCourseId(), getCurrentFilters());
    renderDiscussions(discussions);
    
  } catch (error) {
    console.error('Failed to load discussions:', error);
    showNotification('Failed to load discussions', 'error');
  } finally {
    hideLoadingIndicator();
  }
};

const filterDiscussions = async () => {
  try {
    showLoadingIndicator();
    
    const discussions = await LearningAPI.getDiscussions(getCurrentCourseId(), getCurrentFilters());
    renderDiscussions(discussions);
    
  } catch (error) {
    console.error('Failed to filter discussions:', error);
    showNotification('Failed to filter discussions', 'error');
  } finally {
    hideLoadingIndicator();
  }
};

const renderDiscussions = (discussions: any[]) => {
  const container = document.querySelector('.discussions-list');
  if (!container) return;
  
  if (discussions.length === 0) {
    container.innerHTML = `
      <div class="no-discussions">
        <p>No discussions found. Be the first to start a conversation!</p>
      </div>
    `;
    return;
  }
  
  container.innerHTML = discussions.map(discussion => renderDiscussionItem(discussion)).join('');
};

const renderDiscussionItem = (discussion: any): string => {
  return `
    <div class="discussion-item ${discussion.is_resolved ? 'resolved' : ''}" data-discussion-id="${discussion.id}">
      <div class="discussion-header">
        <div class="user-info">
          <img src="${discussion.user_avatar}" alt="${discussion.user_name}" class="user-avatar">
          <div class="user-details">
            <span class="user-name">${discussion.user_name}</span>
            <span class="post-time">${formatRelativeTime(discussion.created_date)}</span>
          </div>
        </div>
        <div class="discussion-actions">
          ${discussion.can_resolve ? `
            <button class="resolve-btn ${discussion.is_resolved ? 'resolved' : ''}" data-discussion-id="${discussion.id}">
              ${discussion.is_resolved ? 'Resolved' : 'Mark as Resolved'}
            </button>
          ` : ''}
        </div>
      </div>
      
      <div class="discussion-content">
        <h3 class="discussion-title">${discussion.title}</h3>
        <div class="discussion-text">${discussion.content}</div>
      </div>
      
      <div class="discussion-footer">
        <button class="like-btn ${discussion.is_liked ? 'liked' : ''}" data-discussion-id="${discussion.id}">
          <span class="like-icon">👍</span>
          <span class="like-count">${discussion.likes_count}</span>
        </button>
        <button class="reply-btn" data-discussion-id="${discussion.id}">
          Reply (${discussion.replies_count})
        </button>
      </div>
      
      <div class="discussion-replies">
        ${discussion.replies ? discussion.replies.map(reply => renderReplyItem(reply)).join('') : ''}
      </div>
    </div>
  `;
};

const renderReplyItem = (reply: any): string => {
  return `
    <div class="reply-item" data-reply-id="${reply.id}">
      <div class="reply-header">
        <img src="${reply.user_avatar}" alt="${reply.user_name}" class="user-avatar">
        <div class="user-details">
          <span class="user-name">${reply.user_name}</span>
          <span class="post-time">${formatRelativeTime(reply.created_date)}</span>
        </div>
      </div>
      <div class="reply-content">${reply.content}</div>
    </div>
  `;
};

// Utility functions
const getCurrentCourseId = (): number => {
  const courseData = document.body.dataset.courseId;
  return courseData ? parseInt(courseData) : 0;
};

const getCurrentFilters = () => {
  const searchInput = document.querySelector('#discussion-search') as HTMLInputElement;
  const filterSelect = document.querySelector('#discussion-filter') as HTMLSelectElement;
  const sortSelect = document.querySelector('#discussion-sort') as HTMLSelectElement;
  
  return {
    search: searchInput?.value || '',
    filter: filterSelect?.value || 'all',
    sort: sortSelect?.value || 'recent'
  };
};

const formatRelativeTime = (dateString: string): string => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
  
  if (diffInSeconds < 60) return 'just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
  return `${Math.floor(diffInSeconds / 86400)} days ago`;
};

const applyFormatting = (textArea: HTMLTextAreaElement, format: string) => {
  const start = textArea.selectionStart;
  const end = textArea.selectionEnd;
  const selectedText = textArea.value.substring(start, end);
  
  let formattedText = '';
  
  switch (format) {
    case 'bold':
      formattedText = `**${selectedText}**`;
      break;
    case 'italic':
      formattedText = `*${selectedText}*`;
      break;
    case 'code':
      formattedText = `\`${selectedText}\``;
      break;
    case 'link':
      const url = prompt('Enter URL:');
      if (url) {
        formattedText = `[${selectedText || 'Link text'}](${url})`;
      }
      break;
  }
  
  if (formattedText) {
    textArea.value = textArea.value.substring(0, start) + formattedText + textArea.value.substring(end);
    textArea.focus();
  }
};

const validateAttachment = (file: File): boolean => {
  const maxSize = 5 * 1024 * 1024; // 5MB
  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
  
  if (file.size > maxSize) {
    showNotification('File size must be less than 5MB', 'error');
    return false;
  }
  
  if (!allowedTypes.includes(file.type)) {
    showNotification('Only images and PDF files are allowed', 'error');
    return false;
  }
  
  return true;
};

const addAttachmentToList = (file: File, container: Element) => {
  const item = document.createElement('div');
  item.className = 'attachment-item';
  item.innerHTML = `
    <span class="file-name">${file.name}</span>
    <button type="button" class="remove-attachment">×</button>
  `;
  
  item.querySelector('.remove-attachment')?.addEventListener('click', () => {
    item.remove();
  });
  
  container.appendChild(item);
};

const setupDiscussionDraft = () => {
  const textArea = document.querySelector('#discussion-content') as HTMLTextAreaElement;
  if (!textArea) return;
  
  // Auto-save draft
  let draftTimeout: NodeJS.Timeout;
  textArea.addEventListener('input', () => {
    clearTimeout(draftTimeout);
    draftTimeout = setTimeout(() => {
      saveDiscussionDraft(textArea.value);
    }, 1000);
  });
  
  // Load draft on page load
  const draft = localStorage.getItem('discussion_draft');
  if (draft) {
    textArea.value = draft;
  }
};

const saveDiscussionDraft = (content: string) => {
  localStorage.setItem('discussion_draft', content);
};

const clearDiscussionDraft = () => {
  localStorage.removeItem('discussion_draft');
};

const prependDiscussion = (discussion: any) => {
  const container = document.querySelector('.discussions-list');
  if (!container) return;
  
  const discussionHtml = renderDiscussionItem(discussion);
  container.insertAdjacentHTML('afterbegin', discussionHtml);
};

const appendDiscussions = (discussions: any[]) => {
  const container = document.querySelector('.discussions-list');
  if (!container) return;
  
  const discussionsHtml = discussions.map(discussion => renderDiscussionItem(discussion)).join('');
  container.insertAdjacentHTML('beforeend', discussionsHtml);
};

const addReplyToDiscussion = (discussionId: number, reply: any) => {
  const discussionItem = document.querySelector(`[data-discussion-id="${discussionId}"]`);
  if (!discussionItem) return;
  
  const repliesContainer = discussionItem.querySelector('.discussion-replies');
  if (repliesContainer) {
    const replyHtml = renderReplyItem(reply);
    repliesContainer.insertAdjacentHTML('beforeend', replyHtml);
  }
  
  // Update reply count
  const replyBtn = discussionItem.querySelector('.reply-btn');
  if (replyBtn) {
    const currentCount = parseInt(replyBtn.textContent?.match(/\d+/)?.[0] || '0');
    replyBtn.textContent = `Reply (${currentCount + 1})`;
  }
};

const updateLikeCount = (button: HTMLElement, delta: number) => {
  const countElement = button.querySelector('.like-count');
  if (countElement) {
    const currentCount = parseInt(countElement.textContent || '0');
    countElement.textContent = (currentCount + delta).toString();
  }
};

const cancelReply = (button: HTMLElement) => {
  const replyForm = button.closest('.reply-form-container');
  if (replyForm) {
    replyForm.remove();
  }
};

const showLoadingIndicator = () => {
  const indicator = document.querySelector('.loading-indicator');
  if (indicator) {
    indicator.classList.add('visible');
  }
};

const hideLoadingIndicator = () => {
  const indicator = document.querySelector('.loading-indicator');
  if (indicator) {
    indicator.classList.remove('visible');
  }
};

const getLatestDiscussionTimestamp = (): string => {
  const discussions = document.querySelectorAll('.discussion-item');
  if (discussions.length === 0) return new Date().toISOString();
  
  // This would need to be implemented based on how timestamps are stored
  return new Date().toISOString();
};

const handleRealTimeUpdates = (updates: any[]) => {
  updates.forEach(update => {
    if (update.type === 'new_discussion') {
      prependDiscussion(update.data);
    } else if (update.type === 'new_reply') {
      addReplyToDiscussion(update.discussion_id, update.data);
    }
  });
  
  if (updates.length > 0) {
    showNotification(`${updates.length} new update(s)`, 'info');
  }
};

const handleReportContent = (button: HTMLElement) => {
  // Implementation for reporting inappropriate content
  console.log('Reporting content');
};

const handleEditContent = (button: HTMLElement) => {
  // Implementation for editing own content
  console.log('Editing content');
};

const handleDeleteContent = (button: HTMLElement) => {
  // Implementation for deleting own content
  console.log('Deleting content');
};