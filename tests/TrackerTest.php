<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Tests;

use \Agentzilla\Realty\Advert;
use \AdService\BigFile;
use \Agentzilla\Realty\Tracker;
use \PHPUnit\Framework\TestCase;
use \SimpleXMLElement;

/**
 * Tracker test
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/tests/TrackerTest.php $
 *
 * @runTestsInSeparateProcesses
 */

class TrackerTest extends TestCase
    {

	/**
	 * Prepare data for testing
	 *
	 * @return void
	 */

	public function setUp()
	    {
		define("BIG_FILE_DIR", __DIR__ . "/testdir");
		$this->_prepare();

		parent::setUp();
	    } //end setUp()


	/**
	 * Prepare to work
	 *
	 * @return void
	 */

	private function _prepare()
	    {
		$bigdir = BIG_FILE_DIR;
		for ($i = 1; $i <= 4; $i++)
		    {
			$xml     = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/tracker/" . $i . ".xml"));
			$bigfile = $bigdir . "/base-" . (string) $xml->phone;
			if (file_exists($bigfile) === true)
			    {
				unlink($bigfile);
			    } //end if

		    } //end for

		if (file_exists($bigdir) === true)
		    {
			rmdir($bigdir);
		    } //end if

		if (file_exists(__DIR__ . "/testdir") === true)
		    {
			rmdir(__DIR__ . "/testdir");
		    } //end if

	    } //end _prepare()


	/**
	 * Destroy testing data
	 *
	 * @return void
	 */

	public function tearDown()
	    {
		$this->_prepare();

		parent::tearDown();
	    } //end setUp()


	/**
	 * Should save ads history to bigfiles by tracker
	 *
	 * @return void
	 */

	public function testShouldSaveAdsHistoryToBigfilesByTracker()
	    {
		for ($i = 1; $i <= 4; $i++)
		    {
			$xml     = new SimpleXMLElement(file_get_contents(__DIR__ . "/datasets/adverts/tracker/" . $i . ".xml"));
			$advert  = new Advert($xml);
			$tracker = new Tracker($advert);
			$tracker->save();
		    } //end for

		$bigfile = new BigFile("base-89526191914", 10);
		$this->assertEquals("05.06.2017", $bigfile->getrecord(0));
		$this->assertEquals("06.06.2017", $bigfile->getrecord(1));

		$bigfile = new BigFile("base-89086623552", 10);
		$this->assertEquals("06.06.2017", $bigfile->getrecord(0));

		$bigfile = new BigFile("base-89627779797", 10);
		$this->assertEquals("05.06.2017", $bigfile->getrecord(0));
	    } //end testShouldSaveAdsHistoryToBigfilesByTracker()


    } //end class

?>
