// Certificates Page
// Handles certificate viewing, downloading, and sharing

import { showNotification } from '@FrontendServices/notifications';
import { DashboardAPI } from '../services/dashboard-api';

export const initializeCertificates = () => {
  // Setup certificate actions
  setupCertificateActions();
  
  // Load certificates
  loadCertificates();
  
  // Setup sharing functionality
  setupSharing();
};

const setupCertificateActions = () => {
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement;
    
    if (target.matches('.download-certificate-btn')) {
      handleDownloadCertificate(target);
    } else if (target.matches('.view-certificate-btn')) {
      handleViewCertificate(target);
    } else if (target.matches('.share-certificate-btn')) {
      handleShareCertificate(target);
    }
  });
};

const handleDownloadCertificate = async (button: HTMLElement) => {
  const certificateId = button.dataset.certificateId;
  if (!certificateId) return;
  
  try {
    // Show loading state
    button.classList.add('loading');
    button.textContent = 'Downloading...';
    
    const downloadUrl = await DashboardAPI.getCertificateDownloadUrl(parseInt(certificateId));
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `certificate-${certificateId}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Certificate downloaded successfully', 'success');
  } catch (error) {
    console.error('Failed to download certificate:', error);
    showNotification('Failed to download certificate', 'error');
  } finally {
    // Reset button state
    button.classList.remove('loading');
    button.textContent = 'Download';
  }
};

const handleViewCertificate = async (button: HTMLElement) => {
  const certificateId = button.dataset.certificateId;
  if (!certificateId) return;
  
  try {
    const certificate = await DashboardAPI.getCertificate(parseInt(certificateId));
    displayCertificateModal(certificate);
  } catch (error) {
    console.error('Failed to load certificate:', error);
    showNotification('Failed to load certificate', 'error');
  }
};

const displayCertificateModal = (certificate: any) => {
  const modal = document.createElement('div');
  modal.className = 'certificate-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h3>Certificate Preview</h3>
        <button class="close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="certificate-preview">
          <img src="${certificate.preview_url}" alt="Certificate Preview" />
        </div>
        <div class="certificate-details">
          <h4>${certificate.course_title}</h4>
          <p>Issued on: ${formatDate(certificate.issued_date)}</p>
          <p>Certificate ID: ${certificate.certificate_id}</p>
        </div>
        <div class="certificate-actions">
          <button class="btn btn-primary download-btn" data-certificate-id="${certificate.id}">
            Download PDF
          </button>
          <button class="btn btn-secondary share-btn" data-certificate-id="${certificate.id}">
            Share
          </button>
        </div>
      </div>
    </div>
  `;
  
  // Add event listeners
  modal.querySelector('.close-btn')?.addEventListener('click', () => {
    modal.remove();
  });
  
  modal.querySelector('.download-btn')?.addEventListener('click', (e) => {
    handleDownloadCertificate(e.target as HTMLElement);
  });
  
  modal.querySelector('.share-btn')?.addEventListener('click', (e) => {
    handleShareCertificate(e.target as HTMLElement);
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

const handleShareCertificate = async (button: HTMLElement) => {
  const certificateId = button.dataset.certificateId;
  if (!certificateId) return;
  
  try {
    const shareData = await DashboardAPI.getCertificateShareData(parseInt(certificateId));
    
    // Check if Web Share API is available
    if (navigator.share) {
      await navigator.share({
        title: shareData.title,
        text: shareData.description,
        url: shareData.public_url
      });
    } else {
      // Fallback: show share modal with social links
      showShareModal(shareData);
    }
  } catch (error) {
    console.error('Failed to share certificate:', error);
    showNotification('Failed to share certificate', 'error');
  }
};

const showShareModal = (shareData: any) => {
  const modal = document.createElement('div');
  modal.className = 'share-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h3>Share Certificate</h3>
        <button class="close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="share-url">
          <label>Public URL:</label>
          <div class="url-input-group">
            <input type="text" value="${shareData.public_url}" readonly />
            <button class="copy-btn">Copy</button>
          </div>
        </div>
        <div class="social-share">
          <h4>Share on:</h4>
          <div class="social-buttons">
            <a href="${generateLinkedInShareUrl(shareData)}" target="_blank" class="social-btn linkedin">
              LinkedIn
            </a>
            <a href="${generateTwitterShareUrl(shareData)}" target="_blank" class="social-btn twitter">
              Twitter
            </a>
            <a href="${generateFacebookShareUrl(shareData)}" target="_blank" class="social-btn facebook">
              Facebook
            </a>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Add event listeners
  modal.querySelector('.close-btn')?.addEventListener('click', () => {
    modal.remove();
  });
  
  modal.querySelector('.copy-btn')?.addEventListener('click', () => {
    const input = modal.querySelector('input') as HTMLInputElement;
    input.select();
    document.execCommand('copy');
    showNotification('URL copied to clipboard', 'success');
  });
  
  document.body.appendChild(modal);
  
  // Close on outside click
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.remove();
    }
  });
};

const generateLinkedInShareUrl = (shareData: any): string => {
  const params = new URLSearchParams({
    url: shareData.public_url,
    title: shareData.title,
    summary: shareData.description
  });
  return `https://www.linkedin.com/sharing/share-offsite/?${params.toString()}`;
};

const generateTwitterShareUrl = (shareData: any): string => {
  const params = new URLSearchParams({
    url: shareData.public_url,
    text: `${shareData.title} - ${shareData.description}`
  });
  return `https://twitter.com/intent/tweet?${params.toString()}`;
};

const generateFacebookShareUrl = (shareData: any): string => {
  const params = new URLSearchParams({
    u: shareData.public_url
  });
  return `https://www.facebook.com/sharer/sharer.php?${params.toString()}`;
};

const setupSharing = () => {
  // Setup bulk sharing actions
  const selectAllCheckbox = document.querySelector('#select-all-certificates') as HTMLInputElement;
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', handleSelectAll);
  }
  
  // Setup individual certificate checkboxes
  document.querySelectorAll('.certificate-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
  });
};

const handleSelectAll = (event: Event) => {
  const selectAll = event.target as HTMLInputElement;
  const checkboxes = document.querySelectorAll('.certificate-checkbox') as NodeListOf<HTMLInputElement>;
  
  checkboxes.forEach(checkbox => {
    checkbox.checked = selectAll.checked;
  });
  
  updateBulkActions();
};

const updateBulkActions = () => {
  const checkedBoxes = document.querySelectorAll('.certificate-checkbox:checked');
  const bulkActions = document.querySelector('.bulk-actions');
  
  if (bulkActions) {
    if (checkedBoxes.length > 0) {
      bulkActions.classList.add('visible');
    } else {
      bulkActions.classList.remove('visible');
    }
  }
};

const loadCertificates = async () => {
  try {
    const certificates = await DashboardAPI.getCertificates();
    updateCertificatesList(certificates);
  } catch (error) {
    console.error('Failed to load certificates:', error);
    showNotification('Failed to load certificates', 'error');
  }
};

const updateCertificatesList = (certificates: any[]) => {
  const container = document.querySelector('.certificates-grid');
  if (!container) return;
  
  if (certificates.length === 0) {
    container.innerHTML = `
      <div class="no-certificates">
        <p>You haven't earned any certificates yet.</p>
        <a href="/courses" class="btn btn-primary">Browse Courses</a>
      </div>
    `;
    return;
  }
  
  // Update DOM with certificates data
  // This would typically use a template or render function
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};