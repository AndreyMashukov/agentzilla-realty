<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \Agentzilla\Realty\ClassifierData;
use \Logics\Foundation\SQL\SQL;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;
use \AM\Container\Container;

/**
 * Classifier data test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/ClassifierDataTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class ClassifierDataTest extends TestCase
    {

	/**
	 * Prepare data for testing
	 *
	 * @return void
	 */

	public function setUp()
	    {
		$c = new Container("agency_send_sms");
		$c->clear();

		$db = SQL::get("MySQL");

		$db->exec("
		    CREATE TABLE `addresses` (
		      `id` int(11) NOT NULL AUTO_INCREMENT,
		      `city` char(100) NOT NULL,
		      `hash` char(40) NOT NULL,
		      `address` char(200) NOT NULL,
		      `phone` char(15) NOT NULL,
		      PRIMARY KEY (`id`),
		      KEY `search` (`city`),
		      KEY `contact` (`phone`)
		    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;");

		$db->exec("
		    CREATE TABLE `agencies` (
		      `id` int(11) NOT NULL AUTO_INCREMENT,
		      `city` char(100) NOT NULL,
		      `hash` char(40) NOT NULL,
		      `name` char(200) NOT NULL,
		      `phone` char(15) NOT NULL,
		      PRIMARY KEY (`id`),
		      KEY `search` (`city`),
		      KEY `contact` (`phone`)
		    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;");

		$db->exec("
		    CREATE TABLE `names` (
		      `id` int(11) NOT NULL AUTO_INCREMENT,
		      `hash` char(40) NOT NULL,
		      `name` text NOT NULL,
		      `phone` text NOT NULL,
		      PRIMARY KEY (`id`),
		      KEY `phone` (`phone`(15))
		    ) ENGINE=InnoDB AUTO_INCREMENT=202324 DEFAULT CHARSET=utf8mb4;");

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

		$db->exec("DROP TABLE `names`");
		$db->exec("DROP TABLE `addresses`");
		$db->exec("DROP TABLE `agencies`");
		$c = new Container("agency_send_sms");
		$c->clear();

		parent::tearDown();
	    } //end setUp()


	/**
	 * Should save classifier data
	 *
	 * @return void
	 */

	public function testShouldSaveClassifierData()
	    {
		for ($i = 1; $i <= 18; $i++)
		    {
			$advert         = new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classifierdata/" . $i . ".xml")));
			$classifierdata = new ClassifierData($advert);
			$classifierdata->save();
		    } //end for

		$expected = [];

		$xml = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classifierdata/agencies.xml"));
		foreach ($xml->agency as $agency)
		    {
			$expected[] = array(
				       "city"  => (string) $agency->city,
				       "hash"  => (string) $agency->hash,
				       "name"  => (string) $agency->name,
				       "phone" => (string) $agency->phone,
				      );
		    } //end foreach

		$db     = SQL::get("MySQL");
		$result = $db->exec("SELECT * FROM `agencies`");
		$i      = 0;

		while ($row = $result->getRow())
		    {
			$this->assertEquals($expected[$i]["city"], $row["city"]);
			$this->assertEquals($expected[$i]["hash"], $row["hash"]);
			$this->assertEquals($expected[$i]["name"], $row["name"]);
			$this->assertEquals($expected[$i]["phone"], $row["phone"]);
			$i++;
		    } //end while

		$c = new Container("agency_send_sms");
		$this->assertEquals(count($expected), count($c));

		$expected = [];

		$xml = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classifierdata/addresses.xml"));
		foreach ($xml->address as $address)
		    {
			$expected[] = array(
				       "city"    => (string) $address->city,
				       "hash"    => (string) $address->hash,
				       "address" => (string) $address->address,
				       "phone"   => (string) $address->phone,
				      );
		    } //end foreach


		$db     = SQL::get("MySQL");
		$result = $db->exec("SELECT * FROM `addresses`");
		$i      = 0;

		while ($row = $result->getRow())
		    {
			$this->assertEquals($expected[$i]["city"], $row["city"]);
			$this->assertEquals($expected[$i]["hash"], $row["hash"]);
			$this->assertEquals($expected[$i]["address"], $row["address"]);
			$this->assertEquals($expected[$i]["phone"], $row["phone"]);
			$i++;
		    } //end while

		$expected = [];

		$xml = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classifierdata/names.xml"));
		foreach ($xml->name as $name)
		    {
			$expected[] = array(
				       "hash"  => (string) $name->hash,
				       "name"  => (string) $name->name,
				       "phone" => (string) $name->phone,
				      );
		    } //end foreach

		$result = $db->exec("SELECT * FROM `names`");
		$m      = 0;

		while ($row = $result->getRow())
		    {
			$this->assertEquals($expected[$m]["hash"], $row["hash"]);
			$this->assertEquals($expected[$m]["name"], $row["name"]);
			$this->assertEquals($expected[$m]["phone"], $row["phone"]);
			$m++;
		    } //end while

	    } //end testShouldSaveClassifierData()


    } //end class

?>
