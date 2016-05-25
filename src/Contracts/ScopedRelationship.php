<?php


namespace AlpineIO\Atlas\Contracts;


interface ScopedRelationship {
	public function getScope();
	public function setScope( $scope );
}