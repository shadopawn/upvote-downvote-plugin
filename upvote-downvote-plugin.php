<?php
/*
    Plugin Name: Upvote Downvote Plugin
    Plugin URI: https://www.delitt.com/
    description: >-
    a plugin to allow for upvotes and downvotes on posts
    Version: 1.2
    Author: Daniel Litt
    Author URI: https://www.delitt.com/
    License: GPL2
*/

// used here only for enabling syntax highlighting. Leave this out if it's already included in your plugin file.

// define the actions for the two hooks created, first for logged in users and the next for logged out users
add_action("wp_ajax_user_vote", "user_vote");
add_action("wp_ajax_nopriv_user_vote", "please_login");

// define the function to be fired for logged in users
function user_vote()
{

    // nonce check for an extra layer of security, the function will exit if it fails
    if (!wp_verify_nonce($_REQUEST['nonce'], "user_vote_nonce")) {
        exit("nonce error");
    }

    // fetch vote_count for a post, set it to 0 if it's empty, increment it when a click is registered
    $vote_count = get_post_meta($_REQUEST["post_id"], "votes", true);
    $vote_count = ($vote_count == '') ? 0 : $vote_count;
    $new_vote_count = $vote_count + $_REQUEST["vote_direction"];

    $voted_posts = get_voted_posts();

    //voted post structure
    //$voted_posts = array("1" => 0, "5" => 1, "7" => -1);

    //condition user has not voted
        //Add post_id and vote direction to user meta

    //user has upvoted presses upvote
        //Remove upvote from rating
        //Add post_id and vote direction to 0 in user meta

    //user has downvoted presses downvote
        //Remove downvote from rating
        //Add post_id and vote direction to 0 in user meta

    //user has upvoted presses downvote
        //Remove upvote from rating
        //Add post_id and vote direction to -1 in user meta

    //user has downvoted presses upvote
        //Remove downvote from rating
        //Add post_id and vote direction to 1 in user meta

    /*
    $vote = false;
    //the user does not have the post_id in their user meta
    if (in_array($_REQUEST["post_id"], $voted_posts) === false) {
        // Update the value of 'votes' meta key for the specified post, creates new meta data for the post if none exists
        $vote = update_post_meta($_REQUEST["post_id"], "votes", $new_vote_count);

        // now add the post_id to the use meta so they can't vote again
        if(count($voted_posts) === 0){
            $voted_posts = array($_REQUEST["post_id"]);
        }
        else{
            array_push($voted_posts, $_REQUEST["post_id"]);
        }
        update_user_meta($current_user_id, "voted_posts", []);
    }
    */

    $vote = update_post_meta($_REQUEST["post_id"], "votes", $new_vote_count);


    // If above action fails, result type is set to 'error' and vote_count set to old value, if success, updated to new_vote_count
    if ($vote === false) {
        $result['type'] = "error";
        $result['vote_count'] = $vote_count;
    } else {
        $result['type'] = "success";
        $result['vote_count'] = $new_vote_count;
    }

    // Check if action was fired via Ajax call. If yes, JS code will be triggered, else the user is redirected to the post page
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $result = json_encode($result);
        echo $result;
    } else {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }

    die();
}

function set_user_vote_direction($post_id, $vote_direction){
    $voted_posts = get_voted_posts();
    $voted_posts[$post_id] = $vote_direction;
    update_user_meta(get_current_user_id(), "voted_posts", $voted_posts);
}

function get_vote_direction($post_id){
    if (has_user_voted($post_id)){
        $voted_posts = get_voted_posts();
        return $voted_posts[$post_id];
    }

}

function has_user_voted($post_id){
    $voted_posts = get_voted_posts();
    if (array_key_exists($post_id, $voted_posts)){
        return true;
    }
    else{
        return false;
    }
}

function get_voted_posts(){
    $current_user_id = get_current_user_id();
    $voted_posts = get_user_meta($current_user_id, "voted_posts", true);
    $voted_posts = ($voted_posts == '') ? [] : $voted_posts;
    return $voted_posts;
}

function set_voted_posts_user_meta(){

}

// define the function to be fired for logged out users
function please_login()
{
    $result['type'] = "error";
    $result = json_encode($result);
    echo $result;
    die();
}

// Fires after WordPress has finished loading, but before any headers are sent.
add_action( 'init', 'script_enqueuer' );

function script_enqueuer() {

    // Register the JS file with a unique handle, file location, and an array of dependencies
    wp_register_script( "vote_display", plugin_dir_url(__FILE__).'vote_display.js', array('jquery') );

    // localize the script to your domain name, so that you can reference the url to admin-ajax.php file easily
    wp_localize_script( 'vote_display', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

    // enqueue jQuery library and the script you registered above
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'vote_display' );
}

