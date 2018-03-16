<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Gis\AddressSlicer;
use \Agentzilla\Gis\GisInfo;
use \Agentzilla\Realty\Advert;
use \AdService\BigFile;
use \Logics\Foundation\SQL\SQL;
use \SimpleXMLElement;

/**
 * Classify advert class
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Classify.php $
 */

class Classify extends Advert
    {

	/**
	 * Database connection
	 *
	 * @var MySQLDatabase
	 */
	private $_db;

	/**
	 * GIS information
	 *
	 * @var GisInfo
	 */
	private $_gis;

	/**
	 * Address slicer
	 *
	 * @var AddressSlicer
	 */
	private $_slicer;

	/**
	 * Construct class to work
	 *
	 * @param SimpleXMLElement $xml Advert XML
	 *
	 * @return void
	 */

	public function __construct(SimpleXMLElement $xml)
	    {
		parent::__construct($xml);

		$this->_db     = SQL::get("MySQL");
		$this->_slicer = new AddressSlicer($this->city . ", " . $this->address);
		$this->_gis    = new GisInfo($this->city, $this->city . ", " . $this->address);

		$this->defineDistrict();
		$this->createXML();
	    } //end __construct()


	/**
	 * Classify
	 *
	 * @return array Result of classify
	 */

	public function classify():array
	    {
		$this->person = "owner";

		$nofloors = [
		    "garage",
		    "commercial",
		    "land",
		    "house",
		];

		if (in_array($this->type, $nofloors) === false)
		    {
			$settings = [
			    "dateFrequency" => 25,
			    "names"         => 20,
			    "hard"          => 30,
			    "floors"        => 25,
			];

			$methods = [
			    "dateFrequency" => "_getDateFrequency",
			    "names"         => "_getNames",
			    "hard"          => "_hardFilter",
			    "floors"        => "_checkFloors",
			];
		    }
		else
		    {
			$settings = [
			    "dateFrequency" => 45,
			    "names"         => 25,
			    "hard"          => 30,
			];

			$methods = [
			    "dateFrequency" => "_getDateFrequency",
			    "names"         => "_getNames",
			    "hard"          => "_hardFilter",
			];
		    }

		$result = 0;
		foreach ($settings as $key => $value)
		    {
			$method = $methods[$key];
			$res    = $this->$method();
			if ($res !== null)
			    {
				if ($res === true)
				    {
					$result += $value;
				    } //end if

			    } //end if

		    } //end 

		if (isset($this->comission) === true && $this->operation === "rent")
		    {
			if ($this->comission > 0)
			    {
				$result -= 100;
			    } //end if

		    } //end if

		if ($this->_inBlacklist() === true)
		    {
			$result -= 100;
		    } //end if

		$amendment = $this->_getAmendment();
		$result    = $result - $amendment;

		$this->person = (($result >= 70) ? "owner" : "agency");

		$this->createXML();

		return [
		    "total_score" => $result,
		    "person"      => $this->person,
		];
	    } //end classify()


	/**
	 * Get amendment
	 *
	 * @return int Amendment
	 */

	private function _getAmendment()
	    {
		$amendment = 0;

		$objects = $this->_getAddresses();
		if ($objects > 1)
		    {
			$amendment = $objects * 5;
		    } //end if

		return $amendment;
	    } //end _getAmendment()


	/**
	 * Define district
	 *
	 * @return void
	 */

	public function defineDistrict()
	    {
		if ($this->_slicer->valid() === true)
		    {
			$this->address = $this->_slicer->street . ", " . $this->_slicer->house;

			if ($this->_gis->district !== null)
			    {
				$this->district = $this->_gis->district;
				$this->_gis->setConnection($this->_db);
				$this->_gis->write();
			    }
			else
			    {
				$this->_gis->setConnection($this->_db);
				if ($this->_gis->location === null && isset($this->lat) === true && isset($this->lang) === true)
				    {
					$this->_gis->setLocation(["lat" => $this->lat, "lang" => $this->lang]);
				    } //end if

				$this->_gis->defineDistrict();
				if ($this->_gis->district !== null)
				    {
					$this->district = $this->_gis->district;
					$this->_gis->write();
				    } //end if

			    } //end if

		    } //end if

	    } //end defineDistrict()


	/**
	 * Check floors of object and floors in advert
	 *
	 * @return mixed Result of check
	 */

	private function _checkFloors()
	    {
		if (isset($this->floors) === true)
		    {
			$from = $this->floors - 1;
			$to   = $this->floors + 1;

			if ($this->_gis->floors !== null && isset($this->floors) === true)
			    {
				if ($this->_gis->floors >= $from && $this->_gis->floors <= $to)
				    {
					return true;
				    }
				else
				    {
					return false;
				    } //end if

			    }
			else
			    {
				return null;
			    } //end if

		    }
		else
		    {
			return null;
		    } //end if

	    } //end _checkFloors()


	/**
	 * Hard filter, search by black and white lists
	 *
	 * @return bool Result of check
	 */

	private function _hardFilter()
	    {
		$check = true;

		$blackxml = new SimpleXMLElement(file_get_contents(__DIR__ . "/storage/blacklist.xml"));

		foreach ($blackxml->patterns->pattern as $pattern)
		    {
			if (isset($this->name) === true)
			    {
				if (preg_match((string) $pattern, $this->name) > 0)
				    {
					$check = false;
					break;
				    } //end if

			    } //end if

			if (preg_match((string) $pattern, $this->description) > 0)
			    {
				$check = false;
				break;
			    } //end if

		    } //end foreach

		if ($check !== false)
		    {
			foreach ($blackxml->words->word as $word)
			    {
				if (isset($this->name) === true)
				    {
					if (preg_match("/" . (string) $word . "/ui", $this->name) > 0)
					    {
						$check = false;
						break;
					    } //end if

				    } //end if

			    } //end foreach

		    } //end if

		if ($check === false)
		    {
			$whitexml = new SimpleXMLElement(file_get_contents(__DIR__ . "/storage/whitelist.xml"));

			foreach ($whitexml->patterns->pattern as $pattern)
			    {
				if (preg_match((string) $pattern, $this->description) > 0)
				    {
					$check = true;
					break;
				    } //end if

			    } //end foreach

		    } //end if

		return $check;
	    } //end _hardFilter()


	/**
	 * Check phone in blacklist
	 *
	 * @return bool Status
	 */

	private function _inBlacklist()
	    {
		$result = $this->_db->exec("SELECT COUNT(`phone`) as count FROM `blacklist` WHERE `phone` = '" . $this->phone . "'");
		$row    = $result->getRow();
		$count  = $row['count'];

		if($count > 0)
		    {
			return true;
		    }
		else
		    {
			return false;
		    } //end if

	    } //end _inBlacklist()


	/**
	 * Get date frequency
	 *
	 * @return int Date frequency
	 */

	private function _getDateFrequency()
	    {
		$dates   = array();
		$copies  = 0;
		$objects = 0;

		$big     = new BigFile("base-" . $this->phone, 10);
		$obj     = $big->getRecords();
		$objects = count($obj);
		$dates   = array_merge($dates, $obj);

		$big    = new BigFile("copy-" . $this->phone, 10);
		$cop    = $big->getRecords();
		$copies = count($cop);
		$dates  = array_unique(array_merge($dates, $cop));

		$in = array();
		foreach ($dates as $date)
		    {
			$in[] = strtotime($date);
		    } //end foreach

		$dates = array();
		if (count($in) > 0)
		    {
			sort($in, SORT_NUMERIC);
			foreach ($in as $time)
			    {
				$dates[] = date("d.m.Y", $time);
			    } //end foreach

			$previousdate  = $dates[0];
			$sum           = 0;
			$difference    = 0;
			$count = count($dates);
			foreach ($dates as $date)
			    {
				if ($date !== $previousdate)
				    {
					$difference   = strtotime($date) - strtotime($previousdate);
					$previousdate = $date;
					$sum          = $difference + $sum;
				    } //end if

			    } //end foreach

			$frequency = (($count > 1) ? ($sum/($count * 86400)) : 365);

			return (($frequency > 2) ? true : false);
		    }
		else
		    {
			return true;
		    } //end if

	    } //end _getDateFrequency()


	/**
	 * Get names by phone, check it
	 *
	 * @return bool Check result
	 */

	private function _getNames()
	    {
		$result = array();
		$rows = $this->_db->exec("SELECT * FROM `names` WHERE `phone` = '" . $this->phone . "'");
		while($row = $rows->getRow())
		    {
			$result[] = mb_strtoupper($row["name"]);
		    } //end while

		$result = array_unique($result);

		if (count($result) > 1)
		    {
			return false;
		    }
		else
		    {
			return true;
		    } //end if

	    } //end _getNames()


	/**
	 * Get addresses
	 *
	 * @return int Unique addresses on phone
	 */

	private function _getAddresses()
	    {
		$hashes = [];
		$rows = $this->_db->exec("SELECT `city`, `address` FROM `addresses` WHERE `phone` = '" . $this->phone . "'");
		while($row = $rows->getRow())
		    {
			$address = $row["city"] . ", " . $row["address"];
			$sliced  = new AddressSlicer($address);

			if ($sliced->valid() === true && isset($hashes[$sliced->hash]) === false)
			    {
				$hashes[$sliced->hash] = $address;
			    } //end if

		    } //end while

		return count($hashes);
	    } //end _getNames()


    } //end class

?>
