$(function() {
    $(".user-chat-room").on("click",function(e) {
        e.preventDefault();






    });
});


const btns = document.getElementsByClassName("user-chat-room");


for (let i = 0; i < btns.length; i++) {

    if(window.location.pathname.includes(btns[i].id)){
        btns[i].classList.add('active');
        $("#to_user_id").val(btns[i].getAttribute('to_user_id'));
    }

    btns[i].addEventListener("click", function() {

        for (let j = 0; j < btns.length; j++) {
            btns[j].classList.remove('active');
        }

        this.classList.add('active');
        console.log(this.getAttribute('to_user_id'));

        $("#to_user_id").val(this.getAttribute('to_user_id'));

        window.history.pushState(this.id, "Title",  this.id);

    });
}

