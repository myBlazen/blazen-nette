(function($) {
    "use strict"; // Start of use strict

    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(window).width() < 768) {
            document.getElementById("content-wrapper").style.marginLeft = '6.5rem';
            document.getElementById("navbar").style.marginLeft = '6.5rem';
            if ($(".sidebar").hasClass("toggled")) {
                $('.sidebar .collapse').collapse('hide');
                document.getElementById("content-wrapper").style.marginLeft = '0';
                document.getElementById("navbar").style.marginLeft = '0';
            };
        }else{
            document.getElementById("content-wrapper").style.marginLeft = '14rem';
            document.getElementById("navbar").style.marginLeft = '14rem';
            if ($(".sidebar").hasClass("toggled")) {
                $('.sidebar .collapse').collapse('hide');
                document.getElementById("content-wrapper").style.marginLeft = '6.5rem';
                document.getElementById("navbar").style.marginLeft = '6.5rem';
            };
        }

    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function() {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        }
        // Toggle the side navigation when window is resized below 480px
        if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
            $("body").addClass("sidebar-toggled");
            $(".sidebar").addClass("toggled");
            $('.sidebar .collapse').collapse('hide');
        }else{
            $("body").removeClass("sidebar-toggled");
            $(".sidebar").removeClass("toggled");
        };
    });
    $(window).resize(function(){
        if($(window).width() < 768 ){
            document.getElementById("content-wrapper").style.marginLeft = '6.5rem';
            document.getElementById("navbar").style.marginLeft = '6.5rem';
            if($(window).width() < 480){
                document.getElementById("content-wrapper").style.marginLeft = '0';
                document.getElementById("navbar").style.marginLeft = '0';
            }else{
                document.getElementById("content-wrapper").style.marginLeft = '6.5rem';
                document.getElementById("navbar").style.marginLeft = '6.5rem';
            }
        } else{
            document.getElementById("content-wrapper").style.marginLeft = '14rem';
            document.getElementById("navbar").style.marginLeft = '14rem';
        }
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function() {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });

})(jQuery); // End of use strict
