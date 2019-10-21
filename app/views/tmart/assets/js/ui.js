$(document).ready(function() {
    if ($('.banners-small-slider').length) {
        $('.banners-small-slider').owlCarousel({
            loop: true,
            margin: 5,
            nav: true,
            navText: ['<i class="zmdi zmdi-chevron-left"></i>', '<i class="zmdi zmdi-chevron-right"></i>'],
            items: 1,
            autoplay:true
        });
    }
});
