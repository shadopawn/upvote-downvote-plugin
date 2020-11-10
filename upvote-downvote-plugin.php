<?php
/*
    Plugin Name: Upvote Downvote Plugin
    Plugin URI: https://github.com/shadopawn/upvote-downvote-plugin
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

    $post_id = $_REQUEST["post_id"];
    $vote_direction = $_REQUEST["vote_direction"];

    // fetch vote_count for a post, set it to 0 if it's empty, increment it when a click is registered
    $old_vote_count = get_post_meta($post_id, "votes", true);
    $old_vote_count = ($old_vote_count == '') ? 0 : $old_vote_count;
    $new_vote_count = $old_vote_count;

    //voted post structure
    //$voted_posts = array("1" => 0, "5" => 1, "7" => -1);

    if ($vote_direction >= 1){
        if (has_user_voted($post_id) === true){
            $previous_vote_direction = get_vote_direction_meta($post_id);
            if ($previous_vote_direction >= 1){
                $new_vote_count = $old_vote_count -1;
                set_user_vote_direction($post_id, 0);
            }elseif ($previous_vote_direction == 0){
                $new_vote_count = $old_vote_count + 1;
                set_user_vote_direction($post_id, 1);
            }elseif ($previous_vote_direction <= -1){
                $new_vote_count = $old_vote_count + 2;
                set_user_vote_direction($post_id, 1);
            }
        }else{
            $new_vote_count += $vote_direction;
            set_user_vote_direction($post_id, $vote_direction);
        }
    }elseif ($vote_direction <= -1){
        if (has_user_voted($post_id) === true){
            $previous_vote_direction = get_vote_direction_meta($post_id);
            if ($previous_vote_direction >= 1){
                $new_vote_count = $old_vote_count - 2;
                set_user_vote_direction($post_id, -1);
            }elseif ($previous_vote_direction == 0){
                $new_vote_count = $old_vote_count - 1;
                set_user_vote_direction($post_id, -1);
            }elseif ($previous_vote_direction <= -1){
                $new_vote_count = $old_vote_count + 1;
                set_user_vote_direction($post_id, 0);
            }
        }else{
            $new_vote_count += $vote_direction;
            set_user_vote_direction($post_id, $vote_direction);
        }
    }

    $vote = update_post_meta($post_id, "votes", $new_vote_count);


    // If above action fails, result type is set to 'error' and vote_count set to old value, if success, updated to new_vote_count
    if ($vote === false) {
        $result['type'] = "error";
        $result['vote_count'] = $old_vote_count;
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
    $voted_posts = get_voted_posts_meta();
    $voted_posts[$post_id] = $vote_direction;
    update_user_meta(get_current_user_id(), "voted_posts", $voted_posts);
}

function get_vote_direction_meta($post_id){
    if (has_user_voted($post_id)){
        $voted_posts = get_voted_posts_meta();
        return $voted_posts[$post_id];
    }
    return 0;
}

function has_user_voted($post_id){
    $voted_posts = get_voted_posts_meta();
    if (array_key_exists($post_id, $voted_posts)){
        return true;
    }
    else{
        return false;
    }
}

function get_voted_posts_meta(){
    $current_user_id = get_current_user_id();
    $voted_posts = get_user_meta($current_user_id, "voted_posts", true);
    $voted_posts = ($voted_posts == '') ? [] : $voted_posts;
    return $voted_posts;
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

