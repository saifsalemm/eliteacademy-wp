<?php

// extract the token from the request header and return false if not found
function get_token()
{
    $authorizationHeader = '';

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } else if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) { // For servers using proxy
        $authorizationHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    // Parse the Authorization header to extract the token
    if (preg_match('/Bearer (.*)/', $authorizationHeader, $matches)) {
        $token = $matches[1];
        // Process the extracted token
        return $token;
    } else {
        // Handle missing or invalid Authorization header
        return false;
    }
}

function is_authenticated($request)
{
    // Extract token from Authorization header
    $token = get_token();

    if (!$token) {
        return new WP_Error('rest_forbidden', 'Authorization header missing or invalid format', array('status' => 401));
    }

    try {
        $jwt = new EliteJwt();
        $decoded = $jwt->elite_decode($token);
    } catch (Exception $e) {
        return new WP_Error('rest_forbidden', 'Invalid token', array('status' => 401));
    }

    // Access user data from decoded token (replace with actual claim names)
    $user_data = $decoded;

    // If valid, attach user data to request object (similar to Express)
    $request->set_param('user', $user_data);
    $request->set_param('domain', parse_url($_SERVER['HTTP_ORIGIN'])['host']);

    return true;
}

function is_teacher($request)
{
    // Extract token from Authorization header
    $token = get_token();

    if (!$token) {
        return new WP_Error('rest_forbidden', 'Authorization header missing or invalid format', array('status' => 401));
    }

    try {
        $jwt = new EliteJwt();
        $decoded = $jwt->elite_decode($token);
        if ($decoded->role !== 'teacher' && $decoded->role !== 'admin') {
            return new WP_Error('rest_forbidden', 'Not authorized', array('status' => 401));
        }
    } catch (Exception $e) {
        return new WP_Error('rest_forbidden', 'Invalid token', array('status' => 401));
    }

    // Access user data from decoded token (replace with actual claim names)
    $user_data = $decoded;

    // If valid, attach user data to request object (similar to Express)
    $request->set_param('user', $user_data);
    $request->set_param('domain', $_SERVER['SERVER_NAME']);

    return true;
}
