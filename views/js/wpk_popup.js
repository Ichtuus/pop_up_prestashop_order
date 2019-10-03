$(document).ready(function(){
    var timer = setInterval(randpop, 1000);
    function randpop(){
        var timeArray = new Array(2000, 5000, 7000, 10000, 11000, 14000, 17000);

        popupSet();
        clearInterval(timer);
        timer = setInterval(randpop, rangeRandome(timeArray));
    }

    function rangeRandome(data){
        var time = data[Math.floor(data.length * Math.random())];
        return time;
    }

    function popupSet() {
        var popupH = $(".popup").outerHeight(true);
        if(!$(".popup").hasClass("open")){
            $(".popup").addClass("open");
            $(".popup").not(":animated").animate({
                "top": -popupH,
                "right": 20
            }, 100, function(){
            $(this).show().not(":animated").animate({
                "top": 20
                }, 300);
            });
        }
        else{
            $(".popup").removeClass("open");
            $(".popup").not(":animated").animate({
                "top": -popupH,
                "right": 20
            }, 300, function(){
                $(this).hide();
            });
        }
    }
});
