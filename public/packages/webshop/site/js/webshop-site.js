const WebshopSite = {
    currentUrl: null,

    init: function() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        this.initCart();
        this.initCompare();
        this.initGallery();
        this.initReviewStars();
        this.initReviewSubmit();
        this.initOther();
    },

    initCart: function() {
        const self = this;

        // Kosárba rakás
        $(document).on('click', '.js-add-to-cart', function() {
            const btn = $(this);
            const id = btn.data('id');
            const qty = btn.data('with-qty') ? $('.js-quantity-select').val() : 1;
            // Ha a gomb a related modalban van, ne nyissunk újabb modalt
            const showRelatedModal = btn.closest('#relatedProductsModal').length === 0;

            btn.prop('disabled', true);

            $.post('/webshop/cart/add', {
                product_id: id,
                quantity: qty,
                show_related_modal: showRelatedModal ? 1 : 0
            }, function(res) {
                btn.prop('disabled', false).find('.fa-spinner').remove();
                if (res.success) {
                    $('.js-cart-count').text(res.count);
                    self.updateCartDropdown();

                    if (res.related_html) {
                        $('#related-products-modal-container').remove();
                        $('body').append('<div id="related-products-modal-container">' + res.related_html + '</div>');
                        $('#relatedProductsModal').modal('show');
                    } else {
                        self.showToast('success', res.message);
                    }
                }
            });
        });

        // Kosár dropdown megnyitáskor frissítés
        $('.ws-cart-button').on('click', function() {
            self.updateCartDropdown();
        });

        // Törlés a kosárból
        $(document).on('click', '.js-remove-cart-item', function() {
            if (!confirm('Biztosan törli a terméket a kosárból?')) return;
            const id = $(this).data('id');
            const reload = $(this).data('reload');

            $.ajax({
                url: '/webshop/cart/remove',
                type: 'DELETE',
                data: { product_id: id },
                success: function(res) {
                    if (res.success) {
                        $('.js-cart-count').text(res.count);
                        if (reload) {
                            location.reload();
                        } else {
                            self.updateCartDropdown();
                        }
                    }
                }
            });
        });
    },

    updateCartDropdown: function() {
        $.get('/webshop/cart/dropdown', function(res) {
            if (res.success) {
                $('.js-cart-dropdown').html(res.html);
                $('.js-cart-count').text(res.count);
            }
        });
    },

    initCompare: function() {
        const self = this;
        $(document).on('click', '.js-add-to-compare', function() {
            const id = $(this).data('id');
            $.post('/webshop/compare/add', { product_id: id }, function(res) {
                if (res.success) {
                    $('.js-compare-count').text(res.count);
                    self.updateCompareDropdown();
                    self.showToast('success', res.message);
                } else {
                    self.showToast('error', res.message);
                }
            });
        });

        // Kosár dropdown megnyitáskor frissítés
        $('.ws-compare-button').on('click', function() {
            self.updateCompareDropdown();
        });

        $(document).on('click', '.js-remove-compare-item', function() {
            const id = $(this).data('id');
            const reload = $(this).data('reload');
            $.ajax({
                url: '/webshop/compare/remove',
                type: 'DELETE',
                data: { product_id: id },
                success: function(res) {
                    if (res.success) {
                        $('.js-compare-count').text(res.count);
                        if (reload) {
                            location.reload();
                        } else {
                            self.updateCompareDropdown();
                        }
                    }
                }
            });
        });
    },

    updateCompareDropdown: function() {
        $.get('/webshop/compare/dropdown', function(res) {
            if (res.success) {
                $('.js-compare-dropdown').html(res.html);
                $('.js-compare-count').text(res.count);
            }
        });
    },

    initProductList: function(apiUrl, options = {}) {
        const self = this;
        this.currentUrl = apiUrl;
        this.currentPage = 1;
        this.categoryKey = options.category_key || null;

        const defaultSort = options.sort || 'newest';
        const defaultPerPage = options.per_page || '30';
        const defaultViewMode = options.view_mode || 'card';

        // Alapértelmezett állapot beállítása a UI-on
        $('.js-sort-select').val(defaultSort);
        $('.js-per-page-select').val(defaultPerPage);
        $('.js-view-mode').removeClass('active');
        $(`.js-view-mode[data-mode="${defaultViewMode}"]`).addClass('active');

        // Nézet mód betöltése localStorage-ból (felülírja az alapértelmezettet)
        const savedViewMode = localStorage.getItem('ws_view_mode');
        if (savedViewMode) {
            $('.js-view-mode').removeClass('active');
            $(`.js-view-mode[data-mode="${savedViewMode}"]`).addClass('active');
        }

        const loadProducts = () => {
            const formData = $('#ws-filter-form').serializeArray();
            const sort = $('.js-sort-select').val();
            const perPage = $('.js-per-page-select').val();
            const viewMode = $('.js-view-mode.active').data('mode') || defaultViewMode;
            const page = self.currentPage || 1;

            // Nézet mód mentése
            localStorage.setItem('ws_view_mode', viewMode);

            let beautyParams = [];

            // Szűrők feldolgozása
            formData.forEach(item => {
                if (item.value) {
                    if (item.name.startsWith('f[')) {
                        // f[catId][] = propId -> {slug}-f{propId}
                        let $input = $(`input[name="${item.name}"][value="${item.value}"]`);
                        let slug = $input.data('slug') || '';
                        beautyParams.push((slug ? slug + '-' : '') + 'f' + item.value);
                    } else if (item.name.startsWith('n[')) {
                        // n[catId][min/max] -> n{catId}min-{val}
                        let match = item.name.match(/n\[(\d+)\]\[(min|max)\]/);
                        if (match) {
                            beautyParams.push('n' + match[1] + match[2] + '-' + item.value);
                        }
                    }
                }
            });

            // Egyéb paraméterek, csak ha nem alapértelmezettek
            if (sort && sort !== defaultSort) beautyParams.push('sort-' + sort);
            if (perPage && perPage !== defaultPerPage) beautyParams.push('per_page-' + perPage);
            if (viewMode && viewMode !== defaultViewMode) beautyParams.push('view_mode-' + viewMode);
            if (page > 1) beautyParams.push('page-' + page);

            let path = window.location.pathname.split(';')[0];
            let pathParts = path.split('/');
            let lastPart = pathParts[pathParts.length - 1];

            // Ha van categoryKey, akkor az utolsó részt lecseréljük rá (ha még nem az)
            if (self.categoryKey && lastPart !== self.categoryKey) {
                pathParts[pathParts.length - 1] = self.categoryKey;
                path = pathParts.join('/');
            }

            let newUrl = path;
            if (beautyParams.length > 0) {
                newUrl += ';' + beautyParams.join(';');
            }

            $('#product-list-container').css('opacity', 0.5);

            // AJAX híváshoz a normál formátumot használjuk a kompatibilitás miatt
            // Fontos: a view_mode-ot mindig elküldjük, hogy a szerver a megfelelő template-et adja vissza
            let queryData = {};
            formData.forEach(item => {
                if (item.value) {
                    if (item.name.endsWith('[]')) {
                        let name = item.name;
                        if (!queryData[name]) queryData[name] = [];
                        queryData[name].push(item.value);
                    } else {
                        queryData[item.name] = item.value;
                    }
                }
            });
            queryData.sort = sort;
            queryData.per_page = perPage;
            queryData.view_mode = viewMode;
            if (page > 1) queryData.page = page;

            $.get(this.currentUrl, queryData, function(res) {
                $('#product-list-container').html(res.html).css('opacity', 1);
                $('#pagination-container').html(res.pagination);

                // History API update
                window.history.pushState({path: newUrl}, '', newUrl);
            });
        };

        $(document).on('change', '.js-filter-input, .js-sort-select, .js-per-page-select', function() {
            self.currentPage = 1; // Szűréskor vissza az első oldalra
            loadProducts();
        });

        $(document).on('click', '.js-view-mode', function() {
            $('.js-view-mode').removeClass('active');
            $(this).addClass('active');
            loadProducts();
        });

        $(document).on('click', '.js-pagination-link', function(e) {
            e.preventDefault();
            self.currentPage = $(this).data('page');
            loadProducts();
            $('html, body').animate({ scrollTop: $(".ws-toolbar").offset().top - 100 }, 500);
        });

        $(document).on('click', '.js-filter-clear', function() {
            $('#ws-filter-form')[0].reset();
            self.currentPage = 1;
            loadProducts();
        });

        // Kezdő állapot szinkronizálása az URL-ből
        const initialParams = window.location.pathname.split(';');
        initialParams.forEach(p => {
            if (p.startsWith('page-')) self.currentPage = parseInt(p.split('-')[1]);
            if (p.startsWith('sort-')) $('.js-sort-select').val(p.split('-')[1]);
            if (p.startsWith('per_page-')) $('.js-per-page-select').val(p.split('-')[1]);
            if (p.startsWith('view_mode-')) {
                const vm = p.split('-')[1];
                $('.js-view-mode').removeClass('active');
                $(`.js-view-mode[data-mode="${vm}"]`).addClass('active');
            }
        });

        loadProducts(); // Initial load
    },

    initGallery: function() {
        $(document).on('click', '.js-thumb', function() {
            $('.js-thumb').removeClass('active border-primary');
            $(this).addClass('active border-primary');
            let wsMainImg = $('#ws-main-img');
            wsMainImg.attr('src', $(this).data('src'));
            wsMainImg.closest('a').attr('href', $(this).data('src'));
            createGallery();
        });
    },

    initReviewStars: function() {
        $(document).on('mouseenter', '.js-star-input', function() {
            const rating = $(this).data('rating');
            $('.js-star-input').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).removeClass('fa-regular').addClass('fa-solid');
                } else {
                    $(this).removeClass('fa-solid').addClass('fa-regular');
                }
            });
        }).on('mouseleave', '.ws-rating-input', function() {
            const selected = $('#ws-rating-value').val();
            $('.js-star-input').each(function() {
                if (selected && $(this).data('rating') <= selected) {
                    $(this).removeClass('fa-regular').addClass('fa-solid');
                } else {
                    $(this).removeClass('fa-solid').addClass('fa-regular');
                }
            });
        }).on('click', '.js-star-input', function() {
            $('#ws-rating-value').val($(this).data('rating'));
        });
    },

    initReviewSubmit: function() {
        $('#ws-review-form').on('submit', function(e) {
            e.preventDefault();
            if (!$('#ws-rating-value').val()) { alert('Kérjük, válasszon értékelést!'); return; }

            const form = $(this);
            const btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).prepend('<i class="fa fa-spinner fa-spin mr-2"></i>');

            $.post('/webshop/reviews', form.serialize(), function(res) {
                if (res.success) {
                    alert(res.message);
                    $('#reviewModal').modal('hide');
                    location.reload();
                }
            }).fail(function(err) {
                btn.prop('disabled', false).find('.fa-spinner').remove();
                alert('Hiba történt a mentés során. Kérjük, ellenőrizze az adatokat!');
            });
        });
    },

    initCheckout: function() {
        const self = this;
        $(document).on('click', '.js-qty-plus, .js-qty-minus', function() {
            const id = $(this).data('id');
            const input = $(`.js-qty-input[data-id="${id}"]`);
            let val = parseInt(input.val());

            if ($(this).hasClass('js-qty-plus')) val++;
            else val--;

            if (val < 1) return;

            input.val(val);
            $.post('/webshop/cart/update', { product_id: id, quantity: val }, function(res) {
                if (res.success) {
                    location.reload(); // Egyszerűbb, mint minden árat JS-ből újraszámolni a bonyolult layout miatt
                }
            });
        });

        // Billing collapse kezelés
        const toggleBillingRequired = (isChecked) => {
            const collapse = $('.js-billing-collapse');
            const inputs = collapse.find('.js-billing-required');

            if (isChecked) {
                collapse.collapse('hide');
                inputs.prop('required', false);
            } else {
                collapse.collapse('show');
                inputs.prop('required', true);
            }
        };

        $(document).on('change', '.js-billing-same-as-shipping', function() {
            toggleBillingRequired($(this).is(':checked'));
        });
    },

    initOther: function() {
        $(document).ready(function () {
            $('.js-category-dropdown').on('show.bs.dropdown', function () {
                $(this).closest('.category-row').addClass('visible');
            });

            $('.js-category-dropdown').on('hide.bs.dropdown', function () {
                $(this).closest('.category-row').removeClass('visible');
            });

            $('.js-show-filter-btn').on('click', function () {
                $('.ws-filter-sidebar').toggleClass('show');
                $('body').toggleClass('overflow-hidden');
            });

            $('.ws-filter-sidebar').on('click', function (e) {
                if (!$(e.target).closest('.ws-filter-box').length) {
                    $(this).removeClass('show');
                    $('body').toggleClass('overflow-hidden');
                }
            });
        });
    },

    showToast: function(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        const $toast = $('<div class="alert ' + alertClass + ' alert-dismissible fade show ws-site-toast" role="alert">' +
            '<i class="fa ' + icon + ' mr-2"></i> ' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');

        $('.ws-site-toast').remove(); // Csak egy toast legyen egyszerre
        $('body').append($toast);
        setTimeout(function () { $toast.alert('close'); }, 5000);
    }
};

$(function() {
    WebshopSite.init();
});
