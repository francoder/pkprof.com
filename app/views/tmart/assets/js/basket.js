$(document).ready(function() {
    $('body').on('click', "[data-bs-basket]", function() {
        var params = {
            id:$(this).data('id'),
            amount:$(this).data('amount')
        };
        $.post('/api/basket/add', params).done(function(data) {
            var res = JSON.parse(data);
            basket_update_wdget(res);
        });
        return false;
    });

    $('body').on('click', "[data-bs-basket-del]", function(){
        console.info($(this).data());
        basket_update_count('erase', {id:$(this).data('id')});
        return false;
    });
    
	$('body').on('click', '[data-bs-basket-plus]', function(){
        if (basket_update_count('add', {id:$(this).data('id')})) {
            var curElem = $(this).parent().find('input');
            curElem.val(curElem.val()+1);
        }
        return false;
    });

    $('body').on('click', '[data-bs-basket-minus]', function(){
        var curElem = $(this).parent().find('input'),
            col = curElem.val();
        if (col > 1) {
            if (basket_update_count('remove', {id:$(this).data('id')})) {
                col--;
                curElem.val(col);
            }
        }
        return false;
    });
    
    $('body').on('keyup', 'input[data-bs-basket-amount]', function() {
        var amount = $(this).val();
        if (!basket_update_count('change', {id:$(this).data('id'),amount:amount})) {
            basket_update_count('get', {id:$(this).data('id')});
        }
    });
    
    function basket_update_count(action, params) {
        console.info(params);
        var posting = $.post('/api/basket/'+action, params);
        posting.done(function(data) {
            var res = JSON.parse(data);
            if (res.result) {
                basket_update_wdget(res);
                var $sku = $('[data-bs-basket] [data-id="'+params.id+'"]');
                if (res.product !== null) {
                    $sku.find('.quantity').val(res.product.count);
                    $sku.find('.sum').html(res.product.count*res.product.price);
                } else {
                    $sku.detach();
                }
                $('.total_sum').html(res.sum);
            }
            return res.result;
        });
        posting.fail(function() {
            return false;
        });
        posting.always(function () {
            $("#alerts").load('/_util/alerts');
        });
    }
    
    function basket_update_wdget(res) {
        var $b = $('#bs_basket__count');
        $b.html(res.count);
        if (res.count > 0) {
            $b.addClass('hl_basket');
        } else {
            $b.removeClass('hl_basket');
        }
        $('#bs_basket__widget_list').load('/api/basket/widget');
    }
    
    $('#order_form').submit(function() {
        var isFormValid = true;
        $("input.required").each(function() {
            console.log($(this));
            if ($.trim($(this).val()).length == 0){
                $(this).addClass("input_hl");
                isFormValid = false;
            } else {
                $(this).removeClass("input_hl");
            }
        });
        return isFormValid;
    });
    
//    $('input[name="user[af_tel]"]').inputmask("+7 (999) 999-99-99");
//    $('input[name="user[email]"]').inputmask({
//        mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,6}][.*{1,2}]",
//        greedy: false,
//        onBeforePaste: function (pastedValue, opts) {
//            pastedValue = pastedValue.toLowerCase();
//            return pastedValue.replace("mailto:", "");
//        },
//        definitions: {
//            '*': {
//                validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~\-]",
//                cardinality: 1,
//                casing: "lower"
//            }
//        }
//    });
    
});

