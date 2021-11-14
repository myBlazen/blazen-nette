/**
 * Select inbox, change url and add value to send message form
 * @param _this
 */
function selectInbox(_this){
    window.history.pushState(_this.id, "Title",  _this.id);

    $("#to_user_id").val(_this.getAttribute('to_user_id'));


    setTimeout(function(){
        refreshMessages();
        refreshInboxes();
    }, 100)

}
// var timer = window.setInterval(function() {
//     var elem = document.getElementById('message-history');
//     elem.scrollTop = elem.scrollHeight;
//     window.clearInterval(timer);
// }, 500);

// window.setInterval(function (){
//     var b = document.getElementById('message-history');
//
//     b.scrollTop = b.scrollHeight;
//
//     var lst = b.scrollTop;
//
//
//     b.addEventListener("scroll", function() {
//
//         if (b.scrollTop > lst) {
//             console.log("Greater than Last")
//
//         } else {
//             console.log("Less or equal than last.")
//
//         }
//
//         lst = b.scrollTop;
//
//     });
// },1000)

function refreshMessages(){
    $.nette.ajax({
        type: "POST",
        dataType: "json",
        url: '?do=refreshMessages'
    });
}

function refreshInboxes(){
    $.nette.ajax({
        type: "POST",
        dataType: "json",
        url: '?do=refreshInboxes'
    });
}

function refreshWallPosts(){
    $.nette.ajax({
        type: "POST",
        dataType: "json",
        url: '?do=refreshWallPosts'
    });
}

$(function () {
    $.nette.init();


    // $('#message-history').ready(function (){
    //     window.setInterval(function (){
    //         refreshMessages();
    //     },1000)
    // })
    var wall_posts = document.getElementById('wall-posts');
    if(wall_posts){
        if(wall_posts.style.display != "none"){
            window.setInterval(function (){
                refreshWallPosts();
            },5000)
        }
    }

    var message_history = document.getElementById('message-history');
    if(message_history){
        if(message_history.style.display != "none"){
            window.setInterval(function (){
                refreshMessages();
            },1000)
        }
    }

});



