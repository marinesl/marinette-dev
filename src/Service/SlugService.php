<?php 

/**
 * Service de création de slug
 * 
 * Méthodes :
 * - slug() : Transformation d'un title en slug
 */

declare(strict_types=1);

namespace App\Service;


class SlugService
{
    public function __construct() {}

	/**
	 * Transformation d'un title en slug
	 * 
	 * @param string title : titre de l'élément à transformer en slug
	 * 
	 * @return string slug : titre transformé en slug
	 */
    public function slug($title)
	{
		$slug  = mb_strtolower( $title, 'UTF-8' ); // lowercase cyrillic letters too
        $slug  = preg_replace( '/[ _.]+/', '-', trim( $slug ) ); // Replace " ", "_", "." by "-"
		$slug  = preg_replace( '/[^A-Za-z0-9-]/', '', $slug ); // Remove all caracters not letters, not number and not "_", "-"
		$slug  = trim( $slug, '-' ); // Remove spaces at start and end
		return $slug;
	}


    
}