var AuthSms = {};

AuthSms.CallByInputLen = function (input, len, callback) {
    var isSended = false;
    input.on("keyup", function () {
        if (isSended) return;
        if ($(this).val().length == len) {
            isSended = true;
            callback();

        }
    });
}

AuthSms.Init = function () {

}

$(document).ready(function () {
    //AuthSms.Init();
});

window.AuthSms = AuthSms;
