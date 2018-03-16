<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Classify;
use \AM\Container\Container;
use \Logics\Foundation\SQL\SQL;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

/**
 * Classify test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/ClassifyTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class ClassifyTest extends TestCase
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
		    CREATE TABLE IF NOT EXISTS `districts_locations` (
		      `id` int(10) UNSIGNED NOT NULL,
		      `city` char(40) COLLATE utf8mb4_unicode_ci NOT NULL,
		      `district` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
		      `lat` double NOT NULL,
		      `lang` double NOT NULL,
		      `sig` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL
		    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

		shell_exec("mysql -u " . DBUSER . " -p" . DBPASS . " -h " . DBHOST . " -D test < " . __DIR__ . "/datasets/sql/addresses.sql");
		shell_exec("mysql -u " . DBUSER . " -p" . DBPASS . " -h " . DBHOST . " -D test < " . __DIR__ . "/datasets/sql/names.sql");
		shell_exec("mysql -u " . DBUSER . " -p" . DBPASS . " -h " . DBHOST . " -D test < " . __DIR__ . "/datasets/sql/blacklist.sql");

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

		$db->exec("DROP TABLE `districts_locations`");
		$db->exec("DROP TABLE `names`");
		$db->exec("DROP TABLE `addresses`");
		$db->exec("DROP TABLE `blacklist");

		parent::tearDown();
	    } //end setUp()


	/**
	 * Should classify adverts
	 *
	 * @return void
	 */

	public function testShouldClassifyAdverts()
	    {
		$advert = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classify/1.xml"));

		$classify = new Classify($advert);
		$result   = $classify->classify();

		$this->assertEquals("agency", $result["person"]);
		$this->assertEquals(0, $result["total_score"]);
	    } //end testShouldClassifyAdverts()


	/**
	 * Should define district
	 *
	 * @return void
	 */

	public function testShouldDefineDistrict()
	    {
		$advert = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classify/1.xml"));

		$classify = new Classify($advert);
		$this->assertEquals("Солнечный", $classify->district);
	    } //end testShouldDefineDistrict()


	/**
	 * Should classify by objects on phone
	 *
	 * @return void
	 */

	public function testShouldClassifyByObjectsPhone()
	    {
		$expected = [
		    "1"  => "agency",
		    "2"  => "owner",
		    "3"  => "owner",
		    "4"  => "owner",
		    "5"  => "owner",
		    "6"  => "agency",
		    "7"  => "owner",
		    "8"  => "agency",
		    "9"  => "owner",
		    "10" => "owner",
		    "11" => "owner",
		    "12" => "owner",
		    "13" => "agency",
		    "14" => "agency",
		    "15" => "owner",
		    "16" => "agency",
		    "17" => "owner",
		];

		for ($i = 1; $i <= 17; $i++)
		    {
			$advert   = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/classify/" . $i . ".xml"));
			$classify = new Classify($advert);
			$result   = $classify->classify();
			$this->assertEquals($expected[$i], $result["person"]);
		    } //end for

	    } //end testShouldClassifyByObjectsPhone()


    } //end class

?>
