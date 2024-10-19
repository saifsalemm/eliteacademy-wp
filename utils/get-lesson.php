<?php

add_action('rest_api_init', function () {
    register_rest_route('elite/v1', '/get-lesson', array(
        'methods' => 'GET',
        'callback' => 'get_lesson_and_student_data_refactored',
        'permission_callback' => 'is_authenticated',
    ));
});

function get_lesson_and_student_data_refactored($request)
{
    $params = $request->get_params();
    $user_id = $params['user']->uid;
    $user_email = $params['user']->user_email;
    $is_admin = $user_id == '1';

    $slug = $_GET['lesson'];

    $product = get_page_by_path($slug, OBJECT, 'product');

    $product_id = $product->ID;
    $product_name = $product->post_title;

    $is_purchased = wc_customer_bought_product($user_id, $user_email, $product_id);
    $is_author = $product->post_author == $user_id;

    if ($is_purchased || $is_author || $is_admin) {

        // $lesson = get_transient('lesson_' . $product_id);

        // if (!$lesson) {
        $lesson_meta = get_post_meta($product_id);
        $questions_data = array();
        $quiz_data = get_post_meta($lesson_meta['quiz_id'][0]);
        $questions_splitted = explode('-', $quiz_data['questions_ids'][0]);

        foreach ($questions_splitted as $question_id) {
            $question_meta = get_post_meta($question_id);
            $question_data = array(
                "question_id" => intval($question_id),
                "title" => get_the_title($question_id),
                "content" => get_the_content(null, false, $question_id),
                "img" => $question_meta['image_url'][0],
                "weight" => intval($question_meta['weight'][0]),
                "answers" => explode('-', $question_meta['answers'][0]),
                "correct_answer" => $question_meta['correct_answer'][0],
                "hint" => $question_meta['question_hint'][0]
            );

            array_push($questions_data, $question_data);
        }

        $hwqs_data = array();
        $hw_data = get_post_meta($lesson_meta['hw_id'][0]);
        $hwqs_splitted = explode('-', $hw_data['questions_ids'][0]);

        foreach ($hwqs_splitted as $question_id) {
            $question_meta = get_post_meta($question_id);
            $question_data = array(
                "question_id" => intval($question_id),
                "title" => get_the_title($question_id),
                "content" => get_the_content(null, false, $question_id),
                "img" => $question_meta['image_url'][0],
                "weight" => intval($question_meta['weight'][0]),
                "answers" => explode('-', $question_meta['answers'][0]),
                "correct_answer" => $question_meta['correct_answer'][0],
                "hint" => $question_meta['question_hint'][0]
            );

            array_push($hwqs_data, $question_data);
        }

        $lesson_date = get_the_date('Y-m-d', $product_id);
        $quiz_title = get_the_title($lesson_meta['quiz_id'][0]);
        $last_trial_date = get_post_meta($lesson_meta['quiz_id'][0], 'last_trial_date', true);

        $lesson = array(
            "lesson_id" => $product_id,
            "title" => $product_name,
            "date" => $lesson_date,
            "price" => intval($lesson_meta['_regular_price'][0]),
            "video_exist" => $lesson_meta['video_on_off'][0] === "yes" ? true : false,
            "videos_data" => $lesson_meta['videos_urls_titles'][0],
            "video_host" => $lesson_meta['video_host'][0],
            "quiz_id" => $lesson_meta['quiz_id'][0] ?? null,
            "quiz_title" => $quiz_title,
            "hide_correct_answers" => $quiz_data['hide_correct_answers'][0] === "on" ? true : false,
            "must_answer_all" => $quiz_data['must_answer_all'][0] === "on" ? true : false,
            "quiz_duration" => $quiz_data['time_minutes'][0],
            "quiz_questions" => $questions_data,
            "quiz_randomize" => $quiz_data['reorder_questions'][0] === "on" ? true : false,
            "quiz_trials" => $lesson_meta['quiz_trials'][0] ?? 1,
            "quiz_required" => $lesson_meta['quiz_required'][0] === "yes" ? true : false,
            "hw_questions" => $hwqs_data,
            "hw_id" => $lesson_meta['hw_id'][0] ?? null,
            "lesson_files" => $lesson_meta['lesson_files'],
            "views_notification_mark" => $lesson_meta['views_notification_mark'][0],
            "last_trial_date" => $last_trial_date,
            "is_purchased" => $is_purchased
        );

        // set_transient('lesson_' . $product_id, $lesson, 3600 * 24);
        // }

        // get hw progress if found
        $homework_id = $lesson_meta['hw_id'][0];
        $homework_results = $lesson_meta['homework_results'];
        $hw_result = get_student_assessment_results($homework_results, $homework_id, true);

        // if ($homework_id) {
        //     $homework_results = get_user_meta($user_id, 'homework_results', false);
        //     foreach ($homework_results as $res) {
        //         $result = explode('-', $res);
        //         if ($result[0] === $homework_id) {
        //             $hw_result = get_post_meta($result[1], 'raw_data', true);
        //         }
        //     }
        // }

        $lesson["hw_result"] = $hw_result;
        $lesson["expiry_date"] = $lesson_meta['allowed_time'][0] == 0 ? -1 : intval(get_user_meta($user_id, $product_id . '_expiry_date', true));
        $lesson["remaining_views"] = $lesson_meta['allowed_views'][0] == 0 ? -1 : intval(get_user_meta($user_id, $product_id . '_remaining_views', true));
        $lesson["past_quiz_trials"] = fetch_grades_by_quiz_and_student($lesson_meta['quiz_id'][0], $user_id);
        $lesson["xvast_protection"] = $is_admin || $lesson_meta['xvast_protection'][0] === 'no' ? false : true;
        $lesson["is_author"] = $is_author || $is_admin ? true : false;
        $lesson["is_offline_purchase"] = in_array($product_id, get_user_meta($user_id, 'galal_offline_ids', false));

        return $lesson;
    }
    // $lesson = get_transient('lesson_' . $product_id);

    // if ($lesson) {
    $lesson_meta = get_post_meta($product_id);
    $pre_type = get_post_meta($product_id, 'prerequisite_type', true);
    $finished_prerequisites = false;

    if ($pre_type === 'quiz') {
        $pre_quiz_id = get_post_meta(get_post_meta($product_id, 'prerequisite', true), 'quiz_id', true);
        $user_past_quiz_trials = get_user_meta($user_id, 'quizzes_results', false);
        foreach ($user_past_quiz_trials as $res) {
            $id_location = strpos($res, $pre_quiz_id);
            if ($id_location !== false && $id_location !== -1) {
                $finished_prerequisites = true;
            }
        }
    } else if ($pre_type === 'hw') {
        $pre_hw_id = get_post_meta(get_post_meta($product_id, 'prerequisite', true), 'hw_id', true);
        $user_past_hw_trials = get_user_meta($user_id, 'homework_results', false);
        foreach ($user_past_hw_trials as $res) {
            $id_location = strpos($res, $pre_hw_id);
            if ($id_location !== false && $id_location !== -1) {
                $finished_prerequisites = true;
            }
        }
    } else {
        $finished_prerequisites = true;
    }

    $lesson = array(
        "lesson_id" => $product_id,
        "title" => $product_name,
        "date" => get_the_date('Y-m-d', $product_id),
        "price" => intval($lesson_meta['_regular_price'][0]),
        "code" => $lesson_meta['payment_method_code'][0] === "yes" ? true : false,
        "wallet" => $lesson_meta['payment_method_wallet'][0] === "yes" ? true : false,
        "fawry" => $lesson_meta['payment_method_fawry'][0] === "yes" ? true : false,
        "vodafone_cash" => $lesson_meta['payment_method_vodafone_cash'][0] === "yes" ? true : false,
        "last_purchase_date" => $lesson_meta['last_purchase_date'][0],
        "pre" => $lesson_meta['prerequisite'][0] == '' || !$pre_hw_id || !$lesson_meta['prerequisite'][0] ? false : intval($lesson_meta['prerequisite'][0]),
        // "hw_raw_data" => $raw_data,
        "is_purchased" => $is_purchased,
        "finished_prerequisites" => $finished_prerequisites
    );

    // set_transient('lesson_' . $product_id, $lesson, 3600 * 24);
    // }

    return $lesson;
}


function get_grade_by_author_and_quiz_id($quiz_id)
{
    // Get author ID and quiz ID from the URL parameters
    $author_id = get_current_user_id();

    // Query for a post with the specified author and quiz_id
    $args = array(
        'post_type' => 'grades',
        //         'author'         => $author_id,
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'   => 'quiz_id',
                'value' => $quiz_id,
            ),
            array(
                'key'   => 'student_id',
                'value' => $author_id,
            ),
        ),
    );

    $posts = get_posts($args);

    // Check if a post is found
    if (empty($posts)) {
        return false;
    }

    // Get the post data
    return true;
}

function fetch_grades_by_quiz_and_student($quiz_id, $student_id)
{

    // $user_data = get_transient('user_quiz_results_' . $student_id . '_' . $quiz_id);

    // if ($user_data) return $user_data;

    $args = array(
        'post_type' => 'grades',
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'quiz_id',
                'value' => $quiz_id,
                'compare' => '=',
            ),
            array(
                'key' => 'student_id',
                'value' => $student_id,
                'compare' => '=',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $grades_data = array();
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $grade = get_post_meta($post_id);
            $grades_data[] = $grade;
        }
        wp_reset_postdata();

        // set_transient('user_quiz_results_' . $student_id . '_' . $quiz_id, $grades_data, 3600);

        return $grades_data;
    } else {
        return null;
    }
}

function get_student_assessment_results($records, $assessment_id, $single)
{
    $results = [];
    foreach ($records as $res) {
        $quiz_and_res = explode($res, '-');
        if ($quiz_and_res[0] == $assessment_id) {
            $results[] = get_post_meta($quiz_and_res[1], 'raw_data', true);
        }
    }
    if (count($results) < 1) {
        return null;
    }
    return $single ? $results[0] : $results;
}
