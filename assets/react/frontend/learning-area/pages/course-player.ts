// Course Player Page
// Handles video player, content display, and lesson navigation

import { showNotification } from '@FrontendServices/notifications';
import { LearningAPI } from '../services/learning-api';
import { ProgressTracker } from '../services/progress-tracking';

export const initializeCoursePlayer = () => {
  // Initialize video player
  setupVideoPlayer();
  
  // Initialize progress tracking
  setupProgressTracking();
  
  // Initialize player controls
  setupPlayerControls();
  
  // Initialize keyboard shortcuts
  setupKeyboardShortcuts();
};

const setupVideoPlayer = () => {
  const videoElement = document.querySelector('.course-video-player') as HTMLVideoElement;
  if (!videoElement) return;
  
  // Setup video event listeners
  videoElement.addEventListener('loadedmetadata', handleVideoLoaded);
  videoElement.addEventListener('timeupdate', handleTimeUpdate);
  videoElement.addEventListener('ended', handleVideoEnded);
  videoElement.addEventListener('pause', handleVideoPause);
  videoElement.addEventListener('play', handleVideoPlay);
  
  // Setup custom controls
  setupCustomControls(videoElement);
  
  // Restore last position
  restoreVideoPosition(videoElement);
};

const setupCustomControls = (video: HTMLVideoElement) => {
  const controls = document.querySelector('.video-controls');
  if (!controls) return;
  
  // Play/Pause button
  const playBtn = controls.querySelector('.play-pause-btn');
  if (playBtn) {
    playBtn.addEventListener('click', () => {
      if (video.paused) {
        video.play();
      } else {
        video.pause();
      }
    });
  }
  
  // Progress bar
  const progressBar = controls.querySelector('.progress-bar') as HTMLInputElement;
  if (progressBar) {
    progressBar.addEventListener('input', () => {
      const time = (parseFloat(progressBar.value) / 100) * video.duration;
      video.currentTime = time;
    });
  }
  
  // Volume control
  const volumeSlider = controls.querySelector('.volume-slider') as HTMLInputElement;
  if (volumeSlider) {
    volumeSlider.addEventListener('input', () => {
      video.volume = parseFloat(volumeSlider.value) / 100;
    });
  }
  
  // Speed control
  const speedSelect = controls.querySelector('.speed-select') as HTMLSelectElement;
  if (speedSelect) {
    speedSelect.addEventListener('change', () => {
      video.playbackRate = parseFloat(speedSelect.value);
    });
  }
  
  // Fullscreen button
  const fullscreenBtn = controls.querySelector('.fullscreen-btn');
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener('click', toggleFullscreen);
  }
};

const handleVideoLoaded = (event: Event) => {
  const video = event.target as HTMLVideoElement;
  updateVideoDuration(video.duration);
  
  // Initialize progress bar
  const progressBar = document.querySelector('.progress-bar') as HTMLInputElement;
  if (progressBar) {
    progressBar.max = '100';
    progressBar.value = '0';
  }
};

const handleTimeUpdate = (event: Event) => {
  const video = event.target as HTMLVideoElement;
  const currentTime = video.currentTime;
  const duration = video.duration;
  
  // Update progress bar
  const progressBar = document.querySelector('.progress-bar') as HTMLInputElement;
  if (progressBar && duration > 0) {
    const progress = (currentTime / duration) * 100;
    progressBar.value = progress.toString();
  }
  
  // Update time display
  updateTimeDisplay(currentTime, duration);
  
  // Save progress
  saveVideoProgress(currentTime);
  
  // Track lesson progress
  ProgressTracker.updateLessonProgress(getCurrentLessonId(), currentTime, duration);
};

const handleVideoEnded = async () => {
  try {
    const lessonId = getCurrentLessonId();
    await LearningAPI.completeLesson(lessonId);
    
    showNotification('Lesson completed!', 'success');
    
    // Auto-advance to next lesson
    const autoAdvance = getAutoAdvanceSetting();
    if (autoAdvance) {
      setTimeout(() => {
        navigateToNextLesson();
      }, 2000);
    } else {
      showNextLessonPrompt();
    }
  } catch (error) {
    console.error('Failed to mark lesson as complete:', error);
    showNotification('Failed to save progress', 'error');
  }
};

const handleVideoPause = () => {
  updatePlayButton('play');
  saveVideoProgress();
};

const handleVideoPlay = () => {
  updatePlayButton('pause');
};

const setupProgressTracking = () => {
  // Track time spent on lesson
  let startTime = Date.now();
  let isActive = true;
  
  // Track when user becomes inactive
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      isActive = false;
      ProgressTracker.pauseTracking();
    } else {
      isActive = true;
      startTime = Date.now();
      ProgressTracker.resumeTracking();
    }
  });
  
  // Periodic progress save
  setInterval(() => {
    if (isActive) {
      const timeSpent = Date.now() - startTime;
      ProgressTracker.addTimeSpent(getCurrentLessonId(), timeSpent);
      startTime = Date.now();
    }
  }, 30000); // Save every 30 seconds
};

const setupPlayerControls = () => {
  // Previous/Next lesson buttons
  const prevBtn = document.querySelector('.prev-lesson-btn');
  const nextBtn = document.querySelector('.next-lesson-btn');
  
  if (prevBtn) {
    prevBtn.addEventListener('click', navigateToPrevLesson);
  }
  
  if (nextBtn) {
    nextBtn.addEventListener('click', navigateToNextLesson);
  }
  
  // Lesson notes toggle
  const notesBtn = document.querySelector('.notes-toggle-btn');
  if (notesBtn) {
    notesBtn.addEventListener('click', toggleNotesPanel);
  }
  
  // Bookmark lesson
  const bookmarkBtn = document.querySelector('.bookmark-btn');
  if (bookmarkBtn) {
    bookmarkBtn.addEventListener('click', toggleBookmark);
  }
};

const setupKeyboardShortcuts = () => {
  document.addEventListener('keydown', (event) => {
    const video = document.querySelector('.course-video-player') as HTMLVideoElement;
    if (!video) return;
    
    // Don't handle shortcuts when typing in input fields
    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
      return;
    }
    
    switch (event.key) {
      case ' ':
      case 'k':
        event.preventDefault();
        if (video.paused) {
          video.play();
        } else {
          video.pause();
        }
        break;
        
      case 'ArrowLeft':
        event.preventDefault();
        video.currentTime = Math.max(0, video.currentTime - 10);
        break;
        
      case 'ArrowRight':
        event.preventDefault();
        video.currentTime = Math.min(video.duration, video.currentTime + 10);
        break;
        
      case 'ArrowUp':
        event.preventDefault();
        video.volume = Math.min(1, video.volume + 0.1);
        break;
        
      case 'ArrowDown':
        event.preventDefault();
        video.volume = Math.max(0, video.volume - 0.1);
        break;
        
      case 'f':
        event.preventDefault();
        toggleFullscreen();
        break;
        
      case 'm':
        event.preventDefault();
        video.muted = !video.muted;
        break;
    }
  });
};

const toggleFullscreen = () => {
  const playerContainer = document.querySelector('.video-player-container');
  if (!playerContainer) return;
  
  if (!document.fullscreenElement) {
    playerContainer.requestFullscreen().catch(err => {
      console.error('Failed to enter fullscreen:', err);
    });
  } else {
    document.exitFullscreen();
  }
};

const updatePlayButton = (state: 'play' | 'pause') => {
  const playBtn = document.querySelector('.play-pause-btn');
  if (playBtn) {
    playBtn.textContent = state === 'play' ? '▶' : '⏸';
    playBtn.setAttribute('aria-label', state === 'play' ? 'Play' : 'Pause');
  }
};

const updateTimeDisplay = (currentTime: number, duration: number) => {
  const timeDisplay = document.querySelector('.time-display');
  if (timeDisplay) {
    const current = formatTime(currentTime);
    const total = formatTime(duration);
    timeDisplay.textContent = `${current} / ${total}`;
  }
};

const updateVideoDuration = (duration: number) => {
  const durationDisplay = document.querySelector('.video-duration');
  if (durationDisplay) {
    durationDisplay.textContent = formatTime(duration);
  }
};

const formatTime = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = Math.floor(seconds % 60);
  
  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  } else {
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
  }
};

const saveVideoProgress = (currentTime?: number) => {
  const video = document.querySelector('.course-video-player') as HTMLVideoElement;
  if (!video) return;
  
  const time = currentTime !== undefined ? currentTime : video.currentTime;
  const lessonId = getCurrentLessonId();
  
  // Save to localStorage for immediate restoration
  localStorage.setItem(`lesson_${lessonId}_progress`, time.toString());
  
  // Save to server (debounced)
  debouncedSaveProgress(lessonId, time);
};

const restoreVideoPosition = (video: HTMLVideoElement) => {
  const lessonId = getCurrentLessonId();
  const savedTime = localStorage.getItem(`lesson_${lessonId}_progress`);
  
  if (savedTime) {
    const time = parseFloat(savedTime);
    if (time > 0 && time < video.duration) {
      video.currentTime = time;
    }
  }
};

const navigateToNextLesson = async () => {
  try {
    const nextLesson = await LearningAPI.getNextLesson(getCurrentLessonId());
    if (nextLesson) {
      window.location.href = nextLesson.url;
    } else {
      showNotification('You have completed all lessons in this course!', 'success');
    }
  } catch (error) {
    console.error('Failed to navigate to next lesson:', error);
    showNotification('Failed to load next lesson', 'error');
  }
};

const navigateToPrevLesson = async () => {
  try {
    const prevLesson = await LearningAPI.getPrevLesson(getCurrentLessonId());
    if (prevLesson) {
      window.location.href = prevLesson.url;
    } else {
      showNotification('This is the first lesson', 'info');
    }
  } catch (error) {
    console.error('Failed to navigate to previous lesson:', error);
    showNotification('Failed to load previous lesson', 'error');
  }
};

const toggleNotesPanel = () => {
  const notesPanel = document.querySelector('.lesson-notes-panel');
  if (notesPanel) {
    notesPanel.classList.toggle('visible');
  }
};

const toggleBookmark = async () => {
  try {
    const lessonId = getCurrentLessonId();
    const isBookmarked = await LearningAPI.toggleBookmark(lessonId);
    
    const bookmarkBtn = document.querySelector('.bookmark-btn');
    if (bookmarkBtn) {
      bookmarkBtn.classList.toggle('bookmarked', isBookmarked);
    }
    
    showNotification(
      isBookmarked ? 'Lesson bookmarked' : 'Bookmark removed',
      'success'
    );
  } catch (error) {
    console.error('Failed to toggle bookmark:', error);
    showNotification('Failed to update bookmark', 'error');
  }
};

const showNextLessonPrompt = () => {
  const modal = document.createElement('div');
  modal.className = 'next-lesson-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <h3>Lesson Complete!</h3>
      <p>Great job! Ready for the next lesson?</p>
      <div class="modal-actions">
        <button class="btn btn-secondary stay-btn">Stay Here</button>
        <button class="btn btn-primary next-btn">Next Lesson</button>
      </div>
    </div>
  `;
  
  modal.querySelector('.stay-btn')?.addEventListener('click', () => {
    modal.remove();
  });
  
  modal.querySelector('.next-btn')?.addEventListener('click', () => {
    modal.remove();
    navigateToNextLesson();
  });
  
  document.body.appendChild(modal);
};

// Utility functions
const getCurrentLessonId = (): number => {
  const lessonData = document.body.dataset.lessonId;
  return lessonData ? parseInt(lessonData) : 0;
};

const getAutoAdvanceSetting = (): boolean => {
  return localStorage.getItem('auto_advance_lessons') === 'true';
};

// Debounced save function
const debouncedSaveProgress = (() => {
  let timeout: NodeJS.Timeout;
  return (lessonId: number, time: number) => {
    clearTimeout(timeout);
    timeout = setTimeout(async () => {
      try {
        await LearningAPI.saveProgress(lessonId, time);
      } catch (error) {
        console.error('Failed to save progress to server:', error);
      }
    }, 2000);
  };
})();