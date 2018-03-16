<?php

/**
 * PHP version 7.1
 *
 * @package Agentzilla\Realty
 */

namespace Agentzilla\Realty;

use \Countable;
use \Iterator;
use \Logics\Foundation\SQL\SQL;
use \SimpleXMLElement;

/**
 * Nearly - allow to get adverts near current advert location
 *
 * @author  Andrey Mashukov <a.mashukoff@gmail.com>
 * @version SVN: $Date: 2018-02-12 19:53:24 +0000 (Mon, 12 Feb 2018) $ $Revision: 2 $
 * @link    $HeadURL: https://svn.agentzilla.ru/realty/trunk/src/Nearly.php $
 */

class Nearly implements Iterator, Countable
    {

	/**
	 * Database connection
	 *
	 * @var MySQL
	 */
	private $_db = null;

	/**
	 * Advert to search
	 *
	 * @var Advert
	 */
	private $_advert = null;

	/**
	 * Order
	 *
	 * @var array Order
	 */
	private $_order = [];

	/**
	 * Position for iterator
	 *
	 * @var int Position
	 */
	private $_position;

	/**
	 * Prepare container to work
	 *
	 * @param Advert $advert Advert to find objects near it
	 *
	 * @return void
	 */

	public function __construct(Advert $advert)
	    {
		$this->_db     = SQL::get("MySQL");
		$this->_advert = $advert;
		$this->_constructOrder();
	    } //end __construct()


	/**
	 * Construct order
	 *
	 * @return void
	 */

	private function _constructOrder()
	    {
		$lat1  = $this->_advert->lat - 0.002;
		$lat2  = $this->_advert->lat + 0.002;
		$lang1 = $this->_advert->lang - 0.002;
		$lang2 = $this->_advert->lang + 0.002;

		$result = $this->_db->exec("SELECT `id` FROM `adverts` WHERE " .
		    "`lat` >= " . $this->_db->sqlText($lat1) . " AND " .
		    "`lat` <= " . $this->_db->sqlText($lat2) . " AND " .
		    "`lang` >= " . $this->_db->sqlText($lang1) . " AND " .
		    "`lang` <= " . $this->_db->sqlText($lang2)
		);

		if ($result->getNumRows() > 0)
		    {
			while ($row = $result->getRow())
			    {
				$this->_order[] = $row["id"];
			    } //end while

		    } //end if

	    } //end _constructOrder()


	/**
	 * Get advert by ID
	 *
	 * @param int $id Identifier in database
	 *
	 * @return mixed Current advert or null
	 */

	private function _getAdvert(int $id)
	    {
		$advert = null;
		$result = $this->_db->exec("SELECT `advert` FROM `adverts` WHERE `id` = " . $this->_db->sqlText($id));

		if ($result->getNumRows() > 0)
		    {
			while ($row = $result->getRow())
			    {
				$advert = new Advert(new SimpleXMLElement($row["advert"]));
			    } //end while

		    } //end if

		return $advert;
	    } //end _getAdvert()

	/**
	 * Rewind
	 *
	 * @return void
	 */

	public function rewind()
	    {
		$this->_position = 0;
	    } //end rewind()


	/**
	 * Current element
	 *
	 * @return array Element
	 */

	public function current():Advert
	    {
		$id = $this->_order[$this->_position];
		return $this->_getAdvert($id);
	    } //end current()


	/**
	 * Key
	 *
	 * @return int Position index
	 */

	public function key():int
	    {
		return $this->_position;
	    } //end key()


	/**
	 * Next iteration
	 *
	 * @return void
	 */

	public function next()
	    {
		++$this->_position;
	    } //end next()


	/**
	 * Validate element
	 *
	 * @return bool Exist element
	 */

	public function valid():bool
	    {
		return isset($this->_order[$this->_position]);
	    } //end valid()


	/**
	 * Count
	 *
	 * @retutn int count
	 */

	public function count():int
	    {
		return count($this->_order);
	    } //end count()


    } //end class

?>