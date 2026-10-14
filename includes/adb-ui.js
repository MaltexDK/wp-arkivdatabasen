/* Local UI: no CDN dependencies or global form names. */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.adb-search').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.elements.string.value.trim()) {
                event.preventDefault();
                form.elements.string.focus();
                form.elements.string.setCustomValidity('En søgning kan ikke være tom');
                form.elements.string.reportValidity();
            }
        });
        form.elements.string.addEventListener('input', function () { this.setCustomValidity(''); });
    });
    document.querySelectorAll('.adb-results').forEach(function (table) {
        var header = table.querySelector('[data-sort="' + table.dataset.sort + '"]');
        if (header && window.sorttable) { sorttable.innerSortFunction.call(header); }
        table.querySelectorAll('[data-sort]').forEach(function (cell) {
            cell.addEventListener('click', function () {
                table.closest('.adb-container').querySelectorAll('[name="sortering"]').forEach(function (input) { input.value = cell.dataset.sort; });
            });
        });
    });
    document.querySelectorAll('[data-adb-lightbox]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            if (typeof HTMLDialogElement === 'undefined') { return; }
            event.preventDefault();
            var dialog = document.createElement('dialog');
            var image = document.createElement('img');
            var close = document.createElement('button');
            image.src = link.href;
            image.alt = link.querySelector('img').alt;
            close.textContent = 'Luk';
            close.type = 'button';
            close.addEventListener('click', function () { dialog.close(); });
            dialog.addEventListener('close', function () { dialog.remove(); });
            dialog.append(close, image);
            dialog.className = 'adb-lightbox';
            document.body.appendChild(dialog);
            dialog.showModal();
        });
    });
});
