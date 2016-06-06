<?php


namespace AlpineIO\Atlas\Services;


use AlpineIO\Atlas\Contracts\SelfRegistration;

class RegisterWordPressTypes {
	public static function register($models = []) {
		$types = apply_filters( 'alpineio_atlas_models', $models );
		foreach ($types as $type ) {
			if (class_exists( $type ) &&  (new \ReflectionClass( $type ))->implementsInterface( SelfRegistration::class ) ) {
				/** @var $type SelfRegistration */
				$type::selfRegister();
				// Fire some awesome action to let the world know we are registered
				// Skip to the next
				continue;
			}
			// Maybe display a message about the problem
			if ( ! class_exists( $type )) {
				global $alpineio_atlas;
				$alpineio_atlas['errors'][] = $type;
				add_action( 'admin_notices', [static::class, 'classNotFoundError']);
			}
		}	
	}

	public static function classNotFoundError() {
		global $alpineio_atlas;
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( sprintf('Class Not Found: Atlas could not find the %s class. Check php autoloader.', implode( ', ',
					$alpineio_atlas['errors'] )), 'alpineio-atlas' ); ?></p>
		</div>
		<?php
	}
}