/**
 * Joe Kanagawa (c) 2018
 * Versatile js scripts used throughout the website.
 */

var teamone = (function() {
    var userInfo = {};

    // private関数
    /**
     * Calling this function will cause the website to show a pop up window.
     * @param {string} popUpHTML The path to the HTML file, the actual contents of the pop up, that will go inside the window.
     * @param {function?} complete An optional callback function once loading of the contents is done.
     * @returns {jQuery} The topmost HTML element that contains every popup-related element.
     */
    function showPopUp(popUpHTML, complete) {
        return $("<div id='pop_up_bg'></div>").prependTo($("body")).fadeIn(550, function () {
            var container = $("<div></div>").prependTo($("<div></div>")
                .prependTo($("#pop_up_bg"))); // from block to table
            $("<div id='pop_up_window'></div>").prependTo(container).animate({
                "opacity": "1"
            }, 500, "swing", function () {
                $("#pop_up_bg").click(function () {
                    $(this).fadeOut(450, function () {
                        $(this).remove();
                    });
                }); // ↓prevent clicking pop up from exiting it
            }).click(function (event) { event.stopPropagation(); }).load(popUpHTML, complete || function(){});
        });
    }

    // 以下public関数として全世界に発信
    return {
        "createAccount": function () {
            userInfo = {
                "first": $("#new_first_name").val(),
                "last": $("#new_last_name").val(),
                "user": $("#new_username").val(),
                "pwd": $("#new_password_verify").val(),
                "email": $("#new_email").val()
            };
            $.ajax({
                "data": {
                    "user": userInfo.user,
                    "email": userInfo.email
                },
                "dataType": "text",
                "method": "POST",
                "url": "/php/signup.php"
            }).done(function (data) {
                if (data === "user_exists") {
                    var popUpWindow = $("#pop_up_window");
                    if (popUpWindow.find(".exists_msg").length === 0) {
                        popUpWindow.children(".sub_header").after("<br class='exists_msg'/>" +
                            "<span class='exists_msg pop_up_err_msg'>User already exists!<br />" +
                            "Please pick a different username.</span>");
                    }
                } else {
                    console.log(data);
                    $("#pop_up_window").load("/signup/verify.html").children().remove();
                }
            });
        },

        "logOut": function () {
            $.ajax({
                'method': 'POST',
                'url': '/php/logout.php'
            }).done(function(){
                window.location.replace('/');
            });
        },

        "selectVideo": function (title, path) {
            $("video").attr("src", "/res/video/" + path)
                .prop("controls", true).css("background-color", "black").prev().remove();
            $("#video_settings").children("span").html("Currently Playing: " + title);
        },

        "showSignUp": function () {
            showPopUp("/signup/body.html").on("input", function () {
                // check here if all fields are entered. If not, disable the sign up button.
                $("#pop_up_window").find("input").not("#new_submit").each(function () {
                    if (!$(this).val() || (function(jq) /* jq === <input /> */ {
                        if (jq.get(0).id === "new_password_verify") {
                            var enclosingSpan = jq.parent();
                            var enclosingDiv = enclosingSpan.parent();
                            if (enclosingDiv.toggleClass("pwd_not_match", jq.val() !== $("#new_password").val())
                                    .hasClass("pwd_not_match")) {
                                if (enclosingSpan.children().length === 1)
                                    enclosingSpan.append("<br /><span>Password does not match!</span>");
                                return true;
                            } else
                                enclosingSpan.children().not("input").remove();
                        }
                        return false;
                    })($(this))) {
                        $("#new_submit").prop("disabled", true); // 空 or パスワードがマッチしない
                        return false; // each関数を早期脱却
                    } else
                        $("#new_submit").prop("disabled", false);
                });
            });
        },

        "showUpload": function () {
            showPopUp("/upload/details_prompt.html", function() {
                $("#pop_up_window").find("input[type='file']").change(function() {
                    $("#pop_up_window").find("input[type='submit']").prop("disabled", false);
                });
            });

        },


        "upload": function () {
            var popUpWindow = $("#pop_up_window");
            var data = new FormData();
            data.append("video", popUpWindow.find("input[type='file']").get(0).files[0]);
            data.append("title", $("#video_title").val());
            popUpWindow.load("/upload/upload_and_process.html");
            $.ajax({
                "contentType": false,
                "data": data,
                "dataType": "text",
                "method": "POST",
                "processData": false,
                "url": "/php/upload.php"
            }).done(function(data) {
                if (data) { // fail
                    console.log(data); // process用 ajaxを送信
                } else { // success, process it
                    console.log("success!");
                    $("#pop_up_window").find(".loading").eq(0)
                        .removeClass("loading").addClass("check").next().html("Upload complete!");
                    $(".uap_each_container").eq(1).children(":not(.loading)").html("Processing video..");
                    $.ajax({

                    })
                }

            });
        },

        "verify": function() {
            $.ajax({
                "data": {
                    "vcode": $("#v_code").val(),
                    "first": userInfo.first,
                    "last": userInfo.last,
                    "user": userInfo.user,
                    "pwd": userInfo.pwd,
                    "email": userInfo.email
                },
                "dataType": "text",
                "method": "POST",
                "url": "/php/signup.php"
            }).done(function (data) {
                var popUpWindow = $("#pop_up_window");
                switch (data) {
                    case "success":
                        popUpWindow.load("/signup/success.html").children().remove();
                        break;
                    case "fail":
                        if (popUpWindow.find(".fail_msg").length === 0) {
                            if (popUpWindow.find(".expired_msg").length === 2)
                                popUpWindow.find(".expired_msg").remove();
                            popUpWindow.children(".sub_header")
                                .after($("<span class='fail_msg pop_up_err_msg'>Incorrect code. " +
                                    "Please try again.</span><br class='fail_msg' />"));
                        }
                        break;
                    case "expired":
                        if (popUpWindow.find(".expired_msg").length === 0) {
                            if (popUpWindow.find(".fail_msg").length === 2)
                                popUpWindow.find(".fail_msg").remove();
                            popUpWindow.children(".sub_header").after($("<span class='expired_msg pop_up_err_msg'>" +
                                "Verification code has been expired. " +
                                "Please restart the registration process.</span><br class='expired_msg' />"));
                        }
                }
            });
        }
    };
})();
