<?php 

class UserRegistrationHook{
    private $user;
    
    function __construct($user_id) {
        $this->user = new WP_User( $user_id );
    }

    function send_verification_mail() {
        $recipient = $user->user_email;
        if ( '' === $recipient ) {
            return;
        }
        
        $content = $this->get_email_content( array(
            'user_id' => $user_id,
            'context' => 'confirmation_email',
            'content' => alg_wc_ev()->core->emails->get_default_email_content('confirmation'),
            'heading' => __( 'Your account has been activated', 'emails-verification-for-woocommerce' )
        ) );
        $subject           = $this->get_email_subject( array(
            'user_id' => $user_id,
            'context' => 'confirmation_email',
            'subject' => '[%site_title%]: ' . __( 'Your account has been activated successfully', 'emails-verification-for-woocommerce' )
        ) );
        $wc_email_template = get_option( 'alg_wc_ev_wc_email_template', 'simulation' );
        $email_template    = get_option( 'alg_wc_ev_email_template', 'plain' );
        
        if ( in_array( $email_template, array( 'wc', 'smart' ) ) && 'real_wc_email' === $wc_email_template ) {
            do_action( 'alg_wc_ev_trigger_confirmation_wc_email', $user_id );
        } else {
            $this->send_mail( $recipient, $subject, $content );
        }
        
        $data = array( 'confirmation_email_sent' => time() );
        alg_wc_ev()->core->update_activation_code_data( $user_id, $args['code'], $data );
        
    }
    
    /**
     * get_email_content.
     *
     * @version 2.6.9
     * @since   1.8.0
     * @todo    (maybe) `$user->user_url`, `$user->user_registered`
     *
     * @param null $args
     *
     * @return string
     */
    function get_email_content( $args = null ) {
        $args = wp_parse_args( $args, array(
            'user_id'                 => '',
            'code'                    => false,
            'content'                 => $this->get_default_email_content( 'activation' ),
            'heading'                 => __( 'Activate your account', 'emails-verification-for-woocommerce' ),
            'context'                 => 'activation_email_separate',
            'placeholders'            => alg_wc_ev_get_common_placeholders(),
            'verification_check_page' => 'myaccount', // myaccount | checkout
        ) );
        $verification_check_page = $args['verification_check_page'];
        $user_id                 = $args['user_id'];
        $code                    = $args['code'];
        $placeholders            = array_merge( $args['placeholders'], alg_wc_ev_get_user_placeholders( array( 'user_id' => $user_id ) ) );
        if ( $args['code'] ) {
            $placeholders['%verification_url%'] = $this->get_verification_url( array(
                'user_id'                 => $user_id,
                'code'                    => $code,
                'verification_check_page' => $verification_check_page
            ) );
        }
        $content = apply_filters( 'alg_wc_ev_email_content', $args['content'], $args );
    
        return apply_filters( 'alg_wc_ev_email_content_final', str_replace( array_keys( $placeholders ), $placeholders, $content ), $args );
    }

    /**
	 * get_default_email_content.
	 *
	 * @version 2.6.7
	 * @since   2.6.7
	 *
	 * @param $email_type 'activation' | confirmation | admin
	 *
	 * @return string
	 */
	function get_default_email_content( $email_type ) {
		$default_content = '';
		switch ( $email_type ) {
			case 'activation':
				$default_content = sprintf(
					__( '<p>Please <a href="%s" target="_blank">click here</a> to verify your email on %s.</p>', 'emails-verification-for-woocommerce' ),
					'%verification_url%',
					'<a href="%site_url%" target="_blank">%site_title%</a>'
				);
				break;
			case 'confirmation':
				$default_content = sprintf(
					__( '<p>Your account has been activated successfully on %s.</p>', 'emails-verification-for-woocommerce' ),
					'<a href="%site_url%" target="_blank">%site_title%</a>'
				);
				break;
			case 'admin':
				$default_content =
					sprintf(
						__( 'User %s has just verified his email (%s) on %s.', 'emails-verification-for-woocommerce' ),
						'<a href="%admin_user_profile_url%">%user_login%</a>',
						'%user_email%',
						'<a href="%site_url%" target="_blank">%site_title%</a>'
					);
				break;
		}

		return $default_content;
	}

    /**
	 * alg_wc_ev_get_common_placeholders.
	 *
	 * @version 2.6.5
	 * @since   2.6.5
	 *
	 * @return array
	 */
	function alg_wc_ev_get_common_placeholders() {
		$placeholders = array(
			'%site_title%' => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
			'%site_url%'   => wp_parse_url( home_url(), PHP_URL_HOST )
		);

		return apply_filters( 'alg_wc_ev_common_placeholders', $placeholders ); //TODO: filters
        //return $placeholders;
	}
}