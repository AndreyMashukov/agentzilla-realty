<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Realty\Advert;
use \AdService\BigFile;
use \Agentzilla\Realty\Savable;

/**
 * Advert tracker - allow to track seller adverts dates
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Tracker.php $
 */

class Tracker extends Savable
    {

	/**
	 * Advert
	 *
	 * @var Advert
	 */
	private $_advert;

	/**
	 * Construct deduplicator data
	 *
	 * @param Advert $advert Deduplicated advert
	 *
	 * @return void
	 */

	public function __construct(Advert $advert)
	    {
		$this->_advert = $advert;
	    } //end __construct()


	/**
	 * Save data
	 *
	 * @return void
	 */

	public function save()
	    {
		if (isset($this->_advert->date) === true && isset($this->_advert->phone) === true)
		    {
			$bigfile = new BigFile("base-" . $this->_advert->phone, 10);
			$bigfile->addrecord($this->_advert->date);
		    } //end if

	    } //end save()


    } //end class


?>
