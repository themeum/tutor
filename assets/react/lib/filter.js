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

window.onload = () => {
  document.getElementById("tutor-backend-filter-course").onchange = (e) => {
    window.location = urlPrams("course-id", e.target.value);
  };
  document.getElementById("tutor-backend-filter-order").onchange = (e) => {
    window.location = urlPrams("order", e.target.value);
  };
  document.getElementById("tutor-backend-filter-date").onchange = (e) => {
    window.location = urlPrams("date", e.target.value);
  };
  document.getElementById("tutor-admin-search-filter-form").onsubmit = (e) => {
    e.preventDefault();
    const search = document.getElementById("tutor-backend-filter-search").value;
    window.location = urlPrams("search", search);
  };

  /**
   * Onsubmit bulk form handle ajax request then reload page
   */
  const bulkForm = document.getElementById("tutor-admin-bulk-action-form");
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
    formData.set("bulk-ids", bulkIds);
    formData.set(
      window.tutor_get_nonce_data(true).key,
      window.tutor_get_nonce_data(true).value
    );
    if (formData.get('bulk-action') === 'bulk action') {
      alert(__('Please select an action', 'tutor'));
      return;
    }
    try {
      const post = await fetch(window._tutorobject.ajaxurl, {
        method: "POST",
        body: formData,
      });
      const response = await post.json();
      if (response.success) {
        location.reload();
      }
    } catch (error) {
      alert(error);
    }
  };

  function urlPrams(type, val) {
    const url = new URL(window.location.href);
    const params = url.searchParams;
    params.set(type, val);
    params.set('paged', 1);
    return url;
  }

  /**
   * Select all bulk checkboxes
   *
   * @since v2.0.0
   */
  const selectAll = document.querySelector("#tutor-bulk-checkbox-all");
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
};
