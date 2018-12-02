<?php
namespace Moudarir\Binga;


class Message {

    /**
     * List of all benchmark markers
     *
     * @var	array
     */
    public $marker = [];

    /**
     * Set a benchmark marker
     *
     * Multiple calls to this function can be made so that several
     * execution points can be timed.
     *
     * @param	string	$name	Marker name
     * @return	void
     */
    public function mark($name) {
        $this->marker[$name] = microtime(true);
    }

    /**
     * Elapsed time
     *
     * Calculates the time difference between two marked points.
     *
     * If the first parameter is empty this function instead returns the
     * {elapsed_time} pseudo-variable. This permits the full system
     * execution time to be shown in a template. The output class will
     * swap the real value for this variable.
     *
     * @param	string	$point1		A particular marked point
     * @param	string	$point2		A particular marked point
     * @param	int	$decimals	Number of decimal places
     *
     * @return	string	Calculated elapsed time on success,
     *			an '{elapsed_string}' if $point1 is empty
     *			or an empty string if $point1 is not found.
     */
    public function elapsedTime($point1 = '', $point2 = '', $decimals = 4) {

        if (!isset($this->marker[$point1])) {
            return '';
        }

        if (!isset($this->marker[$point2])) {
            $this->marker[$point2] = microtime(true);
        }

        return number_format($this->marker[$point2] - $this->marker[$point1], $decimals);
    }

    /**
     * dnd()
     *
     * @param mixed $data
     * @param boolean $continue
     * @return void
     */
    public static function dnd ($data, $continue = false) {
        $data = is_array($data) ? $data : [$data];
        if (!empty($data)) {
            foreach ($data as $key => $item) {
                var_dump($key, $item);
            }
        }

        if (!$continue) die();
    }

}