/**
 * @file
 * AEO Multilingual dashboard behaviors.
 */
(function (Drupal) {
  'use strict';

  /**
   * Initialize AEO Multilingual dashboard interactions.
   */
  Drupal.behaviors.aeoMultilingualDashboard = {
    attach: function (context) {
      // Sortable table headers.
      const headers = context.querySelectorAll('.aeo-table th[data-sortable]');
      headers.forEach(function (header) {
        header.addEventListener('click', function () {
          const table = this.closest('table');
          const tbody = table.querySelector('tbody');
          const index = Array.from(this.parentNode.children).indexOf(this);
          const rows = Array.from(tbody.querySelectorAll('tr'));
          const asc = this.dataset.sort !== 'asc';

          rows.sort(function (a, b) {
            const aVal = (a.children[index] && a.children[index].textContent.trim()) || '';
            const bVal = (b.children[index] && b.children[index].textContent.trim()) || '';
            const aNum = parseInt(aVal, 10);
            const bNum = parseInt(bVal, 10);

            if (!isNaN(aNum) && !isNaN(bNum)) {
              return asc ? aNum - bNum : bNum - aNum;
            }
            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
          });

          rows.forEach(function (row) { tbody.appendChild(row); });
          this.dataset.sort = asc ? 'asc' : 'desc';
          headers.forEach(function (h) {
            if (h !== header) {
              delete h.dataset.sort;
            }
          });
        });
      });
    }
  };

}(Drupal));
