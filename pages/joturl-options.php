<?php

$submit = JotUrl::getRequest( 'set_account' );
if ( ! empty( $submit ) ) {
	$hasErrors = false;

	$user_email = JotUrl::getRequest( 'user_email' );
	if ( empty( $user_email ) || ! is_email( $user_email ) ) {
		JotUrl::addError( 'user_email', __( 'Invalid email', 'joturl-link-shortener' ) );
		$hasErrors = true;
	}

	$public_key = preg_replace( '/[^a-f0-9]/ui', '', JotUrl::getRequest( 'public_key' ) );
	if ( empty( $public_key ) || strlen( $public_key ) != 32 ) {
		JotUrl::addError( 'public_key', __( 'Invalid public key', 'joturl-link-shortener' ) );
		$hasErrors = true;
	}

	$private_key = preg_replace( '/[^a-f0-9]/ui', '', JotUrl::getRequest( 'private_key' ) );
	if ( empty( $private_key ) || strlen( $private_key ) != 32 ) {
		JotUrl::addError( 'private_key', __( 'Invalid private key', 'joturl-link-shortener' ) );
		$hasErrors = true;
	}

	if ( ! $hasErrors ) {
		try {
			$joturl = new JotUrlSDK( $user_email, $public_key, $private_key );

			if ( $joturl->login( true ) ) {
				JotUrl::setConfiguration( array( 'user_email' => $user_email, 'public_key' => $public_key, 'private_key' => $private_key ) );

				JotUrl::addMessage( 'generic', __( 'Login credentials are correct and the plug is correctly connected to JotUrl.', 'joturl-link-shortener' ) );
			}
		}
		catch ( Throwable $t ) {
			JotUrl::addError( 'generic', __( 'An error occurred while trying to access JotUrl, did you enter the correct information?', 'joturl-link-shortener' ) . ' (' . $t->getMessage() . ')' );
		}

	}
} else {
	$submit = JotUrl::getRequest( 'reset_account' );
	if ( ! empty( $submit ) ) {
		JotUrl::resetConfiguration();
	}
}

$user_email = JotUrl::getConfiguration( 'user_email' );
$public_key = JotUrl::getConfiguration( 'public_key' );
$private_key = JotUrl::getConfiguration( 'private_key' );

$configured = JotUrl::isConfigured();

?>
<div id="joturl-container" class="wrap">
	<?php
	JotUrl::emitErrors();
	?>
	<?php
	JotUrl::emitMessages();
	?>
    <h2><?php _e( 'JotUrl Settings', 'joturl-link-shortener' ); ?></h2>
    <form method="post">
		<?php
		if ( ! $configured ) {
			?>
            <p>
				<?php _e( 'Please enter your JotUrl account and API keys here to activate the plugin.', 'joturl-link-shortener' ); ?>
            </p>
			<?php
		}
		?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php _e( 'The email address you use to login into JotUrl', 'joturl-link-shortener' ); ?><span class="mandatory">*</span></th>
                <td><input type="text" class="regular-text" name="user_email" value="<?php echo esc_attr( $user_email ); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Public API key', 'joturl-link-shortener' ); ?><span class="mandatory">*</span></th>
                <td><input type="text" class="regular-text" name="public_key" value="<?php echo esc_attr( $public_key ); ?>"></td>
            </tr>
            <tr>
                <th><?php _e( 'Private API key', 'joturl-link-shortener' ); ?><span class="mandatory">*</span></th>
                <td><input type="password" class="regular-text" name="private_key" value="<?php echo esc_attr( $private_key ); ?>"></td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="set_account" class="button button-primary" value="<?php echo esc_attr( __( 'Set login credentials', 'joturl-link-shortener' ) ); ?>">
			<?php
			if ( $configured ) {
				?>
                &emsp;
                <input type="submit" name="reset_account" class="button button-secondary"
                       value="<?php echo esc_attr( __( 'Remove login credentials', 'joturl-link-shortener' ) ); ?>">
				<?php
			} else {
				?>
                &emsp;
                <a class="button button-secondary" target="_blank"
                   href="https://joturl.com/reserved/settings.html#tools-api"><?php _e( 'Retrieve API keys', 'joturl-link-shortener' ); ?></a>
				<?php
			}
			?>
        </p>
    </form>
    <br><br>
	<?php
	if ( ! $configured ) {
		?>
        <table>
            <tbody>
            <tr>
                <td><h3><?php _e( "Don't have a JotUrl account yet?", 'joturl-link-shortener' ); ?></h3></td>
                <td>&emsp;</td>
                <td><a class="button button-primary" target="_blank" href="https://joturl.com/reserved/signup.html"><?php _e( 'Sign Up', 'joturl-link-shortener' ); ?></a></td>
            </tr>
            </tbody>
        </table>
		<?php
	}
	?>
</div>