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

    initProductList: function(apiUrl) {
        const self = this;
        this.currentUrl = apiUrl;

        const loadProducts = () => {
            const formData = $('#ws-filter-form').serialize();
            const sort = $('.js-sort-select').val();
            const perPage = $('.js-per-page-select').val();
            const viewMode = $('.js-view-mode.active').data('mode') || 'card';

            const params = formData + '&sort=' + sort + '&per_page=' + perPage + '&view_mode=' + viewMode;

            $('#product-list-container').css('opacity', 0.5);

            $.get(this.currentUrl, params, function(res) {
                $('#product-list-container').html(res.html).css('opacity', 1);
                $('#pagination-container').html(res.pagination);

                // History API update
                const newUrl = window.location.pathname + '?' + params;
                window.history.pushState({path: newUrl}, '', newUrl);
            });
        };

        $(document).on('change', '.js-filter-input, .js-sort-select, .js-per-page-select', function() {
            loadProducts();
        });

        $(document).on('click', '.js-view-mode', function() {
            $('.js-view-mode').removeClass('active');
            $(this).addClass('active');
            loadProducts();
        });

        $(document).on('click', '.js-pagination-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            self.currentUrl = apiUrl + '?page=' + page;
            loadProducts();
            $('html, body').animate({ scrollTop: $(".ws-toolbar").offset().top - 100 }, 500);
        });

        $(document).on('click', '.js-filter-clear', function() {
            $('#ws-filter-form')[0].reset();
            loadProducts();
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
            });

            $('.ws-filter-sidebar').on('click', function (e) {
                if (!$(e.target).closest('.ws-filter-box').length) {
                    $(this).removeClass('show');
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
