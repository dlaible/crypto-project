<?php
/**
 * Handles Comment Post to WordPress and prevents duplicate comment posting.
 *
 * @package WordPress
 */

if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
	header('Allow: POST');
	header('HTTP/1.1 405 Method Not Allowed');
	header('Content-Type: text/plain');
	exit;
}

/** Sets up the WordPress Environment. */
require( dirname(__FILE__) . '/wp-load.php' );

nocache_headers();

/* ----------------------------------------------
 * Crypto-project Modifications
 * --------------------------------------------*/
$comment = $comment_msg = null;
if ( !cp_use_secure_methods() ) {
	
	/* Insert comment using simple sql query to allow for sql injection */
	global $wpdb;

	if ( isset( $_POST['comment'] ) && is_string( $_POST['comment'] ) ) {
        $comment_msg = trim( $_POST['comment'] );
	}

	// Raw sql for comment insert
	$sql = 
		"
		INSERT INTO `a7415375_cdb`.`cwp_comments`
			(`comment_ID`, `comment_post_ID`, `comment_author`, 
				`comment_author_email`, `comment_author_url`,
				`comment_author_IP`, `comment_date`, `comment_date_gmt`,
				`comment_content`, `comment_karma`, `comment_approved`,
				`comment_agent`, `comment_type`, `comment_parent`, `user_id`)
			VALUES (NULL, '1', 'hacker', 'prachtaine33@gmail.com', '', '',
				'2016-10-26 12:00:00', '2016-10-26 10:00:00', '" . $comment_msg . "',
				'0', '1', '', '', '0', '0');
		";

	// Execute un-prepared sql statement
	$wpdb->query( $sql );

	// Retrieve data from the new comment
	$id = (int) $wpdb->insert_id;
	$comment = get_comment( $id );

} else {
	// Original code
	$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
	if ( is_wp_error( $comment ) ) {
		$data = intval( $comment->get_error_data() );
		if ( ! empty( $data ) ) {
			wp_die( '<p>' . $comment->get_error_message() . '</p>', __( 'Comment Submission Failure' ), array( 'response' => $data, 'back_link' => true ) );
		} else {
			exit;
		}
	}
}

$user = wp_get_current_user();

/**
 * Perform other actions when comment cookies are set.
 *
 * @since 3.4.0
 *
 * @param WP_Comment $comment Comment object.
 * @param WP_User    $user    User object. The user may not exist.
 */
do_action( 'set_comment_cookies', $comment, $user );

$location = empty( $_POST['redirect_to'] ) ? get_comment_link( $comment ) : $_POST['redirect_to'] . '#comment-' . $comment->comment_ID;

/**
 * Filters the location URI to send the commenter after posting.
 *
 * @since 2.0.5
 *
 * @param string     $location The 'redirect_to' URI sent via $_POST.
 * @param WP_Comment $comment  Comment object.
 */
$location = apply_filters( 'comment_post_redirect', $location, $comment );

wp_safe_redirect( $location );
exit;
