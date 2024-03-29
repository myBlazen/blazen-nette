




//-----------------------------------------------SIDEBAR--------------------------------------------------------------->
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

//-----------------------------------------------TOTOPSCROLL----------------------------------------------------------->
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

//-------------------------------------------SameScrollLevelOnRefresh-------------------------------------------------->


// $(window).scroll(function () {
//     sessionStorage.scrollTop = $(this).scrollTop();
// });
// $(document).ready(function () {
//     if (sessionStorage.scrollTop != "undefined") {
//         $(window).scrollTop(sessionStorage.scrollTop);
//     }
// });

//-------------------------------------------AutosizeCommentTextarea--------------------------------------------------->

// autosize(document.getElementsByClassName("comment-textarea"));
// autosize(document.getElementsByClassName("message-textarea"));

$('textarea').each(function(){
    autosize(this);
}).on('autosize:resized', function(){
    console.log('textarea height updated');
});

$('.comment-textarea').keypress(function(e){

    if(e.key === 'Enter' && !e.shiftKey) {

        if(1){
            this.form.submit();
            this.form.innerText = "";
        }
    }
});

//--------------------------------------------------Parallex----------------------------------------------------------->


//-----------------------------------------------------LOADING--------------------------------------------------------->
var delayInMilliseconds = 0;


if (document.documentElement) {
    document.documentElement.className = 'loading';
}


setTimeout(function (){
    $(document).ready(function() {
        $(document.documentElement).removeClass('loading');
    });
}, delayInMilliseconds)

//--------------------------------------------CAROUSELS---------------------------------------------------------------->

$('.friends-slick-carousel').slick({
    prevArrow: $('.friends-slick-carousel-prev'),
    nextArrow: $('.friends-slick-carousel-next'),
    arrows: true,
    dots: false,
    autoplay: false,
    infinite: true,
    slidesToShow: 4,
    slidesToScroll: 4,
    responsive: [
        {
            breakpoint: 1235,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 650,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
    ]
});

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

//-------------------------------warframe------------------------------------------------------------------------------>


const futureCycles = 140;
const day = 100 * 60 * 1000;
const night = 50 * 60 * 1000;
const format = 'ddd, MMM D, YYYY h:mm A';
const timeFormat = 'ddd, MMM D, YYYY h:mm:ss A';

let expiry,
    nextCycle,
    nextCycleType,
    currCycleType,
    nextMoment,
    nextMomentMil,
    str = ``;
updateTime();
$.ajax({
    async : false,
    url:'https://api.warframestat.us/pc/cetusCycle',
    beforeSend: () => {
        $('.timetable').addClass('loader');
    }
}).done(data => {



    $('.timetable').removeClass('loader');

    nextCycle = data.expiry;
    nextCycleType = (data.isDay) ? 'night' : 'day';
    currCycleType = (data.isDay) ? 'Day' : 'Night';
    nextMoment = moment(nextCycle);
    nextMomentMil = nextMoment.valueOf();
    expiry = moment(data.expiry).valueOf();

    $('.now .dn').text(currCycleType);

}).fail(function(){
    console.log('error', arguments);
});

function showNotification() {
    const notification = new Notification("New message incoming", {
        body: "Hi there. How are you doing?",
        icon: ""
    })
}

function updateTime(){
    let timer, countdown, cdStr;

    if(expiry){

        timer = expiry - moment().valueOf();
        countdown = moment.duration(timer);
        cdStr = `
            ${(countdown.hours() > 0) ? countdown.hours() + 'h' : ''} 
            ${(countdown.minutes() >= 0) ? countdown.minutes() + 'm' : ''} 
            ${(countdown.seconds() >= 0) ? countdown.seconds() + 's' : ''}
        `;
        $('.now .timeleft').text(cdStr);

        document.title = currCycleType + " |" + cdStr;

        if(countdown.abs() <= 0){
            showNotification();
        }

    }



    $('.time').text(time());

}

function time(){
    return moment().format(timeFormat);
}

function refreshCetusCycle(){
    updateTime();
    $.nette.ajax({
        type: "POST",
        dataType: "json",
        url: '?do=refreshCetusCycle'
    });
}

$(function () {

    var cetusCycle = document.getElementById('cetusCycle');
    if(cetusCycle){
        if(cetusCycle.style.display != "none"){
            window.setInterval(function (){
                refreshCetusCycle()
            },1000)
        }
    }

});
