/**
 * Webshop Admin JS
 * jQuery 3.5.1 + jQuery UI 1.12 + Bootstrap 4.6
 */
var WebshopAdmin = (function ($) {
    'use strict';

    // CSRF token setup
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    /**
     * jQuery UI Sortable inicializálás
     */
    function initSortable(selector, url) {
        $(selector).sortable({
            handle: '.ws-drag-handle',
            axis: 'y',
            cursor: 'move',
            opacity: 0.7,
            update: function () {
                var orderedIds = [];
                $(selector).find('.js-sort-id').each(function () {
                    orderedIds.push($(this).val());
                });
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { orderedIds: orderedIds },
                    success: function (res) {
                        if (res.success) {
                            showToast('success', res.message || 'Sorrend mentve.');
                        }
                    },
                    error: function () {
                        showToast('error', 'Hiba történt a sorrend mentésekor.');
                    }
                });
            }
        });
    }

    /**
     * Aktív/Inaktív switch AJAX
     */
    function initToggleActive() {
        $(document).on('change', '.js-toggle-active', function () {
            var $el = $(this);
            var id = $el.data('id');
            var url = $el.data('url');
            var isActive = $el.is(':checked');

            $.ajax({
                url: url,
                method: 'POST',
                data: { id: id, is_active: isActive },
                success: function (res) {
                    if (res.success) {
                        showToast('success', res.message || 'Státusz frissítve.');
                    }
                },
                error: function () {
                    $el.prop('checked', !isActive);
                    showToast('error', 'Hiba történt.');
                }
            });
        });
    }

    /**
     * Rendelés teljesített státusz AJAX
     */
    function initToggleCompleted() {
        $(document).on('change', '.js-toggle-completed', function () {
            var $el = $(this);
            var id = $el.data('id');
            var url = $el.data('url');
            var isCompleted = $el.is(':checked');

            $.ajax({
                url: url,
                method: 'POST',
                data: { id: id, is_completed: isCompleted },
                success: function (res) {
                    if (res.success) {
                        showToast('success', res.message || 'Státusz frissítve.');
                    }
                },
                error: function () {
                    $el.prop('checked', !isCompleted);
                    showToast('error', 'Hiba történt.');
                }
            });
        });
    }

    /**
     * Törlés megerősítő modal
     */
    function initDeleteConfirm() {
        $(document).on('click', '.js-delete-btn', function () {
            var url = $(this).data('url');
            $('#deleteForm').attr('action', url);
            $('#deleteConfirmModal').modal('show');
        });
    }

    /**
     * Filter type alapján suffix mező mutatás/elrejtés
     */
    function initFilterType() {
        $(document).on('change', '.js-filter-type', function () {
            var val = $(this).val();
            if (val === 'number') {
                $('.js-suffix-group').show();
            } else {
                $('.js-suffix-group').hide();
                $('.js-suffix-group input').val('');
            }
        });
    }

    /**
     * Toast üzenet megjelenítése
     */
    function showToast(type, message) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        var $toast = $('<div class="alert ' + alertClass + ' alert-dismissible fade show ws-toast" role="alert">' +
            '<i class="fa ' + icon + '"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
        $('body').append($toast);
        setTimeout(function () { $toast.alert('close'); }, 3000);
    }

    return {
        initSortable: initSortable,
        initToggleActive: initToggleActive,
        initToggleCompleted: initToggleCompleted,
        initDeleteConfirm: initDeleteConfirm,
        initFilterType: initFilterType,
        showToast: showToast
    };

})(jQuery);
