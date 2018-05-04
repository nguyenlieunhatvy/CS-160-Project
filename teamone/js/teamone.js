/**
 * Joe Kanagawa (c) 2018
 * Versatile js scripts used throughout the website.
 */

var teamone = (function() {
    var userInfo = {};
    var pathToCurrentlySelected = false;

    // private
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

    /**
     * Helper function that checks if all inputs in a form are valid. If not, the submit button is kept disabled.
     * Must be passed as a function object in the first parameter of the .each function!
     * @returns {void|boolean} Returns false if an invalid input is detected to escape the .each function.
     */
    function enableSubmitWhenAppropriate() {
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
    }

    // The below are public functions
    return {
        "confirmDelete": function () {
            showPopUp("settings_popup/delete_account.html").on("input", function () {
                $("#pop_up_window").find("input").not("#new_submit").each(enableSubmitWhenAppropriate);
            });
        },

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

        "deleteAccount": function () {
            $.ajax({
                "data": {
                    "pwd": $("#confirm_password").val()
                },
                "method": "POST",
                "url": "/php/delete_account.php"
            }).done(function (data) {
                console.log(data);
                switch (data) {
                    case "fail":
                        var popUpWindow = $("#pop_up_window");
                        if (popUpWindow.find(".fail_msg").length === 0) {
                            popUpWindow.children(".sub_header").after("<br class='fail_msg'/>" +
                                "<span class='fail_msg pop_up_err_msg'>Incorrect Password! " +
                                "Unable to delete account.</span>");
                        }
                        break;
                    case "success":
                        window.location.replace("/removal_success");
                }
            });
        },

        "modifyVideo": function (type) {
            var data = {
                "modifyType": type,
                "path": pathToCurrentlySelected
            };
            if (type === "rename")
                data["title"] = $("#new_video_name").val();
            $.ajax({
                "data": data,
                "dataType": "text",
                "method": "POST",
                "url": "/php/modify_video.php"
            }).done(function (data) {
                if (data === "success") {
                    $("#pop_up_window").load("video_settings/success.html", function () {
                        $(".sub_header").children("span").html(type === "delete" ? "Deleted" : "Renamed");
                    });
                }
            });
        },

        "logOut": function () {
            $.ajax({
                "method": "POST",
                "url": "/php/logout.php"
            }).done(function(){
                window.location.replace('/');
            });
        },

        "selectVideo": function (title, path, isProcessed) {
            if (!parseInt(isProcessed)) {
                $("#video_box").children("div").html("It appears the video has not finished processing yet." +
                    "<br />Please try again later.");
                return;
            }
            $("video").attr("src", "/res/video/" + path)
                .prop("controls", true).css("background-color", "black").prev().remove();
            var vset = $("#video_settings");
            vset.children("span").html("Playing: " + title);
            if (!pathToCurrentlySelected) {
                vset.children("div").each(function () {
                    $(this).css({
                        "background-image": $(this).css("background-image").replace(/_n(?=\.\w+"\)$)/, ""),
                        "cursor": "pointer"
                    }).click(function () { // bind click event
                        var isDeleteVideo = $(this).get(0).id === "delete_video";
                        showPopUp(isDeleteVideo ? "video_settings/delete_video.html" :
                            "video_settings/rename_video.html");
                    });
                });
            }
            pathToCurrentlySelected = path;
        },

        "showSignUp": function () {
            showPopUp("/signup/body.html").on("input", function () {
                // check here if all fields are entered. If not, disable the sign up button.
                $("#pop_up_window").find("input").not("#new_submit").each(enableSubmitWhenAppropriate);
            });
        },

        "showUpdate": function (path) {
            showPopUp(path, function () {
                $("#new_submit").click(function () {
                    var isPwdUpdate = $(this).attr("data-updateType") === "pwd";
                    var data = {
                        "newData": isPwdUpdate ? $("#new_password_verify").val() : $("#new_data").val(),
                        "updateType": $(this).attr("data-updateType")
                    };
                    if (isPwdUpdate)
                        data["oldData"] = $("#old_password").val();
                    $.ajax({
                        "data": data,
                        "dataType": "text",
                        "method": "POST",
                        "url": "/php/settings_update.php"
                    }).done(function (data) {
                        var popUpWindow = $("#pop_up_window");
                        switch (data) {
                            case "success":
                                popUpWindow.load("settings_popup/success.html");
                                break;
                            case "pwd_not_match":
                                if (popUpWindow.find(".pwd_msg").length === 0) {
                                    if (popUpWindow.find(".fail_msg").length === 2)
                                        popUpWindow.find(".fail_msg").remove();
                                    popUpWindow.children(".sub_header")
                                        .after($("<br class='pwd_msg' /><span class='pwd_msg pop_up_err_msg'>" +
                                            "Old password does not match the current one. Please try again.</span>"));
                                }
                                break;
                            default: // default means any error
                                if (popUpWindow.find(".fail_msg").length === 0) {
                                    if (popUpWindow.find(".pwd_msg").length === 2)
                                        popUpWindow.find(".pwd_msg").remove();
                                    popUpWindow.children(".sub_header")
                                        .after($("<br class='fail_msg' /><span class='fail_msg pop_up_err_msg'>" +
                                            "An error has occurred. Please try again.</span>"));
                                }
                        }
                    });
                });
            }).on("input", function () {
                $("#pop_up_window").find("input").not("#new_submit").each(enableSubmitWhenAppropriate);
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
                if (data === "fail") { // fail
                    console.log(data);
                } else { // success, process it
                    $("#pop_up_window").find(".loading").eq(0)
                        .removeClass("loading").addClass("check").next().html("Upload complete!");
                    $(".uap_each_container").eq(1).children(":not(.loading)").html("Processing video..");
                    var videoId = data;
                    $.ajax({
                        "data": {
                            "vid": videoId
                        },
                        "dataType": "text",
                        "method": "POST",
                        "url": "/php/process_video.php"
                    }).done(function (data) {
                        if (data === "success") {
                            var timer = setInterval(function () {
                                $.ajax({
                                    "data": {
                                        "check": "1",
                                        "vid": videoId
                                    },
                                    "dataTyp": "text",
                                    "method": "POST",
                                    "url": "/php/process_video.php"
                                }).done(function(data) {
                                    if (data === "complete")
                                        $("#pop_up_window").find(".loading").removeClass("loading")
                                            .addClass("check").next().html("Processing Complete!");
                                    clearInterval(timer);
                                })
                            }, 5000);
                        }
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
