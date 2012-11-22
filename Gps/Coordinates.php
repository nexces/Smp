<?php


/*
 * Type   Dir.   Sign    Test
 * Lat.   N      +       > 0
 * Lat.   S      -       < 0
 * Long.  E      +       > 0
 * Long.  W      -       < 0
 */
class Smp_Gps_Coordinates {

    /**
     * Decimal format of coordinate
     * @var int
     */
    const FORMAT_DEC = 10;

    /**
     * DMS (Degrees, Minutes, Seconds)
     * @var int
     */
    const FORMAT_DMS = 11;

    /**
     * MinDec(Degrees, Minutes, Decimal Minutes)
     * @var int
     */
    const FORMAT_MINDEC = 12;

    const MARK_DEG = '°';
    const MARK_MIN = '\'';
    const MARK_SEC = '"';

    private $latitude;
    private $longitude;

    public function getLatitude($format = self::FORMAT_DEC)
    {
        $direction = ($this->latitude > 0 ? 'N' : 'S');
        return $this->_getFormated($direction, $this->latitude, $format);
    }

    public function setLatitude($value, $format = self::FORMAT_DEC)
    {
        $this->latitude = $this->_getDecimal($value, $format);
        return $this;
    }

    public function getLongitude($format = self::FORMAT_DEC)
    {
        $direction = ($this->longitude > 0 ? 'E' : 'W');
        return $this->_getFormated($direction, $this->longitude, $format);
    }

    public function setLongitude($value, $format = self::FORMAT_DEC)
    {
        $this->longitude = $this->_getDecimal($value, $format);
        return $this;
    }

    private function _getDecimal($value, $format)
    {
        switch ($format) {
            case self::FORMAT_DMS:
                return $this->_fromDMS($value);
                break;

            case self::FORMAT_MINDEC:
                return $this->_fromMinDec($value);
                break;

            case self::FORMAT_DEC:
                return (float) $value;
                break;

            default:
                throw new Smp_Gps_Coordinates_Exception(
                    'Unknown format requested',
                    Smp_Gps_Coordinates_Exception::UNKNOWN_FORMAT);
        }
    }

    /**
     * Returns formated decimal coordinate to specified format
     * @param string $direction
     * @param int $value
     * @param int $format
     * @return string
     */
    private function _getFormated($direction, $value, $format)
    {
        switch ($format) {
            case self::FORMAT_DMS:
                return $this->_toDMS($value) . $direction;
                break;

            case self::FORMAT_MINDEC:
                return $this->_toMinDec($value) . $direction ;
                break;

            case self::FORMAT_DEC:
            default:
                return $value;
                break;
        }

        return $value;

    }

    /**
     * Converts coordinate from DMS format to Decimal format
     * @param string $dms
     * @return float
     */
    private function _fromDMS($dms)
    {
        $dms_parts = array();
        $dms_direction = array();

        $regexp_direction = '/[wens]/i';
        $regexp_coords = '/^(\d{1,2})(\D+([0-6]?\d)\D+([0-6]?\d(\.(\d+))?\D+)?)?$/';

        $dms_unidirectional = trim(str_ireplace(array('w', 'e', 'n', 's'), '', $dms));
        $matches_coords = preg_match($regexp_coords, $dms_unidirectional, $dms_parts);

        $matches_direction = preg_match($regexp_direction, $dms, $dms_direction);

        if ($matches_coords === FALSE) {
            throw new Smp_Gps_Coordinates_Exception(
                'Coordinates regexp error',
                Smp_Gps_Coordinates_Exception::COORD_REGEXP_ERROR);
        }
        if ($matches_direction === FALSE) {
            throw new Smp_Gps_Coordinates_Exception(
                'Direction regexp error',
                Smp_Gps_Coordinates_Exception::DIRECTION_REGEXP_ERROR);
        }

        if ($matches_coords == 0 || $matches_direction == 0) {
            throw new Smp_Gps_Coordinates_Exception(
                'Supplied coordinates are not in DMS format',
                Smp_Gps_Coordinates_Exception::NOT_DMS);
        }

        array_unshift($dms_parts, $dms_direction[0]);

        $decimal =
            ((strtolower($dms_parts[0]) == 's' || strtolower($dms_parts[0]) == 'w') ? '-' : '') .
            (
                (int) $dms_parts[2]
                +
                (isset($dms_parts[3])
                    ? (
                        ((int) $dms_parts[4] * 60)
                        +
                        (isset($dms_parts[5])
                            ? (float) $dms_parts[5]
                            : 0)
                    ) / 3600
                    : 0
                )
            )
        ;

        return (float) $decimal;
    }

    /**
     * Converts coordinate from MinDec format to Decimal format
     * @param string $mindec
     * @return float
     */
    private function _fromMinDec($mindec)
    {

        $mindec_parts = array();
        $mindec_direction = array();

        $regexp_direction = '/[wens]/i';
        $regexp_coords = '/^(\d{1,2})(\D+([0-6]?\d)(\.(\d+))?\D+)?$/i';

        $mindec_unidirectional = trim(str_ireplace(array('w', 'e', 'n', 's'), '', $mindec));
        $matches_coords = preg_match($regexp_coords, $mindec_unidirectional, $mindec_parts);

        $matches_direction = preg_match($regexp_direction, $mindec, $mindec_direction);

            if ($matches_coords === FALSE) {
            throw new Smp_Gps_Coordinates_Exception(
                'Coordinates regexp error',
                Smp_Gps_Coordinates_Exception::COORD_REGEXP_ERROR);
        }
        if ($matches_direction === FALSE) {
            throw new Smp_Gps_Coordinates_Exception(
                'Direction regexp error',
                Smp_Gps_Coordinates_Exception::DIRECTION_REGEXP_ERROR);
        }

        if ($matches_coords == 0 || $matches_direction == 0) {
            throw new Smp_Gps_Coordinates_Exception(
                'Supplied coordinates are not in MinDec format',
                Smp_Gps_Coordinates_Exception::NOT_MINDEC);
        }

        array_unshift($mindec_parts, $mindec_direction[0]);

        $decimal =
            ((strtolower($mindec_parts[0]) == 's' || strtolower($mindec_parts[0]) == 'w') ? '-' : '') .
            (
                (int) $mindec_parts[2]
                +
                (isset($mindec_parts[3])
                    ? (
                        (float) $mindec_parts[3]
                    ) / 60
                    : 0
                )
            )
        ;

        return (float) $decimal;

    }

    /**
     * Unidirectional conversion from Decimal format to DMS format
     * @param float $decimal
     * @return string
     */
    private function _toDMS($decimal)
    {
        $decimal = abs($decimal);

        $dms = (int) $decimal . self::MARK_DEG;

        if ((string) ((int) $decimal) == (string) $decimal) {
            return $dms;
        }

        $minutes = ($decimal - (int) $decimal) * 60;

        $dms .= '' . str_pad((int) ($minutes), 2, '0', STR_PAD_LEFT) . self::MARK_MIN;

        if ((string) ((int) $minutes) == (string) $minutes) {
            return $dms;
        }

        $seconds = ($minutes - (int) $minutes) * 60;

        $dms .= '' . number_format($seconds, 3)  . self::MARK_SEC;


        return (string) $dms;
    }

    /**
     * Unidirectional conversion from Decimal format to MinDec format
     * @param float $decimal
     * @return string
     */
    private function _toMinDec($decimal)
    {
        $decimal = abs($decimal);

        $dms = (int) $decimal . self::MARK_DEG;

        if ((string) ((int) $decimal) == (string) $decimal) {
            return $dms;
        }

        $minutes = ($decimal - (int) $decimal) * 60;

        $dms .= '' . str_pad(number_format($minutes, 6), 9, '0', STR_PAD_LEFT) . self::MARK_MIN;

        return (string) $dms;
    }
}