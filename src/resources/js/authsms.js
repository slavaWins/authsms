var AuthSms = {};


AuthSms.CallByInputLen = function (input, len, callback) {
    var isSended = false;

    var prevLen =input.val().length;

    input.on("keyup", function (event) {
        if (isSended) return;

        var length = $(this).val().length;

        if (length == prevLen) return;

        prevLen = length;
        if (length == len) {
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
