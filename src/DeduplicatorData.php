<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Gis\AddressSlicer;
use \Agentzilla\Realty\Advert;
use \DateTime;
use \DateTimezone;
use \Agentzilla\Realty\Savable;
use \Logics\Foundation\SQL\SQL;

/**
 * Deduplicator data class - for save deduplicator data
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/DeduplicatorData.php $
 */

class DeduplicatorData extends Savable
    {

	/**
	 * Advert
	 *
	 * @var Advert
	 */
	private $_advert;

	/**
	 * MySQL
	 *
	 * @var MySQLdatabase
	 */
	private $_db;

	/**
	 * Construct deduplicator data
	 *
	 * @param Advert $advert Deduplicated advert
	 *
	 * @return void
	 */

	public function __construct(Advert $advert)
	    {
		$this->_db     = SQL::get("MySQL");
		$this->_advert = $advert;
	    } //end __construct()


	/**
	 * Save data
	 *
	 * @param bool $savephoto Data to save photo
	 *
	 * @return void
	 */

	public function save($savephoto = true)
	    {
		if ($savephoto === false)
		    {
			$this->_advert->photos = [];
			$this->_advert->createXML();
		    } //end if

		$datetime = new DateTime($this->_advert->date, new DateTimezone("UTC"));
		$date     = $datetime->format("Y-m-d H:i:s");

		$this->_db->execBinaryBlob(
		    "INSERT INTO `adverts` SET " .
		    "`advert` = ?, " .
		    "`lat` = " . $this->_advert->lat . ", " .
		    "`lang` = '" . $this->_advert->lang . "', " .
		    "`date` = '" . $date . "'",
		    $this->_advert->doc->saveXML()
		);
	    } //end save()


    } //end class


?>
