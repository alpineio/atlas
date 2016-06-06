<?php


namespace AlpineIO\Atlas\Tests\Samples;


use AlpineIO\Atlas\Abstracts\AbstractTaxonomy;

class Team extends AbstractTaxonomy {
	protected static $postTypes = [Team::class];
}