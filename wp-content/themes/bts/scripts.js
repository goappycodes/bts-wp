jQuery(document).ready(function($){

var height = $('.term-description').height();
if (height > 280){
   $("body").addClass("largecontent");
}

var home_height = $('.welcome-content').height();
console.log(home_height);
if (home_height > 350){
    $("body").addClass("largecontent");
}

$('<div class="description-more">'+read_more_text+'</div>').insertAfter('.term-description');
$('<div class="description-more">'+read_more_text+'</div>').insertAfter('.welcome-content');

$(document).on('click', '.description-more', function(){
   $("body").removeClass("largecontent");
});

});

jQuery(document).ready(function($) {

    if ($(window).width() > 768) {

        $('.add-to-cart-button').on('click', function() {
            var cart = $('.icon-shopping-basket');
            var imgtodrag = $(this).parent().parent().find("img").eq(0);
            if (imgtodrag) {
                var imgclone = imgtodrag.clone()
                    .offset({
                        top: imgtodrag.offset().top,
                        left: imgtodrag.offset().left
                    })
                    .css({
                        'opacity': '0.5',
                        'position': 'absolute',
                        'height': '150px',
                        'width': '150px',
                        'z-index': '100'
                    })
                    .appendTo($('body'))
                    .animate({
                        'top': cart.offset().top + 10,
                        'left': cart.offset().left + 10,
                        'width': 75,
                        'height': 75
                    }, 1000, 'easeInOutExpo');

                setTimeout(function() {
                    cart.effect("shake", {
                        times: 2
                    }, 200);
                }, 1500);

                imgclone.animate({
                    'width': 0,
                    'height': 0
                }, function() {
                    $(this).detach()
                });
            }
        });
        $('button.single_add_to_cart_button.button.alt').on('click', function() {
            var cart = $('.icon-shopping-basket');
            var imgtodrag = $(this).parent().parent().parent().parent().find("img").eq(0);
            if (imgtodrag) {
                var imgclone = imgtodrag.clone()
                    .offset({
                        top: imgtodrag.offset().top,
                        left: imgtodrag.offset().left
                    })
                    .css({
                        'opacity': '0.5',
                        'position': 'absolute',
                        'height': '150px',
                        'width': '150px',
                        'z-index': '100'
                    })
                    .appendTo($('body'))
                    .animate({
                        'top': cart.offset().top + 10,
                        'left': cart.offset().left + 10,
                        'width': 75,
                        'height': 75
                    }, 500, 'easeInOutExpo');

                setTimeout(function() {
                    cart.effect("shake", {
                        times: 2
                    }, 200);
                }, 1500);

                imgclone.animate({
                    'width': 0,
                    'height': 0
                }, function() {
                    $(this).detach()
                });
            }
        });
    } else {
        $('.add-to-cart-button').on('click', function() {
            var cart = $('.mobile-nav li.cart-item');
            var imgtodrag = $(this).parent().parent().find("img").eq(0);
            if (imgtodrag) {
                var imgclone = imgtodrag.clone()
                    .offset({
                        top: imgtodrag.offset().top,
                        left: imgtodrag.offset().left
                    })
                    .css({
                        'opacity': '0.5',
                        'position': 'absolute',
                        'height': '150px',
                        'width': '150px',
                        'z-index': '100'
                    })
                    .appendTo($('body'))
                    .animate({
                        'top': cart.offset().top + 10,
                        'left': cart.offset().left + 10,
                        'width': 75,
                        'height': 75
                    }, 1000, 'easeInOutExpo');

                setTimeout(function() {
                    cart.effect("shake", {
                        times: 2
                    }, 200);
                }, 1500);

                imgclone.animate({
                    'width': 0,
                    'height': 0
                }, function() {
                    $(this).detach()
                });
            }
        });
        $('button.single_add_to_cart_button.button.alt').on('click', function() {
            var cart = $('.mobile-nav li.cart-item');
            var imgtodrag = $(this).parent().parent().parent().parent().find("img").eq(0);
            if (imgtodrag) {
                var imgclone = imgtodrag.clone()
                    .offset({
                        top: imgtodrag.offset().top,
                        left: imgtodrag.offset().left
                    })
                    .css({
                        'opacity': '0.5',
                        'position': 'absolute',
                        'height': '150px',
                        'width': '150px',
                        'z-index': '100'
                    })
                    .appendTo($('body'))
                    .animate({
                        'top': cart.offset().top + 10,
                        'left': cart.offset().left + 10,
                        'width': 75,
                        'height': 75
                    }, 500, 'easeInOutExpo');

                setTimeout(function() {
                    cart.effect("shake", {
                        times: 2
                    }, 200);
                }, 1500);

                imgclone.animate({
                    'width': 0,
                    'height': 0
                }, function() {
                    $(this).detach()
                });
            }
        });
    }



});



jQuery(document).ready(function($) {

    $('li.cat-item.cat-item-1378 a').attr('href', 'https://www.brieftaubenshop.de/newsletter/?nc=3')

    $("a.nav-top-link.nav-top-not-logged-in").attr("data-open", "");

    setInterval(function() {

        if ($('.lrm-user-modal').hasClass('is-visible')) {
            $('.mfp-wrap.mfp-auto-cursor.off-canvas.off-canvas-left').addClass('modal-open');
        } else {
            $('.mfp-wrap.mfp-auto-cursor.off-canvas.off-canvas-left').removeClass('modal-open');
        }

        $('.widget_shopping_cart p.total.shipping-costs-cart-info.wc-gzd-total-mini-cart a').attr('href', 'https://www.brieftaubenshop.de/bezahlmoeglichkeiten/')
        $('form.checkout.woocommerce-checkout span.woocommerce-gzd-legal-checkbox-text a:nth-child(1)').attr('href', 'https://www.brieftaubenshop.de/agb/')
        $('form.checkout.woocommerce-checkout span.woocommerce-gzd-legal-checkbox-text a:nth-child(2)').attr('href', 'https://www.brieftaubenshop.de/widerrufsbelehrung/')

    }, 500);

});