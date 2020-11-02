<?php
// The 'likes' meta key value will store the total like count for a specific post, it'll show 0 if it's an empty string
$votes = get_post_meta(get_the_ID(), "votes", true);
$votes = ($votes == "") ? 0 : $votes;

// Linking to the admin-ajax.php file. Nonce check included for extra security. Note the "user_like" class for JS enabled clients.
$nonce = wp_create_nonce("user_vote_nonce");
$post_id = get_the_ID();
$link = admin_url('admin-ajax.php?action=user_vote&post_id='.$post_id.'&vote_direction=1' .'&nonce='.$nonce);
echo "<div style=\"text-align:center;\">";
echo '<a class="user_vote" data-nonce="' . $nonce . '" data-post_id="' . $post_id . '" href="' . $link . '">Upvote</a><br>';
echo "<span id='vote_counter_$post_id'>$votes</span><br>";
echo '<a class="user_vote" data-nonce="' . $nonce . '" data-post_id="' . $post_id . '" href="' . $link . '">Downvote</a>';
echo "</div>";