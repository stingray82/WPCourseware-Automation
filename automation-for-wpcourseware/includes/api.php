<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function () {
    register_rest_route( 'wpc-automation/v1', '/enroll', [
        'methods'  => 'POST',
        'callback' => 'wpc_automation_api_enroll_user',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route( 'wpc-automation/v1', '/remove', [
        'methods'  => 'POST',
        'callback' => 'wpc_automation_api_remove_user',
        'permission_callback' => '__return_true',
    ]);
});

function wpc_automation_api_validate_key( $request ) {
    $provided_key = $request->get_header( 'X-WPC-Automation-Key' );
    $stored_key   = get_option( 'wpc_automation_api_key' );

    if ( empty( $provided_key ) || $provided_key !== $stored_key ) {
        return new WP_Error( 'unauthorized', 'Invalid or missing API key.', [ 'status' => 401 ] );
    }

    return true;
}

function wpc_automation_api_enroll_user( WP_REST_Request $request ) {
    $auth = wpc_automation_api_validate_key( $request );
    if ( is_wp_error( $auth ) ) return $auth;

    $email     = sanitize_email( $request->get_param( 'email' ) );
    $course_id = intval( $request->get_param( 'course_id' ) );
    $username  = sanitize_user( $request->get_param( 'username' ) );

    if ( empty( $email ) || empty( $course_id ) ) {
        return new WP_Error( 'missing_fields', 'Email and course_id are required.', [ 'status' => 400 ] );
    }

    $result = create_or_enroll_user_in_course_by_course_id( $email, $course_id, $username );
    if ( is_wp_error( $result ) ) {
        return new WP_REST_Response([ 'success' => false, 'message' => $result->get_error_message() ], 400);
    }

    return new WP_REST_Response([ 'success' => true, 'data' => $result ]);
}

function wpc_automation_api_remove_user( WP_REST_Request $request ) {
    $auth = wpc_automation_api_validate_key( $request );
    if ( is_wp_error( $auth ) ) return $auth;

    $email     = sanitize_email( $request->get_param( 'email' ) );
    $course_id = intval( $request->get_param( 'course_id' ) );

    if ( empty( $email ) || empty( $course_id ) ) {
        return new WP_Error( 'missing_fields', 'Email and course_id are required.', [ 'status' => 400 ] );
    }

    $result = remove_user_from_course_by_course_id( $email, $course_id );
    if ( is_wp_error( $result ) ) {
        return new WP_REST_Response([ 'success' => false, 'message' => $result->get_error_message() ], 400);
    }

    return new WP_REST_Response([ 'success' => true, 'data' => $result ]);
}
