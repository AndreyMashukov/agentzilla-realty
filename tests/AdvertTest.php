<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \AM\Container\Container;
use \DOMDocument;
use \DOMXPath;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

/**
 * Advert class test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/AdvertTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class AdvertTest extends TestCase
    {

	/**
	 * Prepare data for testing
	 *
	 * @return void
	 */

	public function setUp()
	    {
		define("SERVICE_NAME", "advert");
		define("AGENTZILLA", "https://agentzilla.ru");

		parent::setUp();
	    } //end setUp()


	/**
	 * Destroy testing data
	 *
	 * @return void
	 */

	public function tearDown()
	    {
		parent::tearDown();
	    } //end setUp()


	/**
	 * Should generate advert object from page XML
	 *
	 * @return void
	 */

	public function testShouldGenerateAdvertObjectFromPageXml()
	    {
		$headers = array(
			    "Host"                     => "m.avito.ru",
			    "Accept"                   => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			    "Accept-Language"          => "ru,en-US;q=0.7,en;q=0.3",
			    "Accept-Encoding"          => "gzip, deflate, br",
			    "Connection"               => "keep-alive",
			    "Upgrade-Insecure-Request" => 1,
			    "Cache-Control"            => "max-age=0",
			   );

		$pagexml = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/pages/1.xml"));
		$advert  = new Advert();
		$advert->constructFromPage($pagexml);

		$this->assertEquals("https://m.avito.ru/moskva/kvartiry/1-k_kvartira_33_m_1012_et._822631677", $advert->link);

		$this->assertEquals($headers,             $advert->headers);
		$this->assertEquals("Россия",             $advert->country);
		$this->assertEquals("Московская область", $advert->region);
		$this->assertEquals("Москва",             $advert->city);
		$this->assertEquals("Avito",              $advert->site);
		$this->assertEquals("flat",         $advert->type);
		$this->assertEquals("rent",               $advert->operation);

		$this->assertEquals(base64_decode((string) $pagexml->html), $advert->html);

		$this->assertContains("<title>1-к квартира, 33 м², 10/12 эт. - купить, продать, сдать или снять в Москве на Avito       &#8212; Объявления на сайте Avito</title>", $advert->html);
		$this->assertTrue($advert->dom   instanceof DOMDocument);
		$this->assertTrue($advert->xpath instanceof DOMXPath);

		$advert->parseDOM();
		$this->assertEquals(33, $advert->area);
		$this->assertEquals(1, $advert->rooms);
		$this->assertEquals(10, $advert->floor);
		$this->assertEquals(12, $advert->floors);
		$this->assertEquals("Без комиссии и доплат. Светлая,уютная квартира с евроремонтом рядом с метро.Для комфортного проживания одного-двух гостей.Цена указана минимальная от 2000 р/сут. зависит от срока проживания.На одни сутки стоимость 3000р.ЗАСЕЛЕНИЕ ПО ПАСПОРТУ.ЕСТЬ ЗАЛОГ.НЕ СДАЕТСЯ ПОД РАЗВЛЕКАТЕЛЬНЫЕ МЕРОПРИЯТИЯ.", $advert->description);
		$this->assertEquals(2000, $advert->price);
		$this->assertEquals("собственник", $advert->name);
		$this->assertEquals("Агентство наAvito c мая 2014", $advert->person);
		$this->assertEquals(0, $advert->comission);
		$this->assertEquals("Каширское шоссе, 108к1", $advert->address);

		$advert->downloadPhotos();

		$advert->getPhone();
		$this->assertRegExp("/[78]{1}9[0-9]{9}/ui", $advert->phone);

		$advert->createXML();
		$this->assertTrue($advert->validate(__DIR__ . "/schemas/advert.xsd"));
	    } //end testShouldGenerateAdvertObjectFromPageXml()


	/**
	 * Should constructing from advert XML
	 *
	 * @return void
	 */

	public function testShouldConstructingFromAdvertXml()
	    {
		$xml    = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/a.xml"));
		$advert = new Advert($xml);

		$this->assertEquals("https://m.avito.ru/irkutsk/kvartiry/1-k_kvartira_37_m_35_et._1217928329", $advert->link);

		$this->assertEquals("Россия",             $advert->country);
		$this->assertEquals("Иркутская область",  $advert->region);
		$this->assertEquals("Иркутск",            $advert->city);
		$this->assertEquals("flat",               $advert->type);
		$this->assertEquals("rent",               $advert->operation);

		$this->assertEquals(37, $advert->area);
		$this->assertEquals(1, $advert->rooms);
		$this->assertEquals(3, $advert->floor);
		$this->assertEquals(5, $advert->floors);
		$this->assertEquals("Сдам 1комнатную квартиру в Солнечном. Полностью укомплектована мебелью. Оснащена бытовой техникой. На длительный срок.", $advert->description);
		$this->assertEquals(17000, $advert->price);
		$this->assertEquals(".", $advert->name);
		$this->assertEquals("Агентство наAvito c октября 2011", $advert->person);
		$this->assertEquals(8500, $advert->comission);
		$this->assertEquals("ул Ржанова, 25", $advert->address);

//		$photos = [
//		    base64_encode(file_get_contents("https://80.img.avito.st/640x480/3541829180.jpg")),
//		    base64_encode(file_get_contents("https://68.img.avito.st/640x480/3541829168.jpg")),
//		    base64_encode(file_get_contents("https://79.img.avito.st/640x480/3541829179.jpg")),
//		    base64_encode(file_get_contents("https://78.img.avito.st/640x480/3541829178.jpg")),
//		    base64_encode(file_get_contents("https://76.img.avito.st/640x480/3541829176.jpg")),
//		    base64_encode(file_get_contents("https://70.img.avito.st/640x480/3541829170.jpg")),
//		    base64_encode(file_get_contents("https://81.img.avito.st/640x480/3541829181.jpg")),
//		    base64_encode(file_get_contents("https://75.img.avito.st/640x480/3541829175.jpg")),
//		    base64_encode(file_get_contents("https://71.img.avito.st/640x480/3541829171.jpg")),
//		    base64_encode(file_get_contents("https://72.img.avito.st/640x480/3541829172.jpg")),
//		    base64_encode(file_get_contents("https://73.img.avito.st/640x480/3541829173.jpg")),
//		];
//		$this->assertEquals($photos, $advert->photos);

		$this->assertRegExp("/89[0-9]{9}/ui", $advert->phone);

		$this->assertTrue($advert->validate(__DIR__ . "/schemas/advert.xsd"));
	    } //end testShouldConstructingFromAdvertXml()


	/**
	 * Should get geo location of object address
	 *
	 * @return void
	 */

	public function testShouldGetGeoLocationOfObjectAddress()
	    {
		$xml    = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/a.xml"));
		$advert = new Advert($xml);

		$advert->getGeoLocation();
		$this->assertRegExp("/[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}/ui", $advert->date);
		$advert->createXML();
		$this->assertEquals(104.360772, $advert->lat);
		$this->assertEquals(52.250526,  $advert->lang);

		$expectedxml = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/b.xml"));
		$advertxml   = new SimpleXMLElement($advert->doc->saveXML());
		$props = ["country", "region", "city", "address", "lat", "lang", "description", "price", "phone", "link", "rooms", "area", "name", "comission", "floor", "floors"];
		foreach ($props as $prop)
		    {
			if (empty($advertxml->$prop) === false && empty($expectedxml) === false)
			    {
				$this->assertEquals($expectedxml->$prop, $advertxml->$prop);
			    } //end if

		    } //end foreach

	    } //end testShouldGetGeoLocationOfObjectAddress()


    } //end class

?>
