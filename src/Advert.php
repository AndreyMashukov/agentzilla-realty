<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Phones\AvitoPhone;
use \Agentzilla\HTTP\HTTPclient;
use \AdService\XMLGenerator;
use \DOMDocument;
use \DOMXPath;
use \SimpleXMLElement;

/**
 * Advertise class
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-25 21:40:22 +0000 (Sun, 25 Feb 2018) $ $Revision: 7 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Advert.php $
 */

class Advert
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
	 * Site
	 *
	 * @var string
	 */
	public $site = null;

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
	 * Headers
	 *
	 * @var array
	 */
	public $headers = [];

	/**
	 * Operation
	 *
	 * @var string
	 */
	public $link = null;

	/**
	 * Operation
	 *
	 * @var string
	 */
	public $html = null;

	/**
	 * DOM
	 *
	 * @var DOMDocument
	 */
	public $dom = null;

	/**
	 * DOMXPath
	 *
	 * @var DOMXPath
	 */
	public $xpath = null;

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
	 * Parser configuration
	 *
	 * @var SimpleXMLElement
	 */
	private $_config;

	/**
	 * Valid type
	 *
	 * @var bool Valid type status
	 */
	public $validtype = true;

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
		"residentialcomplex",
		"lat",
		"lang",
		"type",
		"operation",
		"rooms",
		"description",
		"area",
		"floor",
		"floors",
		"material",
		"distance",
		"appointment",
		"land",
		"price",
		"comission",
		"name",
		"person",
		"phone",
		"date",
		"link",
	    ];

	/**
	 * Prepare
	 *
	 * @param SimpleXMLElement $advert XML advert
	 *
	 * @return void
	 */

	public function __construct(SimpleXMLElement $advert = null)
	    {
		if ($advert !== null)
		    {
			$this->_loadStandartProperties($advert);

			if (empty($advert->photo) === false)
			    {
				$this->photos = [];
				foreach ($advert->photo as $photo)
				    {
					$this->_xml->newElement("photo", (string) $photo);
					$this->photos[] = $photo;
				    }
			    } //end if

			$this->doc = $this->_xml->getDoc();
		    } //end if

	    } //end __construct()


	/**
	 * Get geo location by object address
	 *
	 * @return void
	 */

	public function getGeoLocation()
	    {
		if (isset($this->lat) === false && isset($this->lang) === false)
		    {
			$address = $this->city . ", " . $this->address;
			$url     = "https://geocode-maps.yandex.ru/1.x/?geocode=" . urlencode(mb_ereg_replace(" ", "+", $address));

			do
			    {
				$http   = new HTTPclient($url);
				$result = $http->get();
				if ($http->lastcode() !== 200)
				    {
					$result = $http->getWithProxy();
				    } //end if

			    } while ($http->lastcode() !== 200);

			$xml = new SimpleXMLElement($result);

			if (empty($xml->{"GeoObjectCollection"}->{"featureMember"}->{"GeoObject"}->{"Point"}->{"pos"}) === false)
			    {
				$position   = explode(" ", (string) $xml->{"GeoObjectCollection"}->{"featureMember"}->{"GeoObject"}->{"Point"}->{"pos"});
				$this->lat  = $position[0];
				$this->lang = $position[1];
			    } //end if

		    } //end if

		$this->createXML();
	    } //end getGeoLocation()


	/**
	 * Construct advert from page XML
	 *
	 * @return void
	 */

	public function constructFromPage(SimpleXMLElement $page)
	    {
		$this->_loadStandartProperties($page, true);

		$this->doc = $this->_xml->getDoc();
	    } //end loadHtml()


	/**
	 * Load standart properties
	 *
	 * @param SimpleXMLElement $xml      Source of data
	 * @param bool             $frompage Loading from page xml
	 *
	 * @return void
	 */

	private function _loadStandartProperties(SimpleXMLElement $xml, bool $frompage = false)
	    {
		$this->_xml = new XMLGenerator("advert");

		if ($frompage === true)
		    {
			$props = array_merge($this->mainprops, ["site", "html"]);
		    }
		else
		    {
			$props = $this->mainprops;
		    } //end if

		foreach ($props as $prop)
		    {
			if (empty($xml->$prop) === false)
			    {
				$this->_xml->newElement($prop, (string) $xml->$prop);
				$value = (string) $xml->$prop;
				if ($prop === "html")
				    {
					$value = base64_decode($value);
				    } //end if

				$this->$prop = $value;
			    } //end if

		    } //end foreach

		if ($frompage === true)
		    {
			if (empty($xml->{"headers"}) === false)
			    {
				$this->headers = unserialize(base64_decode((string) $xml->headers));
			    } //end if

			$this->_loadDOM();
		    } //end if

	    } //end _loadStandartProperties()


	/**
	 * Create XML from parsed html and props
	 *
	 * @return void
	 */

	public function createXML()
	    {
		$this->_xml = new XMLGenerator("advert");

		foreach ($this->mainprops as $prop)
		    {
			if (isset($this->$prop) === true)
			    {
				$this->_xml->newElement($prop, (string) $this->$prop);
			    } //end if

		    } //end foreach

		if (isset($this->photos) === true)
		    {
			if (count($this->photos) > 0)
			    {
				foreach ($this->photos as $photo)
				    {
					$this->_xml->newElement("photo", $photo);
				    } //end foreach

			    } //end if

		    } //end if

		$this->doc = $this->_xml->getDoc();
	    } //end createXML()


	/**
	 * Load DOM from html
	 *
	 * @return void
	 */

	private function _loadDOM()
	    {
		$this->dom = new DOMDocument();
		@$this->dom->loadHTML($this->html);
		$this->xpath = new DOMXPath($this->dom);
	    } //end _loadDOM()


	/**
	 * Parse DOMDocument to advert parameters
	 *
	 * @return void
	 */

	public function parseDOM()
	    {
		$this->_config = new SimpleXMLElement(file_get_contents(__DIR__ . "/config/parser/" . mb_strtolower($this->site) . ".xml"));
		$params = ["info", "title"];
		$params = array_merge($params, $this->mainprops);

		foreach($params as $name)
		    {
			$result = $this->_getValue($name);
			if ($result !== false)
			    {
				$this->$name = $result;
			    } //end if

		    } //end foreach

		switch (mb_strtolower($this->site))
		    {
			case "avito":
			    $this->_parser($params);
			    $this->_filter($params);
			    $this->_getDate();
			break;
			case "cian":
			    $this->parseCIAN();
			    $this->_filter($params);
			    $this->_getDate();
			break;
		    }

		if (isset($this->title) === true)
		    {
			if (preg_match("/Продам/ui", $this->title) > 0 && $this->operation === "rent")
			    {
				$this->validtype = false;
			    }

		    } //end if

		unset($this->info);
		unset($this->title);
	    } //end parseDOM()


	/**
	 * Parse cian html - json
	 *
	 * @return void
	 */

	protected function parseCIAN()
	    {
		if (preg_match("/.*window\._cianConfig\['offer-card']\ = (?P<offer>.*);\n./ui", $this->html, $result) > 0)
		    {
			$address = [];

			$offer = json_decode($result["offer"], true);
			$addr  = $offer["defaultState"]["offerData"]["offer"]["geo"]["address"];
			$loc   = $offer["defaultState"]["offerData"]["offer"]["geo"]["coordinates"];

			$this->lang        = $loc["lng"];
			$this->lat         = $loc["lat"];
			$this->description = $offer["defaultState"]["offerData"]["offer"]["description"];
			$this->area        = ceil($offer["defaultState"]["offerData"]["offer"]["totalArea"]);
			$this->rooms       = $offer["defaultState"]["offerData"]["offer"]["roomsCount"];
			$this->floors      = $offer["defaultState"]["offerData"]["offer"]["building"]["floorsCount"];
			$this->floor       = $offer["defaultState"]["offerData"]["offer"]["floorNumber"];
			$this->photos      = $offer["defaultState"]["offerData"]["offer"]["photos"];
			if (strlen($offer["defaultState"]["offerData"]["user"]["firstName"]) > 1)
			    {
				$this->name = $offer["defaultState"]["offerData"]["user"]["firstName"];
			    }
			else if (strlen($offer["defaultState"]["offerData"]["agent"]["name"]) > 1)
			    {
				$this->person = "Агентство";
				$this->name   = $offer["defaultState"]["offerData"]["agent"]["name"];
			    } // end if

			foreach ($addr as $data)
			    {
				if ($data["type"] === "street" || $data["type"] === "house")
				    {
					$address[] = $data["shortName"];
				    }
			    }

			$this->address = implode(", ", $address);
		    }

		if (preg_match("/.*\(\"pageview\",(?P<pageview>.*)\)<\/script>.*/ui", $this->html, $result) > 0)
		    {
			$pageview    = json_decode($result["pageview"], true);
			$this->price = $pageview["page"]["offerPrice"];
			$this->phone = preg_replace("/\+7/ui", "8", $pageview["page"]["offerPhone"]);
		    }

	    } //end parseCIAN()


	/**
	 * Download cian photos
	 *
	 * @return array photos
	 */

	private function _cianPhotos():array
	    {
		$photos = [];
		foreach($this->photos as $photo)
		    {
			$photos[] = base64_encode(file_get_contents(trim($photo["fullUrl"])));
		    } //end foreach

		return $photos;
	    } //end _cianPhotos()


	/**
	 * Download photos
	 *
	 * @return void
	 */

	public function downloadPhotos()
	    {
		$photos = [];

		if ((string) $this->_config->{"Photos"}["type"] === "xpath")
		    {
			$pattern = (string) $this->_config->{"Photos"};
			$list   = $this->xpath->query($pattern);
			foreach($list as $photo)
			    {
				$photos[] = base64_encode(file_get_contents(trim($photo->textContent)));
			    } //end foreach

		    }
		else if ((string) $this->_config->{"Photos"}["type"] === "method")
		    {
			$photosmethod = (string) $this->_config->{"Photos"};
			$photos = $this->$photosmethod();
		    } //end if

		$this->photos = $photos;
	    } //end downloadPhotos()


	/**
	 * Get phone
	 *
	 * @return void
	 */

	public function getPhone()
	    {
		if ((string) $this->_config->{"Phone"}["type"] !== "none")
		    {
			$phonemethod = (string) $this->_config->{"Phone"};
			$this->$phonemethod();
		    }

	    } //end getPhone()


	/**
	 * Get phone from avito site
	 *
	 * @return void
	 */

	private function _getPhoneAvito()
	    {
		$avitophone  = new AvitoPhone($this->link, $this->headers);
		$this->phone = $avitophone->get();
	    } //end _getPhoneAvito()


	/**
	 * Get property value
	 *
	 * @param string $name Property name
	 *
	 * @return mixed False or property value
	 */

	private function _getValue($name)
	    {
		$patterns = [];
		foreach ($this->_config->{"Fields"}->{"XPath"} as $data)
		    {
			$patterns[(string) $data["name"]] = $data;
		    } //end foreach

		if(isset($patterns[$name]) === true)
		    {
			$list = $this->xpath->query((string) $patterns[$name]);
			if($list->{"length"} !== 0)
			    {
				$result = trim(preg_replace("/(\b|\t+)/ui", "", preg_replace("/(\n|\s{2})/ui", "", $list[0]->nodeValue)));
				if (isset($patterns[$name]["explode"]) === true && isset($patterns[$name]["index"]) === true)
				    {
					$exploded = explode((string) $patterns[$name]["explode"], $result);
					$result   = $exploded[(int) $patterns[$name]["index"]];
				    } //end if

				return $result;
			    }
			else
			    {
				return false;
			    } //end if

		    }
		else
		    {
			return false;
		    } //end if

	    } //end _getValue()


	/**
	 * Get advert date
	 *
	 * @param string $format Date format
	 *
	 * @return void
	 */

	private function _getDate($format = "d.m.Y")
	    {
		if (isset($this->date) === true)
		    {
			$this->date = trim(preg_replace("/(Размещено)/ui", "", $this->date));

			$months = array(
					"января"   => 1,
					"февраля"  => 2,
					"марта"    => 3,
					"апреля"   => 4,
					"мая"      => 5,
					"июня"     => 6,
					"июля"     => 7,
					"августа"  => 8,
					"сентября" => 9,
					"октября"  => 10,
					"ноября"   => 11,
					"декабря"  => 12,
				       );

			if (preg_match("/сегодня/iu", $this->date) > 0)
			    {
				$this->date = date($format);
			    }
			else if (preg_match("/вчера/iu", $this->date) > 0)
			    {
				$this->date = date($format, strtotime(date("d.m.Y") . " -1 day"));
			    }
			else if (preg_match("/(?P<day>\d{1,2})\s+(?P<month>" . implode("|", array_keys($months)) . ")/iu", $this->date, $m) > 0)
			    {
				$this->date = date($format, strtotime($m["day"] . "." . $months[$m["month"]] . "." . date("Y")));
			    }
			else if (preg_match("/(?P<day>\d{1,2})\.(?P<month>\d{2})\.(?P<year>\d{4})/iu", $this->date, $m) > 0)
			    {
				$this->date = date($format, strtotime($m["day"] . "." . $m["month"] . "." . $m["year"]));
			    }
			else
			    {
				unset($this->date);
			    } //end if

		    }
		else
		    {
			$this->date = date($format);
		    } //end if

	    } //end _getDate()


	/**
	 * Parse dom and get properties
	 *
	 * @param array $params Parameters for parsing
	 *
	 * @return void
	 */

	private function _parser($params)
	    {
		$patterns = array();

		foreach ($this->_config->{"Parser"}->{"Pattern"} as $pattern)
		    {
			$patterns[] = (string) $pattern;
		    } //end if


		foreach($patterns as $pattern)
		    {
			$properties = ["title", "info"];

			foreach ($properties as $in)
			    {
				if (isset($this->$in) === true)
				    {
					if (preg_match($pattern, $this->$in, $props) > 0)
					    {
						foreach($params as $name)
						    {
							if (isset($props[$name]) === true)
							    {
								$this->$name = $props[$name];
							    } //end if

						    } //end foreach

					    } //end if

				    } //end if

			    } //end foreach

		    } //end foreach

		if (isset($this->description) === true)
		    {
			if(preg_match("/cтуди[яю]/ui", $this->description) > 0 && $this->rooms < 1)
			    {
				$this->rooms = 1;
			    } //end if

		    } //end if

		if (isset($this->title) === true)
		    {
			if(preg_match("/cтуди[яю]/ui", $this->title) > 0 && $this->rooms < 1)
			    {
				$this->rooms = 1;
			    } //end if

		    } //end if

	    } //end _parser()


	/**
	 * Modify advert properties to right format
	 *
	 * @param array $params Properties names array
	 *
	 * @return void
	 */

	private function _filter($params)
	    {
		$values = array(
			   "address" => array(
				       "value"  => false,
				       "action" => ((isset($this->address) === true) ? $this->address : ""),
				       "else"   => "Адрес не указан",
				      ),
			   "price" => array(
				       "value"  => false,
				       "action" => preg_replace("/\D/ui", "", ((isset($this->price) === true) ? $this->price : "")),
				       "else"   => "не указана",
				      ),
			   "comission" => array(
				       "value"  => false,
				       "action" => preg_replace("/\D/ui", "", ((isset($this->comission) === true) ? $this->comission : "")),
				       "else"   => 0,
				      ),
			   "name"  => array(
				       "value"  => false,
				       "action" => ((isset($this->name) === true) ? $this->name : ""),
				       "else"   => "не указано",
				      ),
			  );

		foreach($params as $name)
		    {
			if(isset($values[$name]))
			    {
				if(isset($this->$name) !== $values[$name]["value"])
				    {
					$this->$name = $values[$name]["action"];
				    }
				else
				    {
					$this->$name = $values[$name]["else"];
				    } //end if

			    } //end if

		    } //end foreach

		if (isset($this->city) === true && isset($this->address) === true)
		    {
			if (preg_match("/" . $this->city . "(,\s?)?/ui", $this->address) > 0)
			    {
				$this->address = preg_replace("/" . $this->city . "(,\s?)?/ui", "", $this->address);
			    } //end if

		    } //end if

	    } //end _filter()


	/**
	 * ToString method, get XML Advert
	 *
	 * @return string XML
	 */

	public function __toString():string
	    {
		$this->createXML();
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
		$this->createXML();
		return ($this->doc->schemaValidate($path) && $this->validtype === true);
	    } //end validate()


    } //end class

?>
