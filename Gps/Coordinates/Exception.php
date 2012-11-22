<?php
/**
 * @see Zend_Exception
 */
require_once 'Zend/Exception.php';

/**
 * Exception class for Smp_Gps_Coordinates
 *
 * @category  Smp
 * @package   Smp_Gps_Coordinates
 * @license   https://github.com/nexces/Smp/blob/master/LICENSE.txt    BSD License
 * @uses      Zend_Exception
 * @author    Adrian 'Nexces' Piotrowicz / adrianp
 */
class Smp_Gps_Coordinates_Exception extends Zend_Exception {
    const UNKNOWN_FORMAT = 1;
    const NOT_DMS = 2;
    const NOT_MINDEC = 3;

    const COORD_REGEXP_ERROR = 10;
    const DIRECTION_REGEXP_ERROR = 11;

}