<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \Agentzilla\Realty\Comparing;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

/**
 * Comparing advert test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/ComparingTest.php $
 *
 * @runTestsInSeparateProcesse
 */

class ComparingTest extends TestCase
    {

	/**
	 * Prepare data for testing
	 *
	 * @return void
	 */

	public function setUp()
	    {
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
	 * Should compare adverts
	 *
	 * @return void
	 */

	public function testShouldCompareAdverts()
	    {
		$advert    = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/comparing/1.xml"));
		$comparing = new Comparing($advert);

		$testdata = [];

		$expected = [
		    "2" => true,
		    "3" => false,
		    "4" => true,
		    "5" => false,
		];

		for ($i = 2; $i <= 5; $i++)
		    {
			$testdata[] = array(
				       "advert"   => new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/comparing/" . $i . ".xml"))),
				       "expected" => $expected[$i],
				      );
		    } //end for

		foreach ($testdata as $data)
		    {
			$this->assertEquals($data["expected"], $comparing->compare($data["advert"]));
		    } //end foreach

	    } //end testShouldCompareAdverts()


	/**
	 * Should compare adverts with bad addresses
	 *
	 * @return void
	 */

	public function testShouldCompareAdvertsWithBadAddresses()
	    {
		$advert    = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/comparing/bad/1.xml"));
		$comparing = new Comparing($advert);

		$testdata = [];

		$expected = [
		    "2" => true,
		    "3" => false,
		    "4" => true,
		    "5" => false,
		];

		for ($i = 2; $i <= 5; $i++)
		    {
			$testdata[] = array(
				       "advert"   => new Advert(new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/comparing/bad/" . $i . ".xml"))),
				       "expected" => $expected[$i],
				      );
		    } //end for

		foreach ($testdata as $data)
		    {
			$this->assertEquals($data["expected"], $comparing->compare($data["advert"]));
		    } //end foreach

	    } //end testShouldCompareAdvertsWithBadAddresses()


    } //end class

?>
