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
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
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
     * Admin rendelés létrehozás JS
     */
    function initAdminOrderCreate() {
        const self = this;
        
        // Termék kiválasztása datalist-ből
        $(document).on('input', '.js-admin-order-product-search', function () {
            const val = $(this).val();
            const $list = $('#' + $(this).attr('list'));
            const $option = $list.find('option').filter(function() {
                return $(this).val() === val;
            });

            if ($option.length) {
                const id = $option.data('id');
                const price = $option.data('price');
                const name = $option.val();
                
                $('.js-admin-order-product-id').val(id);
                $('.js-admin-order-product-price').val(price);
            } else {
                $('.js-admin-order-product-id').val('');
                $('.js-admin-order-product-price').val('');
            }
        });

        // Termék hozzáadása
        $(document).on('click', '.js-admin-order-add-item', function () {
            const id = $('.js-admin-order-product-id').val();
            const name = $('.js-admin-order-product-search').val();
            const price = parseFloat($('.js-admin-order-product-price').val());
            const qty = parseInt($('.js-admin-order-product-qty').val()) || 1;

            if (!id || !name) {
                alert('Kérjük, válasszon egy terméket a listából!');
                return;
            }

            // Ellenőrizzük, hogy benne van-e már
            const $existingRow = $(`.js-admin-order-item-row[data-id="${id}"]`);
            if ($existingRow.length) {
                const $qtyInput = $existingRow.find('.js-admin-order-item-qty');
                const newQty = parseInt($qtyInput.val()) + qty;
                $qtyInput.val(newQty).trigger('change');
            } else {
                const rowHtml = `
                    <tr class="js-admin-order-item-row" data-id="${id}">
                        <td>
                            <input type="hidden" name="items[${id}][product_id]" value="${id}">
                            <input type="hidden" name="items[${id}][name]" value="${name}">
                            ${name}
                        </td>
                        <td>
                            <input type="number" name="items[${id}][quantity]" class="form-control form-control-sm js-admin-order-item-qty" value="${qty}" min="1" style="width: 80px;">
                        </td>
                        <td class="js-price-col">
                            <input type="hidden" class="js-admin-order-item-price" value="${price}">
                            ${hufFormat(price)}
                        </td>
                        <td class="js-price-col">
                            <span class="js-admin-order-line-total font-weight-bold">${hufFormat(price * qty)}</span>
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-danger js-admin-order-item-remove"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('.js-admin-order-items').append(rowHtml);
            }

            // Reset search
            $('.js-admin-order-product-search').val('');
            $('.js-admin-order-product-id').val('');
            $('.js-admin-order-product-price').val('');
            $('.js-admin-order-product-qty').val(1);
            
            updateGrandTotal();
        });

        // Darabszám módosítás
        $(document).on('change keyup', '.js-admin-order-item-qty', function () {
            const $row = $(this).closest('.js-admin-order-item-row');
            const qty = parseInt($(this).val()) || 0;
            const price = parseFloat($row.find('.js-admin-order-item-price').val()) || 0;
            
            $row.find('.js-admin-order-line-total').text(hufFormat(price * qty));
            updateGrandTotal();
        });

        // Törlés
        $(document).on('click', '.js-admin-order-item-remove', function () {
            $(this).closest('.js-admin-order-item-row').remove();
            updateGrandTotal();
        });

        function updateGrandTotal() {
            let total = 0;
            $('.js-admin-order-item-row').each(function () {
                const qty = parseInt($(this).find('.js-admin-order-item-qty').val()) || 0;
                const price = parseFloat($(this).find('.js-admin-order-item-price').val()) || 0;
                total += qty * price;
            });
            $('.js-admin-order-grand-total').text(hufFormat(total));
        }
    }

    /**
     * HUF formázás JS
     */
    function hufFormat(amount) {
        return new Intl.NumberFormat('hu-HU', { style: 'currency', currency: 'HUF', minimumFractionDigits: 0 }).format(amount);
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
            var $btnStart = $(this).closest('.js-gallery-upload-container').find('.js-gallery-upload-start');
            if (files.length > 0) {
                $btnStart.show();
            } else {
                $btnStart.hide();
            }
        });

        $(document).on('click', '.js-gallery-upload-start', function () {
            var $btnStart = $(this);
            var $section = $btnStart.closest('.js-gallery-upload-container');
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
            $input.next('label').removeClass('font-weight-bold').text('Kép feltöltése folyamatban..');
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
                        $input.next('label').text('Képek feltöltve!');
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
     * Rendelés részletek modal megnyitása és státusz módosítás
     */
    function initOrderDetails() {
        $(document).on('click', '.js-order-details', function () {
            var $btn = $(this);
            var url = $btn.data('url');
            var $modal = $('#orderDetailsModal');
            var $content = $modal.find('.js-order-details-content');

            // Alapállapot visszaállítása (spinner)
            $content.html('<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Betöltés...</span></div><p class="mt-2">Rendelés adatai betöltése...</p></div>');
            $modal.modal('show');

            $.ajax({
                url: url,
                method: 'GET',
                success: function (res) {
                    if (res.success) {
                        $content.html(res.html);
                    } else {
                        $modal.modal('hide');
                        showToast('error', res.message || 'Hiba történt.');
                    }
                },
                error: function () {
                    $modal.modal('hide');
                    showToast('error', 'Hiba történt a részletek betöltésekor.');
                }
            });
        });

        // Státusz módosítás AJAX
        $(document).on('submit', '.js-order-status-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var url = $form.attr('action');
            var data = $form.serialize();

            $btn.prop('disabled', true).text('Mentés...');

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function (res) {
                    if (res.success) {
                        showToast('success', res.message);
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast('error', res.message || 'Hiba történt.');
                        $btn.prop('disabled', false).text('Mentés');
                    }
                },
                error: function (xhr) {
                    var msg = 'Hiba történt.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    showToast('error', msg);
                    $btn.prop('disabled', false).text('Mentés');
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

    /**
     * Termék kapcsolatok (kapcsolódó és variáció) datalist kezelése
     */
    function initProductRelationPicker(config) {
        const searchUrl = config.searchUrl;
        const currentProductId = config.currentProductId;

        function initPicker(type) {
            const $input = $(`.js-${type}-product-input`);
            const $idInput = $(`.js-${type}-product-id`);
            const $btnAdd = $(`.js-add-${type}-product`);
            const $results = $(`.js-${type}-product-results`);
            const $list = $(`.js-${type}-products-list`);
            let searchTimer;

            $input.on('input', function() {
                const q = $(this).val();
                clearTimeout(searchTimer);
                $idInput.val('');
                $btnAdd.prop('disabled', true);

                if (q.length < 2) {
                    $results.hide();
                    return;
                }

                searchTimer = setTimeout(function() {
                    $.get(searchUrl, { q: q, exclude_id: currentProductId, is_variation: type === 'variation' ? 1 : 0 }, function(products) {
                        if (products.length > 0) {
                            let html = '<ul class="list-group shadow-sm">';
                            products.forEach(p => {
                                html += `<li class="list-group-item list-group-item-action p-2 js-search-result" 
                                            data-id="${p.id}" 
                                            data-name="${p.name}"
                                            data-sku="${p.sku || ''}"
                                            data-category="${p.category_name}"
                                            data-img="${p.primary_image || ''}">
                                            <div class="d-flex align-items-center">
                                                <img src="${p.primary_image || ''}" class="img-fluid mr-2" style="width:30px;height:30px;object-fit:cover">
                                                <div class="lh-12">
                                                    <div class="fw-600 fs-14">${p.name}</div>
                                                    <small class="text-muted">${p.category_name} ${p.sku ? '| ' + p.sku : ''}</small>
                                                </div>
                                            </div>
                                        </li>`;
                            });
                            html += '</ul>';
                            $results.html(html).show();
                        } else {
                            $results.hide();
                        }
                    });
                }, 300);
            });

            $results.on('click', '.js-search-result', function() {
                const p = $(this).data();
                $input.val(p.name);
                $idInput.val(p.id);
                $btnAdd.prop('disabled', false).data('product', p);
                $results.hide();
            });

            $btnAdd.on('click', function() {
                const p = $(this).data('product');
                if (!p) return;

                if ($list.find(`[data-id="${p.id}"]`).length > 0) {
                    showToast('error', 'Ez a termék már szerepel a listában.');
                    return;
                }

                const rowHtml = `
                    <div class="ws-relation-row js-${type}-product-row d-flex align-items-center justify-content-between p-2 mb-1 border rounded bg-light" data-id="${p.id}">
                        <div class="d-flex align-items-center">
                            <img src="${p.img}" class="img-fluid mr-2" style="width:30px;height:30px;object-fit:cover">
                            <div>
                                <div class="fw-600 lh-1">${p.name}</div>
                                <small class="text-muted">${p.category} ${p.sku ? '| ' + p.sku : ''}</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger js-remove-${type}-product"><i class="fa fa-trash-alt"></i></button>
                        <input type="hidden" name="${type === 'related' ? 'related_product_ids[]' : 'variation_product_ids[]'}" value="${p.id}">
                    </div>
                `;
                $list.append(rowHtml);
                $input.val('');
                $idInput.val('');
                $btnAdd.prop('disabled', true).removeData('product');
            });

            $list.on('click', `.js-remove-${type}-product`, function() {
                if (confirm('Biztosan eltávolítod ezt a kapcsolatot?')) {
                    $(this).closest(`.js-${type}-product-row`).remove();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest(`.js-${type}-product-input, .js-${type}-product-results`).length) {
                    $results.hide();
                }
            });
        }

        initPicker('related');
        initPicker('variation');
    }

    return {
        initSortable: initSortable,
        initToggleActive: initToggleActive,
        initToggleCompleted: initToggleCompleted,
        initDeleteConfirm: initDeleteConfirm,
        initFilterType: initFilterType,
        initGalleryUpload: initGalleryUpload,
        initOrderDetails: initOrderDetails,
        initAdminOrderCreate: initAdminOrderCreate,
        initProductRelationPicker: initProductRelationPicker,
        showToast: showToast
    };

})(jQuery);
