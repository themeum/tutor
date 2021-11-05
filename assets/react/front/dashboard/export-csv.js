document.addEventListener("DOMContentLoaded", function() {
  const { __, _x, _n, _nx } = wp.i18n;
  /**
   * Export purchase history
   *
   * @since v2.0.0
   */

  const exportPurchase = document.getElementById("tutor-export-purchase-history");
  exportPurchase.onclick = (e) => {
    const target = e.currentTarget;
    const filename = `order-${target.dataset.order}-purchase-history.csv`;
    const data = [
      {
        "Order ID ": target.dataset.order,
        "Course Name": target.dataset.courseName,
        Price: target.dataset.price,
        Date: target.dataset.date,
        Status: target.dataset.status,
      },
    ];
    exportCSV(data, filename);
  };

  /**
   * Export CSV file
   *
   * @param {*} data | data that will be used for generating CSV file
   * @param {*} filename | filename of CSV file
   * @since v2.0.0
   */
  function exportCSV(data, filename) {
    const keys = Object.keys(data[0]);
    const csvFile = [keys.join(","), data.map((row) => keys.map((key) => row[key]).join(",")).join("\n")].join("\n");
    //generate csv
    const blob = new Blob([csvFile], { type: "text/csv;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = "hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
});
