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
    },

    initCart: function() {
        const self = this;
        
        // Kosárba rakás
        $(document).on('click', '.js-add-to-cart', function() {
            const btn = $(this);
            const id = btn.data('id');
            const qty = btn.data('with-qty') ? $('.js-quantity-select').val() : 1;

            btn.prop('disabled', true).append(' <i class="fa fa-spinner fa-spin"></i>');

            $.post('/webshop/cart/add', { product_id: id, quantity: qty }, function(res) {
                btn.prop('disabled', false).find('.fa-spinner').remove();
                if (res.success) {
                    $('.js-cart-count').text(res.count);
                    self.updateCartDropdown();
                    
                    if (res.related_html) {
                        $('#related-products-modal-container').remove();
                        $('body').append('<div id="related-products-modal-container">' + res.related_html + '</div>');
                        $('#relatedProductsModal').modal('show');
                    } else {
                        alert(res.message);
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
                    alert(res.message);
                } else {
                    alert(res.message);
                }
            });
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
            $('#ws-main-img').attr('src', $(this).data('src'));
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
    }
};

$(function() {
    WebshopSite.init();
});
