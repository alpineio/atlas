<?php


namespace AlpineIO\Atlas\Traits;


trait PiklistTaxonomyRegistration {
	static function selfRegister() {
		add_filter( 'piklist_taxonomies', [ static::class, 'piklistRegisterTaxonomy' ] );
	}

	public static function piklistRegisterTaxonomy( $taxonomies ) {
		$settings = array(
			'post_type'         => static::getPostTypeSlugs(),
			'name'              => static::getTaxonomy(),
			'show_admin_column' => true,
			'public'            => true,
            'hierarchical'      => true,
			'show_ui'           => true,
			'configuration'     => array(
				'hierarchical'  => true,
				'labels'        => static::getLabels(),
				'hide_meta_box' => false,
				'show_ui'       => true,
				'query_var'     => true,
				'rewrite'       => array(
					'slug' => static::getSlug()
				)
			)
		);
		if ( static::hasSettings() ) {
			$settings = array_merge_recursive( $settings, static::getSettings() );
		}
		$taxonomies[] = $settings;
		return $taxonomies;
	}
}
