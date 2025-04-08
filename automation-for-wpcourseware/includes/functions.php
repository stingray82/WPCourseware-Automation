<?php
if ( ! defined('ABSPATH') ) {
    exit;
}

// Example function (update as needed)
function rup_wpc_auto_automation_for__wpcourseware_example_function() {
    return true;
}

/**
 * Enroll an existing user in a course using the internal course ID (as shown in the GUI).
 *
 * @param string $email     The user's email address.
 * @param int    $course_id The internal WP Courseware course ID (as seen in the GUI).
 *
 * @return array|WP_Error   User context data on success, or WP_Error on failure.
 */
function enroll_existing_user_in_course_by_course_id( $email, $course_id ) {
    // Validate email.
    if ( ! is_email( $email ) ) {
        return new WP_Error( 'invalid_email', 'Please enter a valid email address.' );
    }

    // Look up the user by email.
    $user = get_user_by( 'email', $email );
    if ( ! $user ) {
        return new WP_Error( 'user_not_found', 'User not found for this email address.' );
    }
    $user_id = $user->ID;

    // Ensure WP Courseware functions are available.
    if ( ! function_exists( 'wpcw_get_courses' ) ) {
        return new WP_Error( 'function_not_exists', 'wpcw_get_courses does not exist.' );
    }

    // Retrieve the list of courses.
    $courses = wpcw_get_courses();
    if ( empty( $courses ) ) {
        return new WP_Error( 'no_courses_found', 'No courses found.' );
    }

    // Build an enrollment list by matching the course ID from the GUI.
    $enroll_course_list = array();
    foreach ( $courses as $course ) {
        if ( intval( $course->course_id ) === intval( $course_id ) ) {
            $enroll_course_list[ $course->course_id ] = $course->course_id;
            break;
        }
    }

    if ( empty( $enroll_course_list ) ) {
        return new WP_Error( 'course_not_found', 'Course not found for provided course ID.' );
    }

    // Ensure the enrollment function exists.
    if ( ! function_exists( 'WPCW_courses_syncUserAccess' ) ) {
        return new WP_Error( 'function_not_exists', 'WPCW_courses_syncUserAccess does not exist.' );
    }

    // Enroll the user in the course(s).
    WPCW_courses_syncUserAccess( $user_id, $enroll_course_list, 'add' );

    // Retrieve user context. If available, use WordPress::get_user_context; otherwise, return basic data.
    $context = array();
    if ( class_exists( 'WordPress' ) && method_exists( 'WordPress', 'get_user_context' ) ) {
        $context = WordPress::get_user_context( $user_id );
    } else {
        $context = array( 'user_id' => $user_id );
    }

    // Optionally, add course details to the context.
    if ( function_exists( 'wpcw_get_course' ) ) {
        $context['course'] = wpcw_get_course( $course_id );
    }

    return $context;
}

/**
 * Creates a new user (if they don't already exist) and enrolls them in a course using the internal course ID.
 *
 * @param string $email     The user's email address.
 * @param int    $course_id The internal WP Courseware course ID (as seen in the GUI).
 * @param string $username  Optional username; if not provided, one is derived from the email.
 *
 * @return array|WP_Error   User context data on success, or WP_Error on failure.
 */
function create_or_enroll_user_in_course_by_course_id( $email, $course_id, $username = '' ) {
    // Validate email.
    if ( ! is_email( $email ) ) {
        return new WP_Error( 'invalid_email', 'Please enter a valid email address.' );
    }

    // Check if user exists.
    $user = get_user_by( 'email', $email );
    if ( ! $user ) {
        // Derive a username from the email if not provided.
        if ( empty( $username ) ) {
            $username = sanitize_user( current( explode( '@', $email ) ), true );
        }
        // Generate a random password.
        $random_password = wp_generate_password();
        $user_id         = wp_create_user( $username, $random_password, $email );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
    }

    // Enroll the user in the course.
    return enroll_existing_user_in_course_by_course_id( $email, $course_id );
}

/**
 * Removes an existing user from a course using the internal course ID (as shown in the GUI).
 *
 * @param string $email     The user's email address.
 * @param int    $course_id The internal WP Courseware course ID (as seen in the GUI).
 *
 * @return array|WP_Error   User context data on success, or WP_Error on failure.
 */
function remove_user_from_course_by_course_id( $email, $course_id ) {
    // Validate email.
    if ( ! is_email( $email ) ) {
        return new WP_Error( 'invalid_email', 'Please enter a valid email address.' );
    }

    // Get the user by email.
    $user = get_user_by( 'email', $email );
    if ( ! $user ) {
        return new WP_Error( 'user_not_found', 'User not found for this email address.' );
    }
    $user_id = $user->ID;

    // Ensure WP Courseware function exists.
    if ( ! function_exists( 'WPCW_users_getUserCourseList' ) ) {
        return new WP_Error( 'function_not_exists', 'WPCW_users_getUserCourseList does not exist.' );
    }

    // Retrieve the list of courses the user is enrolled in.
    $user_course_list = WPCW_users_getUserCourseList( $user_id );
    if ( empty( $user_course_list ) ) {
        return new WP_Error( 'no_courses', 'User is not enrolled in any courses.' );
    }

    // Build a new course list excluding the course to be removed.
    $sync_course_list = array();
    foreach ( $user_course_list as $course ) {
        // Compare using the GUI course ID (course->course_id).
        if ( intval( $course->course_id ) !== intval( $course_id ) ) {
            $sync_course_list[ $course->course_id ] = $course->course_id;
        } else {
            // If the course to remove is the only one enrolled, try to add an alternative course.
            if ( empty( $sync_course_list ) && count( $user_course_list ) == 1 && function_exists( 'WPCW_courses_getCourseList' ) ) {
                $all_courses = WPCW_courses_getCourseList();
                $all_course_ids = array_keys( $all_courses );
                // Exclude the current course.
                $all_course_ids_without_current = array_diff( $all_course_ids, [ $course->course_id ] );
                // If an alternative exists, add it.
                if ( ! empty( $all_course_ids_without_current ) ) {
                    $alternative_course = array_shift( $all_course_ids_without_current );
                    $sync_course_list[ $alternative_course ] = $alternative_course;
                }
            }
        }
    }

    // If no changes were made (i.e. the specified course was not found), return an error.
    if ( count( $sync_course_list ) === count( $user_course_list ) ) {
        return new WP_Error( 'course_not_found', 'The specified course was not found in the user enrollment.' );
    }

    // Ensure the enrollment sync function exists.
    if ( ! function_exists( 'WPCW_courses_syncUserAccess' ) ) {
        return new WP_Error( 'function_not_exists', 'WPCW_courses_syncUserAccess does not exist.' );
    }

    // Update the user's course access by syncing the new list.
    WPCW_courses_syncUserAccess( $user_id, $sync_course_list, 'sync', false, true );

    // Optionally retrieve user context (if available).
    $context = array();
    if ( class_exists( 'WordPress' ) && method_exists( 'WordPress', 'get_user_context' ) ) {
        $context = WordPress::get_user_context( $user_id );
    } else {
        $context = array( 'user_id' => $user_id );
    }

    return $context;
}

// Optional logging helper
if (!function_exists('wpcw_debug_log')) {
    function wpcw_debug_log($message, $data = null) {
        $log_dir = WP_CONTENT_DIR . '/logs';
        $log_file = $log_dir . '/wpcw-debug.log';

        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $output = "[$timestamp] $message";

        if ($data !== null) {
            $output .= "\n" . print_r($data, true);
        }

        $output .= "\n" . str_repeat('-', 80) . "\n";
        file_put_contents($log_file, $output, FILE_APPEND);
    }
}

// Trigger when a unit is completed
add_action('wpcw_user_completed_unit', function($user_id, $unit_id, $unitParentData) {
    $user = get_userdata($user_id);

    $data = [
        'hook'         => 'wpcw_user_completed_unit',
        'user_id'      => $user_id,
        'username'     => $user->user_login,
        'email'        => $user->user_email,
        'unit_id'      => $unit_id,
        'unit_title'   => get_the_title($unit_id),
        'module_id'    => $unitParentData->module_id ?? '',
        'module_title' => $unitParentData->module_title ?? '',
        'course_id'    => $unitParentData->course_id ?? '',
        'course_title' => $unitParentData->course_title ?? '',
    ];

    // wpcw_debug_log('Hook Fired (Unit)', $data);
    do_action('flowmattic_trigger_wpcw_user_completed_unit', $data);
}, 10, 3);

// Trigger when a module is completed (via notification hook for full context)
add_action('wpcw_user_completed_module_notification', function($unitParentData, $userDetails, $adminSubject, $adminBody) {
    $data = [
        'hook'         => 'wpcw_user_completed_module_notification',
        'user_id'      => $userDetails->ID,
        'username'     => $userDetails->user_login,
        'email'        => $userDetails->user_email,
        'module_id'    => $unitParentData->module_id ?? '',
        'module_title' => $unitParentData->module_title ?? '',
        'course_id'    => $unitParentData->course_id ?? '',
        'course_title' => $unitParentData->course_title ?? '',
        'course_link'  => get_permalink($unitParentData->course_id ?? ''),
    ];

    // wpcw_debug_log(' Hook Fired (Module)', $data);
    do_action('flowmattic_trigger_wpcw_user_completed_module', $data);
}, 10, 4);

// Trigger when a course is completed (via notification hook for full context)
add_action('wpcw_user_completed_course_notification', function($unitParentData, $userDetails, $adminSubject, $adminBody) {
    $data = [
        'hook'         => 'wpcw_user_completed_course_notification',
        'user_id'      => $userDetails->ID,
        'username'     => $userDetails->user_login,
        'email'        => $userDetails->user_email,
        'course_id'    => $unitParentData->course_id ?? '',
        'course_title' => $unitParentData->course_title ?? '',
        'course_link'  => get_permalink($unitParentData->course_id ?? ''),
        'module_id'    => $unitParentData->module_id ?? '',
        'module_title' => $unitParentData->module_title ?? '',
    ];

    // wpcw_debug_log(' Hook Fired (Course)', $data);
    do_action('flowmattic_trigger_wpcw_user_completed_course', $data);
}, 10, 4);

// Trigger when a user is enrolled in one or more courses
add_action('wpcw_enroll_user', function($user_id, $course_ids) {
    $user = get_userdata($user_id);

    if (!is_array($course_ids)) {
        $course_ids = [$course_ids];
    }

    //wpcw_debug_log(' Raw $course_ids from WP Courseware', $course_ids);

    foreach ($course_ids as $course_id) {
        $course_details = function_exists('WPCW_courses_getCourseDetails') 
            ? WPCW_courses_getCourseDetails($course_id) 
            : null;

        $data = [
            'hook'         => 'wpcw_enroll_user',
            'user_id'      => $user_id,
            'username'     => $user->user_login,
            'email'        => $user->user_email,
            'course_id'    => $course_id,
            'course_title' => $course_details->course_title ?? '',
            'course_link'  => isset($course_details->course_post_id)
                                ? get_permalink($course_details->course_post_id)
                                : '',
        ];

        //wpcw_debug_log('Custom Hook Triggered: flowmattic_trigger_wpcw_enroll_user', $data);
        do_action('flowmattic_trigger_wpcw_enroll_user', $data);
    }
}, 10, 2);

