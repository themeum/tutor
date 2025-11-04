// Settings Page
// Handles user profile settings and preferences

import { showNotification } from '@FrontendServices/notifications';
import { storage } from '@FrontendServices/storage';
import { validateForm } from '@FrontendServices/validation';
import { DashboardAPI } from '../services/dashboard-api';

export const initializeSettings = () => {
  // Setup settings forms
  setupSettingsForms();
  
  // Setup avatar upload
  setupAvatarUpload();
  
  // Setup password change
  setupPasswordChange();
  
  // Setup preferences
  setupPreferences();
};

const setupSettingsForms = () => {
  const profileForm = document.querySelector('#profile-form') as HTMLFormElement;
  if (profileForm) {
    profileForm.addEventListener('submit', handleProfileUpdate);
  }
  
  const preferencesForm = document.querySelector('#preferences-form') as HTMLFormElement;
  if (preferencesForm) {
    preferencesForm.addEventListener('submit', handlePreferencesUpdate);
  }
};

const handleProfileUpdate = async (event: Event) => {
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
    await DashboardAPI.updateProfile(formData);
    showNotification('Profile updated successfully', 'success');
  } catch (error) {
    console.error('Failed to update profile:', error);
    showNotification('Failed to update profile', 'error');
  }
};

const setupAvatarUpload = () => {
  const avatarInput = document.querySelector('#avatar-upload') as HTMLInputElement;
  const avatarPreview = document.querySelector('.avatar-preview') as HTMLImageElement;
  
  if (!avatarInput || !avatarPreview) return;
  
  avatarInput.addEventListener('change', (event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    
    // Validate image file
    if (!file.type.startsWith('image/')) {
      showNotification('Please select an image file', 'error');
      return;
    }
    
    if (file.size > 2 * 1024 * 1024) { // 2MB limit
      showNotification('Image size must be less than 2MB', 'error');
      return;
    }
    
    // Preview image
    const reader = new FileReader();
    reader.onload = (e) => {
      avatarPreview.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
    
    // Upload image
    uploadAvatar(file);
  });
};

const uploadAvatar = async (file: File) => {
  const formData = new FormData();
  formData.append('avatar', file);
  
  try {
    const result = await DashboardAPI.uploadAvatar(formData);
    showNotification('Avatar updated successfully', 'success');
    
    // Update avatar in header/sidebar if present
    updateAvatarInUI(result.avatar_url);
  } catch (error) {
    console.error('Failed to upload avatar:', error);
    showNotification('Failed to upload avatar', 'error');
  }
};

const updateAvatarInUI = (avatarUrl: string) => {
  document.querySelectorAll('.user-avatar').forEach(avatar => {
    (avatar as HTMLImageElement).src = avatarUrl;
  });
};

const setupPasswordChange = () => {
  const passwordForm = document.querySelector('#password-form') as HTMLFormElement;
  if (!passwordForm) return;
  
  passwordForm.addEventListener('submit', handlePasswordChange);
  
  // Setup password strength indicator
  const newPasswordInput = passwordForm.querySelector('#new-password') as HTMLInputElement;
  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', updatePasswordStrength);
  }
};

const handlePasswordChange = async (event: Event) => {
  event.preventDefault();
  
  const form = event.target as HTMLFormElement;
  const formData = new FormData(form);
  
  const currentPassword = formData.get('current_password') as string;
  const newPassword = formData.get('new_password') as string;
  const confirmPassword = formData.get('confirm_password') as string;
  
  // Validate passwords
  if (newPassword !== confirmPassword) {
    showNotification('New passwords do not match', 'error');
    return;
  }
  
  if (newPassword.length < 8) {
    showNotification('Password must be at least 8 characters long', 'error');
    return;
  }
  
  try {
    await DashboardAPI.changePassword({
      current_password: currentPassword,
      new_password: newPassword
    });
    
    showNotification('Password changed successfully', 'success');
    form.reset();
  } catch (error) {
    console.error('Failed to change password:', error);
    showNotification('Failed to change password', 'error');
  }
};

const updatePasswordStrength = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const password = input.value;
  const strengthIndicator = document.querySelector('.password-strength');
  
  if (!strengthIndicator) return;
  
  const strength = calculatePasswordStrength(password);
  strengthIndicator.className = `password-strength strength-${strength.level}`;
  strengthIndicator.textContent = strength.text;
};

const calculatePasswordStrength = (password: string) => {
  let score = 0;
  
  if (password.length >= 8) score++;
  if (/[a-z]/.test(password)) score++;
  if (/[A-Z]/.test(password)) score++;
  if (/[0-9]/.test(password)) score++;
  if (/[^A-Za-z0-9]/.test(password)) score++;
  
  const levels = ['weak', 'fair', 'good', 'strong'];
  const texts = ['Weak', 'Fair', 'Good', 'Strong'];
  
  const level = Math.min(score - 1, 3);
  return {
    level: levels[level] || 'weak',
    text: texts[level] || 'Weak'
  };
};

const setupPreferences = () => {
  // Setup notification preferences
  document.querySelectorAll('.notification-toggle').forEach(toggle => {
    toggle.addEventListener('change', handleNotificationToggle);
  });
  
  // Setup language preference
  const languageSelect = document.querySelector('#language-select') as HTMLSelectElement;
  if (languageSelect) {
    languageSelect.addEventListener('change', handleLanguageChange);
  }
};

const handleNotificationToggle = async (event: Event) => {
  const toggle = event.target as HTMLInputElement;
  const preference = toggle.dataset.preference;
  const enabled = toggle.checked;
  
  try {
    await DashboardAPI.updateNotificationPreference(preference!, enabled);
    
    // Store preference locally for immediate UI updates
    storage.set(`notification_${preference}`, enabled);
    
    showNotification('Notification preference updated', 'success');
  } catch (error) {
    console.error('Failed to update notification preference:', error);
    showNotification('Failed to update preference', 'error');
    
    // Revert toggle state
    toggle.checked = !enabled;
  }
};

const handleLanguageChange = async (event: Event) => {
  const select = event.target as HTMLSelectElement;
  const language = select.value;
  
  try {
    await DashboardAPI.updateLanguagePreference(language);
    showNotification('Language preference updated. Please refresh the page.', 'success');
    
    // Optionally reload page after a delay
    setTimeout(() => {
      window.location.reload();
    }, 2000);
  } catch (error) {
    console.error('Failed to update language preference:', error);
    showNotification('Failed to update language preference', 'error');
  }
};

const handlePreferencesUpdate = async (event: Event) => {
  event.preventDefault();
  
  const form = event.target as HTMLFormElement;
  const formData = new FormData(form);
  
  try {
    await DashboardAPI.updatePreferences(formData);
    showNotification('Preferences updated successfully', 'success');
  } catch (error) {
    console.error('Failed to update preferences:', error);
    showNotification('Failed to update preferences', 'error');
  }
};