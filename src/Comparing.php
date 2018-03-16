<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Agentzilla\Gis\AddressSlicer;
use \Agentzilla\Realty\Advert;
use \AdService\Comparison;
use \SimpleXMLElement;

/**
 * Comparing advert class
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Comparing.php $
 */

class Comparing extends Advert
    {

	/**
	 * Address slicer
	 *
	 * @var AddressSlicer
	 */
	private $_slicer;

	/**
	 * Construct class to work
	 *
	 * @param SimpleXMLElement $xml Advert XML
	 *
	 * @return void
	 */

	public function __construct(SimpleXMLElement $xml)
	    {
		parent::__construct($xml);

		$this->_slicer = new AddressSlicer($this->city . ", " . $this->address);

		$this->createXML();
	    } //end __construct()


	/**
	 * Compare adverts
	 *
	 * @param Advert $advert Advert to compare
	 *
	 * @return bool Result of compare
	 */

	public function compare(Advert $advert):bool
	    {
		$result = false;

		if ($advert->link === $this->link)
		    {
			$result = true;
		    }
		else
		    {
			$slicer = new AddressSlicer($advert->city . ", " . $advert->address);

			$address1 = $this->address;
			$address2 = $advert->address;

			if ($this->_slicer->valid() === true)
			    {
				$address1 = $this->_slicer->address;
			    } //end if

			if ($slicer->valid() === true)
			    {
				$address2 = $slicer->address;
			    } //end if

			$checks = [
			    "address"     => array(
					      "results" => [35, 90],
					      "1"       => $address1,
					      "2"       => $address2,
					      "slicers" => [$slicer, $this->_slicer],
					     ),
			    "description" => array(
					      "results" => [60, 85],
					      "1"       => $this->description,
					      "2"       => $advert->description,
					     ),
			];


			$results = [];
			foreach ($checks as $name => $check)
			    {
				$results[$name] = false;

				$comparison = new Comparison($check["1"], $check["2"]);
				if ($comparison->match === true)
				    {
					$results[$name] = $this->_compare($comparison, $check, $name, $check["results"][0]);
				    }
				else
				    {
					$results[$name] = $this->_compare($comparison, $check, $name, $check["results"][1]);
				    } //end if

			    } //end foreach

			if ($results["address"] === true && $results["description"] === true)
			    {
				if ($this->type === $advert->type && $this->operation === $advert->operation)
				    {
					$result = true;
				    } //end if

			    } //end if

		    } //end if

		return $result;
	    } //end compare()


	/**
	 * Compare data
	 *
	 * @param Comparison $comparison Data comparison
	 * @param array      $check      Checking data
	 * @param string     $name       Name of check
	 * @param int        $percent    Compare percent
	 *
	 * @return bool Result of compare
	 */

	private function _compare(Comparison $comparison, array $check, string $name, int $percent):bool
	    {
		$result = false;
		if ($comparison->percent >= $percent)
		    {
			if ($name === "address")
			    {
				$slicers = $check["slicers"];
				if ($slicers[0]->valid() === true && $slicers[1]->valid() === true)
				    {
					if (mb_strtoupper($slicers[0]->city) === mb_strtoupper($slicers[1]->city)
						&& mb_strtoupper($slicers[0]->street) === mb_strtoupper($slicers[1]->street)
						&& mb_strtoupper($slicers[0]->numberhouse) === mb_strtoupper($slicers[1]->numberhouse)
						&& mb_strtoupper($slicers[0]->building) === mb_strtoupper($slicers[1]->building))
					    {
						$result = true;
					    } //end if
				    }
				else
				    {
					if ($comparison->percent >= $percent)
					    {
						$result = true;
					    } //end if

				    } //end if

			    }
			else
			    {
				$result = true;
			    } //end if

		    } //end if

		return $result;
	    } //end _compare()


    } //end class

?>
