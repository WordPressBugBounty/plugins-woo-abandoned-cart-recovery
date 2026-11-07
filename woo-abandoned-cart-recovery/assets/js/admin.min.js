'use strict';
jQuery(document).ready(function ($) {
    $('.vi-ui.tabular.menu .item').tab({
        history: true,
        historyType: 'hash'
    });

    $('.vi-ui.accordion').accordion();

    function wacvControlSelector(name) {
        return '.wacv-' + String(name).replace(/_/g, '-');
    }

    function wacvControlOn(name) {
        let $el = $(wacvControlSelector(name)).filter('input, select, textarea');
        if (!$el.length) {
            return false;
        }
        if ($el.is(':checkbox')) {
            if (!$el.is(':checked')) {
                return false;
            }
        } else if ($el.is(':radio')) {
            $el = $el.filter(':checked');
            if (!$el.length) {
                return false;
            }
        }
        let $ownerRow = $el.closest('tr[data-wacv-depends]');
        if ($ownerRow.length) {
            return wacvRowShouldShow($ownerRow);
        }
        return true;
    }

    function wacvRowShouldShow($row) {
        let deps = String($row.attr('data-wacv-depends') || '').split(',').map(function (s) {
            return $.trim(s);
        }).filter(Boolean);
        if (!deps.length) {
            return true;
        }
        for (let i = 0; i < deps.length; i++) {
            if (wacvControlOn(deps[i])) {
                return true;
            }
        }
        return false;
    }

    function wacvToggleDepends() {
        $('[data-wacv-depends]').each(function () {
            let $row = $(this);
            $row.toggle(wacvRowShouldShow($row));
        });
    }

    wacvToggleDepends();
    $('#wacv-admin-settings').on('change', 'input[type=checkbox], input[type=radio], select', wacvToggleDepends);

    $('.wacv-order-stt, .wacv-sms-order-stt').select2({});

    $('.wacv-tracking-user-exclude').select2({
        width: '100%',
        placeholder: 'Select people who won\'t be tracked cart',
        ajax: {
            url: wacv_ls.ajax_url + '?action=wacv_search&param=user&nonce=' + wacv_ls.nonce,
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term,
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true,
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 2,
        allowClear: true,
    });


//Email rules
    addRules('email_rules');
    addRules('abd_orders');

    function addRules(slug) {
        $('.wacv-add-' + slug).on('click', function () {
            let mlg_select = '';
            if (wacv_ml) {
                for (let i = 0; i < wacv_ml.length; i++) {
                    mlg_select += '<p class="wacv-mlg-label">' + wacv_ml[i] + ': </p>';
                    mlg_select += '<select name="wacv_params[' + slug + '][template_'+ wacv_ml[i] +'][]"' +
                        'class="wacv-select-email-template">' +
                        wacvEmailTemplatesList.map(listCp) +
                        '</select>'
                }
            }
            var row = '   <tr class="wacv-' + slug + '-row-target">' +
                '                            <td class="">' +
                '                                <input type="number" name="wacv_params[' + slug + '][time_to_send][]"' +
                '                                       class=""' +
                '                                       value="" min="1">' +
                '                            </td>' +
                '                            <td class="">' +
                '                                <select name="wacv_params[' + slug + '][unit][]"' +
                '                                        class="">' +
                '                                    <option value="minutes">minutes</option>' +
                '                                    <option value="hours">hours</option>' +
                '                                </select>' +
                '                            </td>' +
                '                            <td class="">' +
                '                                <select name="wacv_params[' + slug + '][template][]"' +
                '                                        class="wacv-select-email-template">' +
                wacvEmailTemplatesList.map(listCp) +
                '                                </select>' + mlg_select +
                '                            </td>' +
                '                            <td align="center" class="">' +
                '                                <button class="wacv-delete-' + slug + ' vi-ui small icon red button" type="button">' +
                '                                    <i class="trash icon"> </i>' +
                '                                </button>' +
                '                            </td>' +
                '                        </tr>';
            $('.wacv-' + slug + '-row-target').last().after(row);
            delete_rule('wacv-delete-' + slug);
        });
    }

    delete_rule('wacv-delete-email_rules');
    delete_rule('wacv-delete-abd_orders');

    function listCp(item) {
        return '<option value="' + item.id + '">' + item.value + '</option>';
    }

//wacv-delete-email-rule
    function delete_rule(target) {
        $('.' + target).on('click', function () {
            $(this).parents().eq(1).remove();
        });
    }

    $('.wacv-save-settings').on('click', function () {
        $(this).addClass('loading');
    });


});