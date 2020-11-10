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
        echo "<td>";
        foreach ($voted_posts as $post_id=>$vote_direction){
            if ($vote_direction >= 1){
                echo get_the_title($post_id) . "<br>";
            }
        }
        echo "</td>";

        echo "<td>";
        foreach ($voted_posts as $post_id=>$vote_direction){
            if ($vote_direction <= -1){
                echo get_the_title($post_id) . "<br>";
            }
        }
        echo "</td>";

        echo "</tr>";
    }

    ?>
</table>
