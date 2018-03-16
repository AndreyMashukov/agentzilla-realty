<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \Agentzilla\Realty\DeduplicatorData;
use \Logics\Foundation\SQL\SQL;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

/**
 * Deduplicator data class
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/DeduplicatorDataTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class DeduplicatorDataTest extends TestCase
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
	 * Should save deduplicator data
	 *
	 * @return void
	 */

	public function testShouldSaveDeduplicatorData()
	    {
		for ($i = 1; $i <= 4; $i++)
		    {
			$advert           = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/deduplicatordata/" . $i . ".xml")));
			$deduplicatordata = new DeduplicatorData($advert);
			$deduplicatordata->save();
		    } //end for

		$db     = SQL::get("MySQL");
		$result = $db->exec("SELECT * FROM `adverts`");
		$i      = 1;

		while ($row = $result->getRow())
		    {
			$expected = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/deduplicatordata/" . $i . ".xml")));
			$advert   = new Advert(new SimpleXMLElement($row["advert"]));
			$this->assertEquals($expected, $advert);
			$this->assertRegExp("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}/ui", $row["date"]);
			$this->assertRegExp("/[0-9]+\.[0-9]+/ui", $row["lat"]);
			$this->assertRegExp("/[0-9]+\.[0-9]+/ui", $row["lang"]);

			$i++;
		    } //end while

	    } //end testShouldSaveDeduplicatorData()


	/**
	 * Should save deduplicator data without photo
	 *
	 * @return void
	 */

	public function testShouldSaveDeduplicatorDataWithoutPhoto()
	    {
		for ($i = 1; $i <= 4; $i++)
		    {
			$advert           = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/deduplicatordata/" . $i . ".xml")));
			$deduplicatordata = new DeduplicatorData($advert);
			$deduplicatordata->save(false);
		    } //end for

		$db     = SQL::get("MySQL");
		$result = $db->exec("SELECT * FROM `adverts`");
		$i      = 1;

		while ($row = $result->getRow())
		    {
			$advert = new Advert(new SimpleXMLElement($row["advert"]));
			$this->assertTrue(isset($advert->photos) === false);
			$this->assertRegExp("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}/ui", $row["date"]);
			$this->assertRegExp("/[0-9]+\.[0-9]+/ui", $row["lat"]);
			$this->assertRegExp("/[0-9]+\.[0-9]+/ui", $row["lang"]);

			$i++;
		    } //end while

	    } //end testShouldSaveDeduplicatorDataWithoutPhoto()


    } //end class

?>
