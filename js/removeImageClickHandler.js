/**
 * Created by moridrin on 11-12-16.
 */

var removeImageClickHandler = function (e) {
    $.ajax({
        type: "POST",
        url: variables.nonceURL,
        data: {
            remove_image: variables.fieldID,
            user_id: variables.userID
        },
        success: function (data) {
            if (data.indexOf("image successfully removed success") >= 0) {
                $("#" + variables.fieldID + "_remove").remove();
                $("#" + variables.fieldID + "_image").remove();
            }
        },
        error: function (data) {
            alert(data.responseText);
        }
    });
    e.stopImmediatePropagation();
    return false;
};
