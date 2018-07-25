$(function () {
    /* 检测输入框的大小写是否开启 */
    window.onload = function () {
        function isIE() {
            if (!!window.ActiveXObject || "ActiveXObject" in window) {
                return true;
            }
            else {
                return false;
            }
        }

        (function () {
            var inputPWD = document.getElementById('password');
            var capital = false;
            var capitalTip = {
                elem: document.getElementById('capital'),
                toggle: function (s) {
                    var sy = this.elem.style;
                    var d = sy.display;
                    if (s) {
                        sy.display = s;
                    }
                    else {
                        sy.display = d == 'none' ? '' : 'none';
                    }
                }
            }
            var detectCapsLock = function (event) {
                if (capital) {
                    return
                }
                ;
                var e = event || window.event;
                var keyCode = e.keyCode || e.which;
                var isShift = e.shiftKey || (keyCode == 16 ) || false;
                if (((keyCode >= 65 && keyCode <= 90 ) && !isShift) || ((keyCode >= 97 && keyCode <= 122 ) && isShift)) {
                    capitalTip.toggle('block');
                    capital = true
                }
                else {
                    capitalTip.toggle('none');
                }
            }
            if (!isIE()) {
                inputPWD.onkeypress = detectCapsLock;
                inputPWD.onkeyup = function (event) {
                    var e = event || window.event;
                    if (e.keyCode == 20 && capital) {
                        capitalTip.toggle();
                        return false;
                    }
                }
            }
        })()
    };

    //validate插件表单验证
    $(".am-form").validate({
        onfocusout: function (element) {
            $(element).valid();
        }
    });
});