$(function() {
    var now; //タイムスタンプ用変数
    $.getJSON('datetime.php') 
    .done(function(json){ //json読み込み成功時
        for(var i in json){
            now = json[i] * 1000;//PHPタイムスタンプ1000倍
        }
    })
    .fail(function(){ //json読み込み失敗時
        console.log('json_error');
    });

    function showtime(){
    today = new Date(now);
    $weekday = ['日','月','火','水','木','金','土'];
    month = today.getMonth() + 1 ;
    $('#datetime').html(month + "月"+ today.getDate() +
     "日（" + $weekday[today.getDay()] +"） " +today.getHours() + 
     ":" + ('0'+today.getMinutes()).slice(-2) + ":" + ('0' +today.getSeconds()).slice(-2));
    now += 1000;
    }
    setInterval(showtime,1000);
});