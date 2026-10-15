type CsvRow = Record<string, string>;

function exportCSV(data: CsvRow[], filename: string): void {
  if (!data.length) {
    return;
  }

  const headers = Object.keys(data[0]);
  const rows = data.map((row) => headers.map((header) => `"${(row[header] ?? '').replace(/"/g, '""')}"`).join(','));

  const csv = [headers.join(','), ...rows].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');

  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

function bindBillingCsvExport(): void {
  document.querySelectorAll<HTMLElement>('.tutor-export-purchase-history').forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();

      const filename = `order-${link.dataset.order ?? ''}-purchase-history.csv`;

      const data: CsvRow[] = [
        {
          'Order ID': link.dataset.order ?? '',
          'Course Name': link.dataset.courseName ?? '',
          Price: link.dataset.price ?? '',
          Date: link.dataset.date ?? '',
          Status: link.dataset.status ?? '',
        },
      ];

      exportCSV(data, filename);
    });
  });
}

export const initBillingCsvExport = () => {
  bindBillingCsvExport();
};
