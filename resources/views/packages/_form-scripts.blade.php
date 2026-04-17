<script src="{{ asset('assets/libs/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(function () {
    var $priceDisplay = $('#price-display');
    var $priceRaw = $('#price-raw');
    var $photoCount = $('#photo_count');
    var $printSize = $('#print_size');
    var $templateCards = $('.template-card');
    var $selectedPreview = $('#selected-template-preview');
    var $selectedCount = $('#selected-template-count');
    var $templateMatchHint = $('#template-match-hint');

    $priceDisplay.inputmask('numeric', {
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: true,
        radixPoint: ',',
        placeholder: '0',
    }).on('input keyup change', function () {
        $priceRaw.val($priceDisplay.inputmask('unmaskedvalue'));
    });

    if ($priceRaw.val()) {
        $priceDisplay.val($priceRaw.val()).trigger('keyup');
    }

    function normalizePrintSize(value) {
        return String(value || '').trim().toUpperCase();
    }

    function selectedTemplates() {
        return $('.template-checkbox:checked').map(function () {
            return $(this).closest('.template-card');
        }).get();
    }

    function renderSelectedPreview() {
        var selected = selectedTemplates();
        $selectedCount.text(selected.length);

        if (!selected.length) {
            $selectedPreview.html('<div class="selected-empty text-muted" id="selected-template-empty">Belum ada template dipilih.</div>');
            return;
        }

        var items = selected.map(function (cardEl) {
            var $card = $(cardEl);
            var name = $card.data('template-name');
            var size = $card.data('template-size');
            var slots = $card.data('template-slots');
            var preview = $card.data('template-preview');
            var thumbHtml = preview
                ? '<img src="' + preview + '" alt="' + name + '">'
                : '<div class="selected-empty-thumb"><i class="mdi mdi-image-outline"></i></div>';

            return '' +
                '<div class="selected-item">' +
                    thumbHtml +
                    '<div>' +
                        '<h6>' + name + '</h6>' +
                        '<p>' + size + ' · ' + slots + ' slot</p>' +
                    '</div>' +
                '</div>';
        });

        $selectedPreview.html(items.join(''));
    }

    function filterTemplateOptions() {
        var photoCount = parseInt($photoCount.val() || '0', 10);
        var printSize = normalizePrintSize($printSize.val());
        var compatibleCount = 0;

        $templateCards.each(function () {
            var $card = $(this);
            var $checkbox = $card.find('.template-checkbox');
            var templateSlots = parseInt($checkbox.data('photo-slots') || '0', 10);
            var templateSize = normalizePrintSize($checkbox.data('print-size'));
            var reasons = [];
            var isCompatible =
                (!photoCount || templateSlots === photoCount) &&
                (!printSize || templateSize === printSize);

            if (photoCount && templateSlots !== photoCount) {
                reasons.push('Slot harus ' + templateSlots);
            }

            if (printSize && templateSize !== printSize) {
                reasons.push('Ukuran harus ' + templateSize);
            }

            $checkbox.prop('disabled', !isCompatible);
            $card.toggleClass('incompatible', !isCompatible);

            if (!isCompatible && $checkbox.is(':checked')) {
                $checkbox.prop('checked', false);
            }

            var $statusBadge = $card.find('.template-status-badge');
            var $reason = $card.find('.template-reason');
            $statusBadge.text(isCompatible ? 'Cocok' : 'Tidak cocok');
            $reason.text(isCompatible ? '' : reasons.join(' • '));

            if (isCompatible) {
                compatibleCount += 1;
            }
        });

        $('.template-card').each(function () {
            var $card = $(this);
            $card.toggleClass('selected', $card.find('.template-checkbox').is(':checked'));
        });

        renderSelectedPreview();

        if (!photoCount || !printSize) {
            $templateMatchHint
                .removeClass('text-danger')
                .addClass('text-muted')
                .text('Pilih jumlah foto dan ukuran cetak untuk melihat template yang cocok.');
            return;
        }

        if (compatibleCount === 0) {
            $templateMatchHint
                .removeClass('text-muted')
                .addClass('text-danger')
                .text('Belum ada template yang cocok dengan kombinasi ini. Ubah jumlah foto atau ukuran cetak.');
            return;
        }

        $templateMatchHint
            .removeClass('text-danger')
            .addClass('text-muted')
            .text('Ditemukan ' + compatibleCount + ' template yang cocok.');
    }

    $(document).on('click', '.template-card', function (event) {
        if ($(event.target).is('input, label')) {
            return;
        }

        var $card = $(this);
        var $checkbox = $card.find('.template-checkbox');
        if ($checkbox.is(':disabled')) {
            return;
        }

        $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
    });

    $(document).on('change', '.template-checkbox', function () {
        var $card = $(this).closest('.template-card');
        $card.toggleClass('selected', $(this).is(':checked'));
        renderSelectedPreview();
    });

    $photoCount.on('input change', filterTemplateOptions);
    $printSize.on('change', filterTemplateOptions);

    filterTemplateOptions();
});
</script>
