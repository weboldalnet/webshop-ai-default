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
    function initSortable(selector, url, axis) {
        var $el = $(selector);
        var isGrid = $el.hasClass('row');
        
        $el.sortable({
            handle: '.ws-drag-handle',
            axis: axis || (isGrid ? false : 'y'),
            cursor: 'move',
            opacity: 0.7,
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            placeholder: 'ui-sortable-placeholder' + (isGrid ? ' col-md-3 col-6' : ''),
            containment: 'parent',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.outerHeight());
                if (isGrid) {
                    ui.placeholder.width(ui.item.outerWidth());
                }
            },
            helper: function(e, ui) {
                // Megőrizzük a szélességet a segédelem számára (grid és táblázat esetén is)
                var $helper = ui.clone();
                $helper.width(ui.outerWidth());
                
                if (!isGrid) {
                    // Táblázat esetén a belső cellák szélességét is fixáljuk
                    var $originals = ui.children();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).outerWidth());
                    });
                }
                return $helper;
            },
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
     * Galéria kép feltöltés AJAX
     */
    function initGalleryUpload(inputSelector, containerSelector) {
        $(document).on('change', inputSelector, function () {
            var files = $(this)[0].files;
            var $btnStart = $(this).closest('.form-inline').find('.js-gallery-upload-start');
            if (files.length > 0) {
                $btnStart.show();
            } else {
                $btnStart.hide();
            }
        });

        $(document).on('click', '.js-gallery-upload-start', function () {
            var $btnStart = $(this);
            var $section = $btnStart.closest('.form-inline');
            var $input = $section.find(inputSelector);
            var url = $input.data('url');
            var $container = $(containerSelector);
            var $status = $section.find('.js-gallery-upload-status');
            var $count = $section.find('.js-gallery-upload-count');
            var $total = $section.find('.js-gallery-upload-total');
            var files = $input[0].files;

            if (files.length === 0) return;

            $status.show();
            $btnStart.hide();
            $input.prop('disabled', true);
            $total.text(files.length);
            $count.text(0);

            var uploadSequence = function (index) {
                if (index >= files.length) {
                    $status.hide();
                    $input.prop('disabled', false);
                    $input.val('');
                    return;
                }

                $count.text(index + 1);
                var formData = new FormData();
                formData.append('gallery_image', files[index]);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $container.append(res.html);
                        } else {
                            showToast('error', res.message || 'Hiba történt a feltöltés során.');
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Hiba történt.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        showToast('error', msg);
                    },
                    complete: function () {
                        uploadSequence(index + 1);
                    }
                });
            };

            uploadSequence(0);
        });

        // Galéria törlés AJAX
        $(document).on('click', '.js-delete-gallery-item', function () {
            if (!confirm('Biztosan törlöd a képet?')) return;

            var $btn = $(this);
            var url = $btn.data('url');
            var id = $btn.data('id');

            $.ajax({
                url: url,
                method: 'DELETE',
                success: function (res) {
                    if (res.success) {
                        $('#galleryItem' + id).remove();
                        showToast('success', res.message);
                    }
                },
                error: function () {
                    showToast('error', 'Hiba történt a törlés során.');
                }
            });
        });

        // Alt szöveg modal megnyitása
        $(document).on('click', '.js-edit-gallery-alt', function () {
            var $btn = $(this);
            var id = $btn.data('id');
            var alt = $btn.attr('data-alt'); // attr-t használunk, hogy a dinamikusan frissített értéket is megkapjuk

            $('#js-gallery-alt-id').val(id);
            $('#js-gallery-alt-input').val(alt);
            $('#galleryAltModal').modal('show');
        });

        // Alt szöveg mentése AJAX
        $(document).on('click', '.js-save-gallery-alt', function () {
            var $btn = $(this);
            var url = $btn.data('url');
            var id = $('#js-gallery-alt-id').val();
            var alt = $('#js-gallery-alt-input').val();

            $btn.prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: { id: id, alt: alt },
                success: function (res) {
                    if (res.success) {
                        // Frissítjük a gombban tárolt alt-ot
                        $('.js-edit-gallery-alt[data-id="' + id + '"]').attr('data-alt', alt);
                        $('#galleryAltModal').modal('hide');
                        showToast('success', res.message);
                    } else {
                        showToast('error', res.message);
                    }
                },
                error: function () {
                    showToast('error', 'Hiba történt a mentés során.');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
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
        initGalleryUpload: initGalleryUpload,
        showToast: showToast
    };

})(jQuery);
