// My Courses Page
// Handles course listing, filtering, and enrollment actions

import { showNotification } from '@FrontendServices/notifications';
import { DashboardAPI } from '../services/dashboard-api';

export const initializeMyCourses = () => {
  // Initialize course filters
  setupCourseFilters();
  
  // Setup course actions
  setupCourseActions();
  
  // Load courses
  loadCourses();
};

const setupCourseFilters = () => {
  const filterForm = document.querySelector('.course-filters') as HTMLFormElement;
  if (!filterForm) return;
  
  filterForm.addEventListener('change', handleFilterChange);
  filterForm.addEventListener('submit', (e) => {
    e.preventDefault();
    handleFilterChange();
  });
};

const handleFilterChange = async () => {
  const filterForm = document.querySelector('.course-filters') as HTMLFormElement;
  const formData = new FormData(filterForm);
  const filters = Object.fromEntries(formData.entries());
  
  try {
    const courses = await DashboardAPI.getCourses(filters);
    updateCourseList(courses);
  } catch (error) {
    console.error('Failed to filter courses:', error);
    showNotification('Failed to load courses', 'error');
  }
};

const setupCourseActions = () => {
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    
    if (target.matches('.continue-course-btn')) {
      handleContinueCourse(target);
    } else if (target.matches('.unenroll-btn')) {
      handleUnenroll(target);
    }
  });
};

const handleContinueCourse = (button: HTMLElement) => {
  const courseId = button.dataset.courseId;
  if (courseId) {
    window.location.href = `/courses/${courseId}/learn`;
  }
};

const handleUnenroll = async (button: HTMLElement) => {
  const courseId = button.dataset.courseId;
  if (!courseId) return;
  
  if (confirm('Are you sure you want to unenroll from this course?')) {
    try {
      await DashboardAPI.unenrollFromCourse(parseInt(courseId));
      showNotification('Successfully unenrolled from course', 'success');
      loadCourses(); // Refresh the list
    } catch (error) {
      console.error('Failed to unenroll:', error);
      showNotification('Failed to unenroll from course', 'error');
    }
  }
};

const loadCourses = async () => {
  try {
    const courses = await DashboardAPI.getCourses();
    updateCourseList(courses);
  } catch (error) {
    console.error('Failed to load courses:', error);
    showNotification('Failed to load courses', 'error');
  }
};

const updateCourseList = (courses: any[]) => {
  const courseContainer = document.querySelector('.courses-grid');
  if (!courseContainer) return;
  
  // Update DOM with course data
  // This would typically use a template or render function
};