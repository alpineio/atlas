<?php


namespace AlpineIO\Atlas\Tests\Samples;


use AlpineIO\Atlas\Abstracts\AbstractTaxonomy;

/**
 * Class Team
 * @package AlpineIO\Atlas\Tests\Samples
 * @property string $exampleField
 */
class Team extends AbstractTaxonomy {
	protected static $postTypes = [Team::class];
}