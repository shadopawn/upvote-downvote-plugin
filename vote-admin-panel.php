<table>
    <tr>
        <th>Username</th>
        <th>Upvote</th>
        <th>Downvote</th>
    </tr>
    <?php

    $users = get_users();
    foreach($users as $user){
        echo "<tr>";

        $user_info = get_userdata($user->ID);
        $voted_posts = get_user_meta($user->ID, "voted_posts", true);
        echo "<td>$user_info->user_login</td>";
        $upvoted_posts = "";
        $downvoted_posts = "";
        foreach ($voted_posts as $post_id=>$vote_direction){
            if ($vote_direction >= 1){
                $upvoted_posts .= get_the_title($post_id) . "<br>";
            }
            if ($vote_direction <= -1){
                $downvoted_posts .= get_the_title($post_id) . "<br>";
            }
        }
        echo "<td>";
        echo $upvoted_posts;
        echo "</td>";

        echo "<td>";
        echo $downvoted_posts;
        echo "</td>";

        echo "</tr>";
    }

    ?>
</table>
