<?php
defined( 'ABSPATH' ) or exit;

function paddle_get_notices( $notice_type = '' ) {
    $notices = get_option( 'paddle_notices', array() );
    if ( empty( $notice_type ) ) {
        return $notices;
    } elseif ( isset( $notices[ $notice_type ] ) ) {
		$notices = $notices[ $notice_type ];
	} else {
        $notices = array();
    }

    return $notices;
}

function paddle_add_notice( $message, $notice_type = 'success', $data = array() ) {
    $notices = paddle_get_notices();

    $message = apply_filters( 'paddle_add_notice_' . $notice_type, $message );

    if ( ! empty( $message ) ) {
		$notices[ $notice_type ][] = array(
			'notice' => $message,
			'data'   => $data,
		);
	}

    update_option( 'paddle_notices', $notices );
}
