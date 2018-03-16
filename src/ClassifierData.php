<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Gis\AddressSlicer;
use \Agentzilla\Realty\Advert;
use \Agentzilla\Realty\Savable;
use \Logics\Foundation\SQL\SQL;
use \AM\Container\Container;

/**
 * Classifier data class
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/ClassifierData.php $
 */

class ClassifierData extends Savable
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
	 * Construct classifier data
	 *
	 * @param Advert $advert Classified advert
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
	 * @return void
	 */

	public function save()
	    {
		$this->_saveAddress();
		$this->_saveName();
		$this->_saveAgency();
	    } //end save()


	/**
	 * Save address
	 *
	 * @return void
	 */

	private function _saveAddress()
	    {
		$slicer = new AddressSlicer($this->_advert->city . ", " . $this->_advert->address);

		if ($slicer->valid() === true && isset($this->_advert->phone) === true)
		    {
			$hash = sha1($slicer->hash . $this->_advert->phone);
			if ($this->_exists("addresses", $hash) === false)
			    {
				$this->_db->exec("INSERT INTO `addresses` SET " .
				    "`city` = '"    . $this->_advert->city . "', " .
				    "`hash` = '"    . $hash . "', " .
				    "`address` = '" . $slicer->address . "', " .
				    "`phone` = '"   . $this->_advert->phone . "'"
				);
			    } //end if

		    }
		else if (isset($this->_advert->phone) === true)
		    {
			$hash = sha1($this->_advert->city . ", " . $this->_advert->address . $this->_advert->phone);
			if ($this->_exists("addresses", $hash) === false)
			    {
				$this->_db->exec("INSERT INTO `addresses` SET " .
				    "`city` = '"    . $this->_advert->city . "', " .
				    "`hash` = '"    . $hash . "', " .
				    "`address` = '" . $this->_advert->address . "', " .
				    "`phone` = '"   . $this->_advert->phone . "'"
				);
			    } //end if

		    } //end if

	    } //end _saveAddress()


	/**
	 * Save agency
	 *
	 * @return void
	 */

	private function _saveAgency()
	    {
		$hash = sha1($this->_advert->city . $this->_advert->phone);
		if ($this->_exists("agencies", $hash) === false && $this->_advert->person === "agency")
		    {
			$this->_db->exec("INSERT INTO `agencies` SET " .
			    "`city` = '"    . $this->_advert->city . "', " .
			    "`hash` = '"    . $hash . "', " .
			    "`name` = '" . mb_strtoupper($this->_advert->name) . "', " .
			    "`phone` = '"   . $this->_advert->phone . "'"
			);

			$agency = [
			    "name"  => mb_strtoupper($this->_advert->name),
			    "phone" => $this->_advert->phone,
			    "city"  => $this->_advert->city,
			];

			$cities = [
			    "Москва",
			    "Санкт-Петербург",
			    "Иркутск",
			    "Красноярск",
			];

			if (in_array(trim($this->_advert->city), $cities) === true)
			    {
				$container = new Container("agency_send_sms");
				$container->add(json_encode($agency));
			    } //end if

		    } //end if

	    } //end _saveAgency()


	/**
	 * Save name
	 *
	 * @return void
	 */

	private function _saveName()
	    {
		if (isset($this->_advert->phone) === true && isset($this->_advert->name) === true)
		    {
			$hash = sha1(mb_strtoupper($this->_advert->name) . $this->_advert->phone);

			if ($this->_exists("names", $hash) === false && mb_strtoupper($this->_advert->name) !== "НЕ УКАЗАНО")
			    {
				$this->_db->exec("INSERT INTO `names` SET " .
				    "`hash` = '"  . $hash . "', " .
				    "`name` = '"  . mb_strtoupper($this->_advert->name) . "', " .
				    "`phone` = '" . $this->_advert->phone . "'"
				);
			    } //end if

		    } //end if

	    } //end _saveName()


	/**
	 * Exists row
	 *
	 * @return bool Result
	 */

	private function _exists(string $table, string $hash)
	    {
		$result = $this->_db->exec("SELECT COUNT(*) AS `count` " .
		    "FROM `" . $table . "` WHERE `hash` = '" . $hash . "'"
		);

		$row   = $result->getRow();
		$count = (int) $row["count"];

		if ($count > 0)
		    {
			return true;
		    }
		else
		    {
			return false;
		    } //end if

	    } //end _exists()


    } //end class


?>
