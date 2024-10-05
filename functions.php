<?php
// Include custom code files
include get_stylesheet_directory() . '/utils/gen-rand-4-digit-number.php';
include get_stylesheet_directory() . '/utils/authenticate-user.php';
// include get_stylesheet_directory() . '/utils/get-user.php';
// include get_stylesheet_directory() . '/utils/get-portal.php';
// include get_stylesheet_directory() . '/utils/get-lesson.php';
include get_stylesheet_directory() . '/utils/custom_product_category_fields-teacher-meta-data.php';
include get_stylesheet_directory() . '/utils/tutor-earnings-page.php';
// Add more includes as needed




// global $elite_redis;
// $elite_redis = new Redis();
// $elite_redis->connect('127.0.0.1', 6379);

add_action('rest_api_init', function () {
    register_rest_route('beta/v1', '/test-authentication', array(
        'methods' => 'POST',
        'callback' => 'test_authentication',
        'permission_callback' => 'is_authenticated',
    ));
});

function test_authentication($request)
{
    $params = $request->get_params();
    return $params;
}



// SELECT ID FROM `wp_posts`
// WHERE wp_posts.post_type = "quiz"
// OR wp_posts.post_type = "homework";


add_action('rest_api_init', function () {
    register_rest_route('elite/v1', '/fix-quizzes-and-homeworks', array(
        'methods' => 'GET',
        'callback' => 'fix_quiz_hw',
    ));
});


function fix_quiz_hw()
{
    $quizzes = get_posts([
        'post_type' => 'quiz',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);
    $homeworks = get_posts([
        'post_type' => 'homework',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);

    $ids = array_merge($quizzes, $homeworks);

    $empty_arr = array();

    foreach ($ids as $id) {
        $questions_string = get_post_meta($id, 'questions_ids', true);
        $questions = explode('-', $questions_string);
        foreach ($questions as $question) {
            array_push($empty_arr, $question);
            add_post_meta($id, 'questions_id', $question);
        }
    }

    return $empty_arr;
}