<?php


namespace AlpineIO\Atlas\Traits;


trait PiklistPostRegistration {
	static function selfRegister() {
		add_filter( 'piklist_post_types', [ static::class, 'piklistPostTypeFilter' ] );
		static::addFilters();
	}

	public static function piklistPostTypeFilter( $postTypes ) {
		//dd(static::getPostType());
		$postTypes[ static::getPostType() ] = array(
			'labels'        => static::getLabels(),
			'public'        => true,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'rewrite'       => array(
				'slug' => static::getSlug()
			),
			'supports'      => array(
				'title',
				'editor',
				//'author',
				'thumbnail',
				'revisions'
			),
			'menu_icon'     => static::$icon,
			/*
			'taxonomies' => [
				'team'	
			],
			*/
			'hide_meta_box' => false
			/*
			'hide_meta_box' => array(
				//'slug',
				//'author',
				//'revisions',
				//'comments',
				//'commentstatus'
			),
			*/
		);

		return $postTypes;
	}

	public static function getLabels() {
		if ( ! function_exists( 'piklist' ) ) {
			// TODO some error or defaults
		}
		$labels = piklist( 'post_type_labels', ucwords( str_replace( '-', ' ', static::getPostType() ) ) );
		if ( isset( static::$labels ) ) {
			return array_merge( $labels, static::$labels );
		}

		return $labels;
	}
}
