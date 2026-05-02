<script src="{{ asset('assets/libs/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(function () {
    var $priceDisplay = $('#price-display');
    var $priceRaw = $('#price-raw');
    var $templateCards = $('.template-card');
    var $selectedPreview = $('#selected-template-preview');
    var $selectedCount = $('#selected-template-count');
    var $templateMatchHint = $('#template-match-hint');

    /* ─── Price input mask ─── */
    $priceDisplay.inputmask('numeric', {
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: true,
        radixPoint: ',',
        placeholder: '',
    }).on('input keyup change', function () {
        $priceRaw.val($priceDisplay.inputmask('unmaskedvalue'));
    });

    if ($priceRaw.val()) {
        $priceDisplay.val($priceRaw.val()).trigger('keyup');
    }

    /* ─── Chip group: jumlah cetak ─── */
    $('.pkg-chip-group').each(function () {
        var $group = $(this);
        var target = $group.data('target');
        var $hidden = $('#' + target);

        $group.find('.pkg-chip-btn').on('click', function () {
            var val = $(this).data('value');
            $group.find('.pkg-chip-btn').removeClass('active');
            $(this).addClass('active');
            $hidden.val(val);
        });
    });

    /* ─── Helpers ─── */
    function normalizePrintSize(value) {
        return String(value || '').trim().toUpperCase();
    }

    function selectedTemplates() {
        return $('.template-checkbox:checked').map(function () {
            return $(this).closest('.template-card');
        }).get();
    }

    function getSelectedSizeAndSlots() {
        var $first = $('.template-checkbox:checked').first();
        if (!$first.length) return null;
        return {
            size: normalizePrintSize($first.data('print-size')),
            slots: parseInt($first.data('photo-slots') || '0', 10),
        };
    }

    function renderSelectedPreview() {
        var selected = selectedTemplates();
        $selectedCount.text(selected.length);

        if (!selected.length) {
            $selectedPreview.html('<div class="selected-empty text-muted small" id="selected-template-empty">Belum ada template dipilih.</div>');
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
                        '<p>' + size + ' · ' + slots + ' foto</p>' +
                    '</div>' +
                '</div>';
        });

        $selectedPreview.html(items.join(''));
    }

    /* ─── Auto-disable templates with different size when one is selected ─── */
    function refreshTemplateAvailability() {
        var lock = getSelectedSizeAndSlots();

        $templateCards.each(function () {
            var $card = $(this);
            var $checkbox = $card.find('.template-checkbox');
            var size = normalizePrintSize($checkbox.data('print-size'));
            var slots = parseInt($checkbox.data('photo-slots') || '0', 10);

            var compatible = !lock || (size === lock.size && slots === lock.slots);

            $checkbox.prop('disabled', !compatible);
            $card.toggleClass('incompatible', !compatible);

            var $reason = $card.find('.template-reason');
            if (!compatible) {
                $reason.text('Beda ukuran/jumlah foto dengan template lain di paket ini');
            } else {
                $reason.text('');
            }
        });

        // Update top hint
        if (!lock) {
            $templateMatchHint
                .removeClass('hint-success hint-danger')
                .find('span').text('Pilih template untuk paket ini. Setelah memilih satu, template lain dengan ukuran berbeda akan dinonaktifkan otomatis.');
        } else {
            $templateMatchHint
                .removeClass('hint-danger')
                .addClass('hint-success')
                .find('span').text('Paket ini akan menggunakan ukuran ' + lock.size + ' dengan ' + lock.slots + ' foto. Pilih template lain dengan spesifikasi sama jika ingin variasi.');
        }
    }

    /* ─── Template card click ─── */
    $(document).on('click', '.template-card', function (event) {
        if ($(event.target).is('input')) return;
        event.preventDefault();

        var $card = $(this);
        var $checkbox = $card.find('.template-checkbox');
        if ($checkbox.is(':disabled')) return;

        $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
    });

    $(document).on('change', '.template-checkbox', function () {
        var $card = $(this).closest('.template-card');
        $card.toggleClass('selected', $(this).is(':checked'));
        renderSelectedPreview();
        refreshTemplateAvailability();
    });

    /* ─── Initial run ─── */
    renderSelectedPreview();
    refreshTemplateAvailability();
});
</script>
