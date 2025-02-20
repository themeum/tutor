import ajaxHandler from "../admin-dashboard/segments/filter";

document.addEventListener("DOMContentLoaded", function () {
  const { __, _x, _n, _nx } = wp.i18n;

  /**
   * On change status
   * update course status
   */
  const courseStatusUpdate = document.querySelectorAll(".tutor-table-row-status-update");
  for (let status of courseStatusUpdate) {
    status.onchange = async (e) => {
      const target = e.target;
      const newStatus = e.currentTarget.value;
      const prevStatus = target.dataset.status;
      if (newStatus === prevStatus) {
        return;
      }

      const icon1 = target.nextElementSibling;
      icon1.classList.add('is-loading-v2');

      // Prepare request form data
      const formData = new FormData();
      formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);

      // Assign all data to the request object
      for (let k in target.dataset) {
        formData.set(k, target.dataset[k]);
      }

      // Set the selected status
      formData.set(target.dataset.status_key, newStatus);

      // Init the http request
      const post = await ajaxHandler(formData);
      const response = await post.json();
      if (response) {

        if(response.success){
          target.dataset.status = newStatus;
          let putStatus = target.getElementsByTagName('OPTION')[target.selectedIndex].dataset.status_class;
          let message = response.data ? response.data.status : "Course status updated ";
          // add new status class
          target.closest(".tutor-form-select-with-icon").setAttribute('class', `tutor-form-select-with-icon ${putStatus}`);

          tutor_toast(__("Updated", "tutor"), __(message, "tutor"), "success");
          courseTabsCoursesCount(prevStatus, newStatus);
        }
        else{
         tutor_toast(__("Failed", "tutor"), __(response.data, 'tutor'), "error");
        }

      } else {
        tutor_toast(__("Failed", "tutor"), __('Course status update failed', 'tutor'), "error");
      }
      icon1.classList.remove('is-loading-v2');
    };
  }
  const courseTabsCoursesCount = (prevStatus, newStatus) => {
    let previousStatus = prevStatus === 'publish' ? 'published' : prevStatus;
    let nextStatus = newStatus === 'publish' ? 'published' : newStatus;
    let prevLabel = document.querySelector('a[data-keypage=' + previousStatus + ']');
    let nextLabel = document.querySelector('a[data-keypage=' + nextStatus + ']');

    if (prevLabel) {
      prevLabel.dataset.keyvalue = parseInt(prevLabel.dataset.keyvalue) - 1;
      prevLabel.querySelector('.filter-btn-number') && (prevLabel.querySelector('.filter-btn-number').innerText = '(' + prevLabel.dataset.keyvalue + ')');
    }

    if (nextLabel) {
      nextLabel.dataset.keyvalue = parseInt(nextLabel.dataset.keyvalue) + 1;
      nextLabel.querySelector('.filter-btn-number') && (nextLabel.querySelector('.filter-btn-number').innerText = '(' + nextLabel.dataset.keyvalue + ')');
    }
  }
});