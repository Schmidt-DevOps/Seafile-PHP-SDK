<?php

namespace Seafile\Type;

use DateTime;
use stdClass;

class Library
{
    /**
     * @var string
     */
    public $permission = "";

    /**
     * @var string
     */
    public $encrypted = "";

    /**
     * @var string
     */
    public $mtimeRelative = "";

    /**
     * @var string
     */
    public $mtime = "";

    /**
     * @var string
     */
    public $owner = "";

    /**
     * @var string
     */
    public $root = "";

    /**
     * @var string
     */
    public $id = "";

    /**
     * @var string
     */
    public $size = "";

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var string
     */
    public $type = "";

    /**
     * @var string
     */
    public $virtual = "";

    /**
     * @var string
     */
    public $desc = "";

    /**
     * @var string
     */
    public $sizeFormatted = "";

    /**
     * Constructor
     * @param array $fromArray Create from array
     */
    public function __construct(array $fromArray = [])
    {
        if (is_array($fromArray) && !empty($fromArray)) {
            $this->fromArray($fromArray);
        }
    }

    /**
     * Populate from array
     * @param array $fromArray Create from array
     * @return void
     */
    public function fromArray(array $fromArray)
    {
        foreach ($fromArray as $key => $value) {
            $lowerCamelCaseKey = preg_replace('/_(.?)/e', "strtoupper('$1')", $key);
            if (property_exists($this, $lowerCamelCaseKey)) {
                switch ($key) {
                    case 'mtime':
                        $this->{$lowerCamelCaseKey} = DateTime::createFromFormat("U", $value);
                        break;
                    default:
                        $this->{$lowerCamelCaseKey} = $value;
                        break;
                }
            }
        }
    }

    /**
     * Create from jsonResponse
     * @param stdClass $jsonResponse
     * @return Library
     */
    public static function fromJsonResponse(stdClass $jsonResponse)
    {
        return new Library((array)$jsonResponse);
    }
}
