<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \Agentzilla\Realty\Estate;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;
use \DateTime;
use \DateTimezone;

/**
 * Estate class test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/EstateTest.php $
 */

class EstateTest extends TestCase
    {

	/**
	 * Should construct estate from adverts
	 *
	 * @return void
	 */

	public function testShouldConstructEstateFromAdverts()
	    {
		$expected     = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/main.xml"));
		$descriptions = [];

		foreach($expected->description as $description)
		    {
			$datetime = new DateTime((string) $description["datetime"], new DateTimezone("UTC"));

			$descriptions[$datetime->getTimestamp()] = array(
								    "datetime"    => $datetime,
								    "phone"       => (string) $description["phone"],
								    "description" => (string) $description,
								   );
		    } //end foreach
		ksort($descriptions);

		$prices = [];

		foreach($expected->price as $price)
		    {
			$datetime = new DateTime((string) $price["datetime"], new DateTimezone("UTC"));

			$prices[$datetime->getTimestamp()] = array(
								    "datetime" => $datetime,
								    "phone"    => (string) $price["phone"],
								    "price"    => (string) $price,
								   );
		    } //end foreach
		ksort($prices);

		$comissions = [];

		foreach($expected->comission as $comission)
		    {
			$datetime = new DateTime((string) $comission["datetime"], new DateTimezone("UTC"));

			$comissions[$datetime->getTimestamp()] = array(
								  "datetime"  => $datetime,
								  "phone"     => (string) $comission["phone"],
								  "comission" => (string) $comission,
								 );
		    } //end foreach
		ksort($comissions);

		$sellers = [];

		foreach($expected->seller as $seller)
		    {
			$hash = sha1(mb_strtoupper((string) $seller) . (string) $seller["phone"]);

			$sellers[$hash] = array(
					   "person" => (string) $seller["person"],
					   "phone"  => (string) $seller["phone"],
					   "seller" => (string) $seller,
					  );
		    } //end foreach

		$links = [];

		foreach($expected->link as $link)
		    {
			$datetime = new DateTime((string) $link["datetime"], new DateTimezone("UTC"));

			$links[$datetime->getTimestamp()] = array(
								  "datetime" => $datetime,
								  "phone"    => (string) $link["phone"],
								  "link"     => (string) $link,
								 );
		    } //end foreach
		ksort($links);

		$newadvert = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/adverts/new.xml")));
		$estate    = new Estate();
		$estate->addAdvert($newadvert);
		for ($i = 1; $i <= 3; $i++)
		    {
			$advert = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/adverts/" . $i . ".xml")));
			$estate->addAdvert($advert);
		    } //end for

		$this->assertEquals($descriptions, $estate->descriptions);
		$this->assertEquals($prices, $estate->prices);
		$this->assertEquals($comissions, $estate->comissions);
		$this->assertEquals($sellers, $estate->sellers);
		$this->assertEquals($links, $estate->links);

		$this->assertEquals("Россия",             $estate->country);
		$this->assertEquals("Иркутская область",  $estate->region);
		$this->assertEquals("Иркутск",            $estate->city);
		$this->assertEquals("flat",               $estate->type);
		$this->assertEquals("rent",               $estate->operation);

		$this->assertEquals(37, $estate->area);
		$this->assertEquals(1,  $estate->rooms);
		$this->assertEquals(3,  $estate->floor);
		$this->assertEquals(5,  $estate->floors);

		$this->assertEquals("Ржанова, 25", $estate->address);
		$this->assertEquals("Солнечный",      $estate->district);
		$this->assertEquals(104.360772,       $estate->lat);
		$this->assertEquals(52.250526,        $estate->lang);

		$estate->createXML();
		$this->assertTrue($estate->validate(__DIR__ . "/schemas/estate.xsd"));
		$main = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/main.xml"));
		$this->assertEquals($main, new SimpleXMLElement($estate->doc->saveXML()));
	    } //end testShouldConstructEstateFromAdverts()


	/**
	 * Should construct estate from XML estate
	 *
	 * @return void
	 */

	public function testShouldConstructEstateFromXmlEstate()
	    {
		$main   = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/main.xml"));
		$estate = new Estate($main);

		$this->assertEquals($main, new SimpleXMLElement($estate->doc->saveXML()));
	    } //end testShouldConstructEstateFromXmlEstate()


	/**
	 * Should make stamps to photo
	 *
	 * @return void
	 */

	public function testShouldMakeStampsToPhoto()
	    {
		$main   = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/main.xml"));
		$estate = new Estate($main);
		$estate->makeStamps();
		$estate->createXML();
		$expected = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/stamped.xml"));

//		$this->assertEquals($expected, new SimpleXMLElement($estate->doc->saveXML()));
	    } //end testShouldMakeStampsToPhoto()


	/**
	 * Should have method for checking owner in sellers
	 *
	 * @return void
	 */

	public function testShouldHaveMethodForCheckingOwnerInSellers()
	    {
		$withowner       = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/withowner.xml"));
		$estatewithowner = new Estate($withowner);
		$this->assertTrue($estatewithowner->haveOwner());

		$withoutowner       = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/estates/withoutowner.xml"));
		$estatewithoutowner = new Estate($withoutowner);
		$this->assertFalse($estatewithoutowner->haveOwner());
	    } //end testShouldHaveMethodForCheckingOwnerInSellers()


    } //end class

?>
