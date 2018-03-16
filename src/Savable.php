<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Realty\Advert;

/**
 * Savable abstract class
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Savable.php $
 */

abstract class Savable
    {

	/**
	 * Construct Savable
	 *
	 * @param Advert $advert Advert to save
	 *
	 * @return void
	 */

	abstract public function __construct(Advert $advert);


	/**
	 * Save data to any storage
	 *
	 * @return void
	 */

	abstract public function save();


    } //end class


?>
