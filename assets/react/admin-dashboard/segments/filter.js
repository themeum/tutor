/**
 * On click add filter value on the url
 * and refresh page
 *
 * Handle bulk action
 *
 * @package Filter / sorting
 * @since v2.0.0
 */
const { __, _x, _n, _nx } = wp.i18n;

document.addEventListener("DOMContentLoaded", function() {
  const filterCourse = document.getElementById("tutor-backend-filter-course");
  if (filterCourse) {
    filterCourse.onchange = (e) => {
      window.location = urlPrams("course-id", e.target.value);
    };
  }
  const filterCategory = document.getElementById("tutor-backend-filter-category");
  if (filterCategory) {
    filterCategory.onchange = (e) => {
      window.location = urlPrams("category", e.target.value);
    };
  }
  const filterOrder = document.getElementById("tutor-backend-filter-order");
  if (filterOrder) {
    filterOrder.onchange = (e) => {
      window.location = urlPrams("order", e.target.value);
    };
  }

  const filterSearch = document.getElementById("tutor-admin-search-filter-form");
  if (filterSearch) {
    filterSearch.onsubmit = (e) => {
      e.preventDefault();
      const search = document.getElementById("tutor-backend-filter-search").value;
      window.location = urlPrams("search", search);
    };
  }

  /**
   * Onsubmit bulk form handle ajax request then reload page
   */
  const bulkForm = document.getElementById("tutor-admin-bulk-action-form");
  if (bulkForm) {
    bulkForm.onsubmit = async (e) => {
      e.preventDefault();
      const formData = new FormData(bulkForm);
      const bulkIds = [];
      const bulkFields = document.querySelectorAll(".tutor-bulk-checkbox");
      for (let field of bulkFields) {
        if (field.checked) {
          bulkIds.push(field.value);
        }
      }
      if (!bulkIds.length) {
        alert( __('Select checkbox for action', 'tutor') );
        return;
      }
      formData.set("bulk-ids", bulkIds);
      formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
      try {
        const loadingButton = document.querySelector('#tutor-confirm-bulk-action.tutor-btn-loading');
        const prevHtml = loadingButton.innerHTML;
        loadingButton.innerHTML = `<div class="ball"></div>
        <div class="ball"></div>
        <div class="ball"></div>
        <div class="ball"></div>`;
        const post = await fetch(window._tutorobject.ajaxurl, {
          method: "POST",
          body: formData,
        });
        loadingButton.innerHTML = prevHtml;
        if (post.ok) {
          const response = await post.json();
          if (response.success) {
            location.reload();
          } else {
            tutor_toast(__("Failed", "tutor"), __("Something went wrong, please try again ", "tutor"), "error");
          }
          
        }
      } catch (error) {
        alert(error);
      }
    };
  }

  /**
   * onclick bulk action button show confirm popup
   * on click confirm button submit bulk form
   */
  const bulkActionButton = document.getElementById("tutor-confirm-bulk-action");
  if (bulkActionButton) {
    bulkActionButton.onclick = () => {
      const input = document.createElement("input");
      input.type = "submit";
      bulkForm.appendChild(input);
      input.click();
      input.remove();
    };
  }

  function urlPrams(type, val) {
    const url = new URL(window.location.href);
    const params = url.searchParams;
    params.set(type, val);
    params.set("paged", 1);
    return url;
  }

  /**
   * Select all bulk checkboxes
   *
   * @since v2.0.0
   */
  const selectAll = document.querySelector("#tutor-bulk-checkbox-all");
  if (selectAll) {
    selectAll.addEventListener("click", () => {
      const checkboxes = document.querySelectorAll(".tutor-bulk-checkbox");
      checkboxes.forEach((item) => {
        if (selectAll.checked) {
          item.checked = true;
        } else {
          item.checked = false;
        }
      });
    });
  }

  /**
   * On change status
   * update course status
   */
  const availableStatus = ["publish", "pending", "trash", "draft"];
  const courseStatusUpdate = document.querySelectorAll(".tutor-admin-course-status-update");
  for (let status of courseStatusUpdate) {
    status.onchange = async (e) => {
      const target = e.target;
      const newStatus = availableStatus[target.selectedIndex];
      const prevStatus = target.dataset.status;
      if (newStatus === prevStatus) {
        return;
      }

      const formData = new FormData();
      formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
      formData.set("id", target.dataset.id);
      formData.set("status", newStatus);
      formData.set("action", "tutor_change_course_status");
      const post = await ajaxHandler(formData);
      const response = await post.json();
      if (response) {
        target.dataset.status = newStatus;
        let putStatus = "select-default";
        newStatus === "publish"
          ? (putStatus = "select-success")
          : newStatus === "pending"
          ? (putStatus = "select-warning")
          : newStatus === 'trash'
          ? (putStatus = "select-danger" )
          : "select-default";

        // add new status class
        target.closest(".tutor-form-select-with-icon").setAttribute('class', `tutor-form-select-with-icon ${putStatus}` );
  
        tutor_toast(__("Updated", "tutor"), __("Course status updated ", "tutor"), "success");
      } else {
        tutor_toast(__("Failed", "tutor"), __("Course status update failed ", "tutor"), "error");
      }
    };
  }

  /**
   * Delete course delete
   */
  const deleteCourse = document.querySelectorAll(".tutor-admin-course-delete");
  for (let course of deleteCourse) {
    course.onclick = async (e) => {
      if (confirm("Do you want to delete this course?")) {
        const id = e.currentTarget.dataset.id;
        const formData = new FormData();
        formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
        formData.set("id", id);
        formData.set("action", "tutor_course_delete");
        const post = await ajaxHandler(formData);
        const response = await post.json();
        if (response) {
          tutor_toast(__("Delete", "tutor"), __("Course has been deleted ", "tutor"), "success");
          e.target.closest("tr").remove();
        } else {
          tutor_toast(__("Failed", "tutor"), __("Course delete failed ", "tutor"), "error");
        }
      }
    };
  }
  /**
   * Handle ajax request show toast message on success | failure
   *
   * @param {*} formData including action and all form fields
   */
  async function ajaxHandler(formData) {
    try {
      const post = await fetch(window._tutorobject.ajaxurl, {
        method: "POST",
        body: formData,
      });
      return post;
    } catch (error) {
      tutor_toast(__("Operation failed", "tutor"), error, "error");
    }
  }
});
