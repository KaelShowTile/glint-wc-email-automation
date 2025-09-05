jQuery(function($) {

    // Localize script with nonce
    var glint_ajax_data = {
        ajax_url: ajaxurl,
        nonce: glint_email_automation.nonce
    };

    $('#triggered_by').change(function() {
        $('#product_trigger_field, #category_trigger_field').hide();
        $('#' + $(this).val() + '_trigger_field').show();
    });

    $('.glint-select2').each(function() {
        var $select = $(this);
        var isProduct = $select.attr('name') === 'product_trigger[]';
        
        $select.select2({
            ajax: {
                url: glint_ajax_data.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: isProduct ? 'glint_email_automation_search_products' : 'glint_email_automation_search_categories',
                        term: params.term,
                        security: glint_ajax_data.nonce
                    };
                },
                processResults: function(data) {
                    return { results: data };
                }
            },
            minimumInputLength: 2
        });
    });
});