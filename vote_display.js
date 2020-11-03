jQuery(document).ready( function() {
    jQuery(".user_vote").click( function(e) {
        e.preventDefault();
        post_id = jQuery(this).attr("data-post_id");
        vote_direction = jQuery(this).attr("data-vote_direction");
        nonce = jQuery(this).attr("data-nonce");
        jQuery.ajax({
            type : "post",
            dataType : "json",
            url : myAjax.ajaxurl,
            data : {action: "user_vote", post_id : post_id, vote_direction : vote_direction, nonce: nonce},
            success: function(response) {
                console.log(response)
                if(response.type === "success") {
                    jQuery("#vote_counter_"+post_id).html(response.vote_count);
                }
                else {
                    alert("Your vote could not be added");
                }
            }
        });
    });
});