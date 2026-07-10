jQuery(document).ready(function($) {
    
    $('p#billing_house_number_field span').append('<input type="checkbox" name="no-hausnr" id="no-hausnr" />');
    $('p#shipping_house_number_field span').append('<input type="checkbox" name="no-hausnr" id="no-hausnr-s" />');
    
    $(document).on('change', 'input#no-hausnr', function(){
        var ischecked = $(this).prop("checked");
        console.log("ischecked", ischecked);
        if ( ischecked ) {
            $('input#billing_house_number').attr('disabled', 'disabled').val('');
        } else {
            $('input#billing_house_number').removeAttr('disabled');
        }
        
    })
    $(document).on('change', 'input#no-hausnr-s', function(){
        var ischecked = $(this).prop("checked");
        console.log("ischecked", ischecked);
        if ( ischecked ) {
            $('input#shipping_house_number').attr('disabled', 'disabled').val('');
        } else {
            $('input#shipping_house_number').removeAttr('disabled');
        }
        
    })
    
});