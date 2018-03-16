<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \Agentzilla\Realty\DeduplicatorData;
use \Agentzilla\Realty\Nearly;
use \PHPUnit\Framework\TestCase;
use \Logics\Foundation\SQL\SQL;
use \SimpleXMLElement;

/**
 * Nearly test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/NearlyTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class NearlyTest extends TestCase
    {

	/**
	 * Prepare data for testing
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$db = SQL::get("MySQL");

		$db->exec("
		CREATE TABLE `adverts` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `lat` double NOT NULL,
		  `lang` double NOT NULL,
		  `advert` longblob NOT NULL,
		  `date` datetime NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

		parent::setUp();
	    } //end setUp()


	/**
	 * Destroy testing data
	 *
	 * @return void
	 */

	public function tearDown()
	    {
		$db = SQL::get("MySQL");

		$db->exec("DROP TABLE `adverts`");

		parent::tearDown();
	    } //end setUp()


	/**
	 * Should find ads near gived advert
	 *
	 * @return void
	 */

	public function testShouldFindAdsNearGivedAdvert()
	    {
		for ($i = 1; $i <= 17; $i++)
		    {
			$advert           = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/nearly/" . $i . ".xml")));
			$deduplicatordata = new DeduplicatorData($advert);
			$deduplicatordata->save();
		    } //end for

		$mainadvert = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/nearly/main.xml")));
		$nearly     = new Nearly($mainadvert);

		$lat1  = $mainadvert->lat - 0.002;
		$lat2  = $mainadvert->lat + 0.002;
		$lang1 = $mainadvert->lang - 0.002;
		$lang2 = $mainadvert->lang + 0.002;

		foreach ($nearly as $advert)
		    {
			$this->assertTrue($advert->lat >= $lat1);
			$this->assertTrue($advert->lat <= $lat2);
			$this->assertTrue($advert->lang >= $lang1);
			$this->assertTrue($advert->lang <= $lang2);
			$this->assertTrue($advert instanceof Advert);
		    } //end foreach

		$this->assertEquals(4, count($nearly));
	    } //end testShouldFindAdsNearGivedAdvert()


    } //end class

?>
