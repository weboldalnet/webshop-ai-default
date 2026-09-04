@push('scripts')
<script>
    $(function () {
        var SORT_URL = '{{ route('admin.webshop.extra-settings.home-page.blocks.sort') }}';

        /* --------------------------------------------------------------
           TinyMCE — LUSTA indítás.
           A megosztott include a .js-tinymce-re oldalbetöltéskor köt, a mezőink
           viszont összecsukott collapse-ban vannak: ott a szerkesztő 0 magasan
           jönne létre. Ezért saját osztály (.js-home-tinymce) és kézi indítás,
           amikor a blokk kinyílik.
           -------------------------------------------------------------- */
        function initHomeTinymce(id) {
            if (typeof tinymce === 'undefined' || tinymce.get(id)) {
                return;
            }

            tinymce.init({
                selector: '#' + id,
                language: 'hu_HU',
                min_height: 260,
                menubar: false,
                plugins: 'autoresize paste preview media table advtable link image imagetools code advcode autolink lists advlist anchor emoticons fullscreen hr charmap',
                toolbar: 'undo redo | bold italic underline | formatselect | alignleft aligncenter alignright | bullist numlist | forecolor backcolor removeformat | link image table | code fullscreen',
                toolbar_mode: 'sliding',
                entity_encoding: 'raw',
                verify_html: false,
                images_upload_url: '/image-upload',
                images_upload_credentials: true
            });
        }

        // Kinyitáskor indítjuk a szerkesztőt (egyszer)
        $('#ws-home-blocks').on('shown.bs.collapse', function (e) {
            $(e.target).find('.js-home-tinymce').each(function () {
                initHomeTinymce(this.id);
            });
        });

        // A már nyitva érkező blokkban (új felvétel / mentés után) is
        $('#ws-home-blocks .collapse.show').find('.js-home-tinymce').each(function () {
            initHomeTinymce(this.id);
        });

        /* --------------------------------------------------------------
           Blokkok átrendezése.
           SAJÁT sortable, nem a WebshopAdmin.initSortable: annak
           find('.js-sort-id') leszármazott-keresése összekeverné a blokk- és a
           tétel-azonosítókat, a helper: ui.clone() pedig a TinyMCE iframe-jét
           klónozná.
           -------------------------------------------------------------- */
        if ($.fn.sortable) {
            $('#ws-home-blocks').sortable({
                items: '> .js-home-block',
                handle: '.js-hb-handle',
                axis: 'y',
                cursor: 'move',
                opacity: 0.7,
                tolerance: 'pointer',
                forcePlaceholderSize: true,

                // Egy <iframe> DOM-áthelyezése újratölti azt, és a TinyMCE tartalma
                // elveszne. Ezért mentjük vissza a textareába, és lebontjuk.
                start: function (e, ui) {
                    ui.item.find('.js-home-tinymce').each(function () {
                        var ed = (typeof tinymce !== 'undefined') ? tinymce.get(this.id) : null;
                        if (ed) {
                            ed.save();
                            ed.remove();
                        }
                    });
                },
                stop: function (e, ui) {
                    ui.item.find('.js-home-tinymce').each(function () {
                        if ($(this).closest('.collapse').hasClass('show')) {
                            initHomeTinymce(this.id);
                        }
                    });
                },
                update: function () {
                    var ids = $('#ws-home-blocks > .js-home-block .js-hb-id').map(function () {
                        return this.value;
                    }).get();

                    $.post(SORT_URL, { orderedIds: ids }).done(function (r) {
                        if (window.WebshopAdmin && WebshopAdmin.showToast) {
                            WebshopAdmin.showToast('success', r.message);
                        }
                    });
                }
            });
        }

        /* --------------------------------------------------------------
           Tétel-listák (termékek / kategóriák) egy blokkon belül.
           Külön osztálynevek (.js-hb-item*), hogy a blokk-sortable-lel ne
           keveredjen; a sorrend a rejtett inputok DOM-sorrendje, az űrlappal megy.
           -------------------------------------------------------------- */
        function initItemSortable($lista) {
            if (!$.fn.sortable || $lista.data('hbSortable')) {
                return;
            }
            $lista.data('hbSortable', true);
            $lista.sortable({
                items: '> .js-hb-item',
                handle: '.js-hb-item-handle',
                axis: 'y',
                tolerance: 'pointer',
                // Nehogy a külső blokk-sortable is lefusson rá
                update: function (e) { e.stopPropagation(); }
            });
        }

        $('.js-hb-list').each(function () { initItemSortable($(this)); });

        $(document).on('click', '.js-hb-item-remove', function () {
            $(this).closest('.js-hb-item').remove();
        });

        function itemSor(id, nev, kepUrl, mezoNev) {
            var kep = kepUrl
                ? '<img src="' + kepUrl + '" alt="" class="mr-2 rounded border" style="width:40px;height:40px;object-fit:cover;">'
                : '';

            return $(
                '<div class="d-flex align-items-center border rounded p-2 mb-1 js-hb-item">' +
                '<span class="js-hb-item-handle mr-2 text-muted" style="cursor:move;"><i class="fa fa-grip-vertical"></i></span>' +
                kep +
                '<span class="mr-auto"></span>' +
                '<input type="hidden" name="' + mezoNev + '" value="' + id + '">' +
                '<button type="button" class="btn btn-sm btn-outline-danger js-hb-item-remove"><i class="fa fa-times"></i></button>' +
                '</div>'
            ).find('.mr-auto').text(nev).end();   // .text() -> nem lehet HTML-injektálás a névből
        }

        function listaBlokkhoz(blockId) {
            return $('.js-hb-list[data-block="' + blockId + '"]');
        }

        function marBenneVan($lista, id) {
            var talalt = false;
            $lista.find('input[type=hidden]').each(function () {
                if (String(this.value) === String(id)) { talalt = true; }
            });
            return talalt;
        }

        /* Kategória hozzáadása */
        $(document).on('click', '.js-hb-cat-add', function () {
            var blockId = $(this).data('block');
            var $select = $('.js-hb-cat-select[data-block="' + blockId + '"]');
            var id = $select.val();

            if (!id) { return; }

            var $lista = listaBlokkhoz(blockId);
            if (marBenneVan($lista, id)) { return; }

            $lista.append(itemSor(id, $select.find('option:selected').text().trim(), null, 'category_ids[]'));
            initItemSortable($lista);
            $select.val('');
        });

        /* Termékkereső */
        var keresoIdozito = null;

        $(document).on('input', '.js-hb-prod-search', function () {
            var $mezo = $(this);
            var blockId = $mezo.data('block');
            var $talalatok = $('.js-hb-prod-results[data-block="' + blockId + '"]');
            var kifejezes = $mezo.val().trim();

            clearTimeout(keresoIdozito);

            if (kifejezes.length < 2) {
                $talalatok.addClass('d-none').empty();
                return;
            }

            keresoIdozito = setTimeout(function () {
                $.get($mezo.data('url'), { q: kifejezes }).done(function (termekek) {
                    $talalatok.empty();

                    if (!termekek || !termekek.length) {
                        $talalatok.append($('<div class="list-group-item text-muted"></div>').text('Nincs találat.'));
                        $talalatok.removeClass('d-none');
                        return;
                    }

                    termekek.forEach(function (t) {
                        var $sor = $('<button type="button" class="list-group-item list-group-item-action d-flex align-items-center"></button>');

                        if (t.primary_image) {
                            $sor.append($('<img alt="" class="mr-2 rounded border" style="width:32px;height:32px;object-fit:cover;">').attr('src', t.primary_image));
                        }

                        $sor.append($('<span></span>').text(t.name + (t.sku ? ' (' + t.sku + ')' : '')));
                        $sor.on('click', function () {
                            var $lista = listaBlokkhoz(blockId);
                            if (!marBenneVan($lista, t.id)) {
                                $lista.append(itemSor(t.id, t.name, t.primary_image, 'product_ids[]'));
                                initItemSortable($lista);
                            }
                            $mezo.val('');
                            $talalatok.addClass('d-none').empty();
                        });

                        $talalatok.append($sor);
                    });

                    $talalatok.removeClass('d-none');
                });
            }, 250);
        });

        // Kattintás a találati listán kívülre -> bezárás
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.js-hb-prod-search, .js-hb-prod-results').length) {
                $('.js-hb-prod-results').addClass('d-none').empty();
            }
        });

        /* --------------------------------------------------------------
           Színválasztók.
           A tárolt érték a SZÖVEGES mezőben van, hogy üresen is hagyható legyen
           (a natív <input type="color"> sosem üres). A választó megnyitáskor a
           mező jelenlegi értékére áll, nem a legutóbb használt színre.
           -------------------------------------------------------------- */
        $(document).on('mousedown', '.js-hb-color-pick', function () {
            var $szoveg = $($(this).data('target'));
            var ertek = ($szoveg.val() || '').trim();

            if (/^#[0-9a-fA-F]{6}$/.test(ertek)) {
                this.value = ertek;
            }
        });

        $(document).on('input change', '.js-hb-color-pick', function () {
            $($(this).data('target')).val(this.value);
        });

        $(document).on('click', '.js-hb-color-clear', function () {
            $($(this).data('target')).val('');
        });

        /* Aktív kapcsoló és törlés a megosztott segédekkel */
        if (window.WebshopAdmin) {
            if (WebshopAdmin.initToggleActive) { WebshopAdmin.initToggleActive(); }
            if (WebshopAdmin.initDeleteConfirm) { WebshopAdmin.initDeleteConfirm(); }
        }
    });
</script>
@endpush
