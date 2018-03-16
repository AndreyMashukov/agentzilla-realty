<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Realty\Advert;
use \Agentzilla\HTTP\HTTPclient;
use \AdService\Stamp;
use \AdService\XMLGenerator;
use \DOMDocument;
use \DOMXPath;
use \SimpleXMLElement;
use \DateTime;
use \DateTimezone;

/**
 * Estate class - the estate information consist of many advert data
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Estate.php $
 */

class Estate
    {

	/**
	 * Country
	 *
	 * @var string
	 */
	public $country = null;

	/**
	 * Region
	 *
	 * @var string
	 */
	public $region = null;

	/**
	 * City
	 *
	 * @var string
	 */
	public $city = null;

	/**
	 * Type
	 *
	 * @var string
	 */
	public $type = null;

	/**
	 * Operation
	 *
	 * @var string
	 */
	public $operation = null;

	/**
	 * Descriptions
	 *
	 * @var array
	 */
	public $descriptions = [];

	/**
	 * Prices
	 *
	 * @var array
	 */
	public $prices = [];

	/**
	 * Comissions
	 *
	 * @var array
	 */
	public $comissions = [];

	/**
	 * Sellers
	 *
	 * @var array
	 */
	public $sellers = [];

	/**
	 * Link
	 *
	 * @var array
	 */
	public $links = [];

	/**
	 * Photos
	 *
	 * @var array
	 */
	public $photos = [];

	/**
	 * Doc
	 *
	 * @var XMLGenerator
	 */
	private $_xml;

	/**
	 * Doc
	 *
	 * @var DOMDocument
	 */
	public $doc = null;

	/**
	 * Hash (estate id)
	 *
	 * @var string
	 */
	public $id = "";

	/**
	 * Main props of advert
	 *
	 * @var array Props
	 */

	protected $mainprops = [
		"country",
		"region",
		"city",
		"address",
		"district",
		"lat",
		"lang",
		"type",
		"operation",
		"rooms",
		"area",
		"floor",
		"floors",
		"material",
	    ];

	/**
	 * Prepare
	 *
	 * @param SimpleXMLElement $estate XML estate
	 *
	 * @return void
	 */

	public function __construct(SimpleXMLElement $estate = null)
	    {
		if ($estate !== null)
		    {
			$this->_loadEstate($estate);
			$this->createXML();
		    } //end if

	    } //end __construct()


	/**
	 * Load estate XML
	 *
	 * @param SimpleXMLElement $estate Estate object
	 *
	 * @return void
	 */

	private function _loadEstate(SimpleXMLElement $estate)
	    {
		foreach ($this->mainprops as $prop)
		    {
			if (empty($estate->$prop) === false)
			    {
				$this->$prop = (string) $estate->$prop;
			    } //end if

		    } //end foreach

		$this->_loadEstateData($estate, "description", "descriptions");
		$this->_loadEstateData($estate, "price", "prices");
		$this->_loadEstateData($estate, "link", "links");
		$this->_loadEstateData($estate, "comission", "comissions");

		foreach ($estate->seller as $data)
		    {
			$this->sellers[] = [
			    "seller" => (string) $data,
			    "person" => (string) $data["person"],
			    "phone"  => (string) $data["phone"],
			];
		    } //end foreach

		if (empty($estate->photo) === false)
		    {
			foreach ($estate->photo as $photo)
			    {
				$hash                = sha1((string) $photo);
				$this->photos[$hash] = (string) $photo;
			    } //end foreach

		    } //end if

	    } //end _loadEstate()


	/**
	 * Load estate data
	 *
	 * @param SimpleXMLElement $estate Estate XML
	 * @param string           $field  Name of field
	 * @param array            $prop   Name of prop
	 *
	 * @return void
	 */

	private function _loadEstateData(SimpleXMLElement $estate, string $field, string $prop)
	    {
		foreach ($estate->$field as $data)
		    {
			$this->$prop[] = [
			    $field     => (string) $data,
			    "datetime" => new DateTime((string) $data["datetime"], new DateTimezone("UTC")),
			    "phone"    => (string) $data["phone"],
			];
		    } //end foreach

	    } //end _loadEstateData()


	/**
	 * Load standart properties
	 *
	 * @param Advert $advert   Source of data
	 *
	 * @return void
	 */

	private function _loadStandartProperties(Advert $advert)
	    {
		foreach ($this->mainprops as $prop)
		    {
			if (empty($advert->$prop) === false)
			    {
				$this->$prop = $advert->$prop;
			    } //end if

		    } //end foreach

		$this->_addAdvertData($advert);

	    } //end _loadStandartProperties()


	/**
	 * Add advert data to arrays
	 *
	 * @param Advert $advert Advert to add
	 *
	 * @return void
	 */

	private function _addAdvertData(Advert $advert)
	    {
		if ($this->country === null && $this->region === null && $this->city === null)
		    {
			$this->_loadStandartProperties($advert);
		    } //end if

		$fields = ["description" => "descriptions", "price" => "prices", "comission" => "comissions", "link" => "links"];

		foreach ($fields as $field => $to)
		    {
			if (isset($advert->$field) === true)
			    {
				$datetime = new DateTime($advert->date, new DateTimezone("UTC"));

				$this->$to[$datetime->getTimestamp()] = array(
									  "datetime" => $datetime,
									  "phone"    => $advert->phone,
									  $field     => $advert->$field,
								 );
			    } //end if

			ksort($this->$to);
		    } //end foreach

		if (isset($advert->phone) === true && isset($advert->name) === true)
		    {
			$hash = sha1(mb_strtoupper($advert->name) . $advert->phone);
			if (isset($this->sellers[$hash]) === false)
			    {
				$this->sellers[$hash] = array(
							 "person" => $advert->person,
							 "phone"  => $advert->phone,
							 "seller" => $advert->name,
							);
			    } //end if

		    } //end if

		if (isset($advert->photos) === true)
		    {
			foreach ($advert->photos as $photo)
			    {
				$hash = sha1($photo);
				if (isset($this->photos[$hash]) === false)
				    {
					$this->photos[$hash] = $photo;
				    } //end if

			    } //end foreach

		    } //end if

	    } //end _addAdvertData()


	/**
	 * Add advert
	 *
	 * @param Advert $advert Advert to add
	 *
	 * @return void
	 */

	public function addAdvert(Advert $advert)
	    {
		$this->_addAdvertData($advert);
	    } //end addAdvert()


	/**
	 * Make stamps
	 *
	 * @return void
	 */

	public function makeStamps()
	    {
		if (count($this->photos) > 0)
		    {
			$stamped = [];
			$stamp   = new Stamp();
			foreach ($this->photos as $photo)
			    {
				$stamped[] = $stamp->stamp($photo, [$this->city . ", " . $this->address, "www.AgentZilla.ru"]);
			    } //end foreach

			$this->photos = $stamped;
		    } //end if

	    } //end makeStamps()


	/**
	 * Create XML from props
	 *
	 * @return void
	 */

	public function createXML()
	    {
		$slice      = array_slice($this->links, 0, 1);
		$this->id   = sha1($slice[0]["link"] . $slice[0]["datetime"]->format("d.m.Y H:i:s"));
		$this->_xml = new XMLGenerator("estate");

		$this->_xml->newElement("id", $this->id);

		$a = array_slice($this->mainprops, 0, 10);
		foreach ($a as $prop)
		    {
			if (isset($this->$prop) === true)
			    {
				$this->_xml->newElement($prop, $this->$prop);
			    } //end if

		    } //end foreach

		$this->_addXMLData("description", "descriptions");

		$b = array_slice($this->mainprops, 10, 14);
		foreach ($b as $prop)
		    {
			if (isset($this->$prop) === true)
			    {
				$this->_xml->newElement($prop, $this->$prop);
			    } //end if

		    } //end foreach

		$c = [
		    "price"     => "prices",
		    "comission" => "comissions",
		];

		foreach ($c as $field => $from)
		    {
			$this->_addXMLData($field, $from);
		    } //end foreach

		foreach ($this->sellers as $seller)
		    {
			$this->_xml->newElement("seller", $seller["seller"], [
			    "person" => $seller["person"],
			    "phone"  => $seller["phone"],
			]);
		    } //end foreach

		$this->_addXMLData("link", "links");

		foreach ($this->photos as $photo)
		    {
			$this->_xml->newElement("photo", $photo);
		    } //end foreach

		$this->doc = $this->_xml->getDoc();
	    } //end createXML()


	/**
	 * Add new element to XML
	 *
	 * @param string $field Field name
	 * @param string $from  Array to extract data
	 *
	 * @return void
	 */

	private function _addXMLData(string $field, string $from)
	    {
		foreach ($this->$from as $data)
		    {
			$this->_xml->newElement($field, $data[$field], [
			    "datetime" => $data["datetime"]->format("d.m.Y H:i:s"),
			    "phone"    => $data["phone"],
			]);
		    } //end foreach

	    } //end _addXMLData()


	/**
	 * ToString method, get XML Estate
	 *
	 * @return string XML
	 */

	public function __toString():string
	    {
		if ($this->doc === null)
		    {
			$this->createXML();
		    } //end if

		return $this->doc->saveXML();
	    } //end __toString()


	/**
	 * Validate advert by XML schema
	 *
	 * @param string $path Path to XML Schema
	 *
	 * @return bool Result
	 */

	public function validate(string $path):bool
	    {
		if ($this->doc === null)
		    {
			$this->createXML();
		    } //end if

		return $this->doc->schemaValidate($path);
	    } //end validate()


	/**
	 * Have owner
	 *
	 * @return bool Status
	 */

	public function haveOwner()
	    {
		$have = false;
		foreach ($this->sellers as $seller)
		    {
			if ($seller["person"] === "owner")
			    {
				$have = true;
				break;
			    } //end if

		    } //end foreach

		return $have;
	    } //end haveOwner()


    } //end class

?>
