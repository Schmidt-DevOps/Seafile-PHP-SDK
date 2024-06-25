<?php

namespace Seafile\Client\Type;

use DateTime;
use CaseHelper\CaseHelperFactory;
use Exception;
use \Seafile\Client\Type\Account as AccountType;
use stdClass;

/**
 * Abstract type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
abstract class Type implements TypeInterface
{
    /**
     * Associative array mode
     */
    public const ARRAY_ASSOC = 1;

    /**
     * Multipart array mode
     */
    public const ARRAY_MULTI_PART = 2;

    /**
     * Constructor
     *
     * @param array $fromArray Create from array
     *
     * @throws Exception
     */
    public function __construct(array $fromArray = [])
    {
        if ($fromArray !== []) {
            $this->fromArray($fromArray);
        }
    }

    /**
     * Populate from array
     *
     * @param array $fromArray Create from array
     *
     * @return self
     * @throws Exception
     */
    public function fromArray(array $fromArray) // type is given in derived class
    {
        foreach ($fromArray as $key => $value) {
            $camelCaseKey = CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_SNAKE_CASE)->toCamelCase($key);

            if (!property_exists($this, $camelCaseKey)) {
                continue;
            }

            switch ($key) {
                case 'creator':
                    $this->{$key} = (new AccountType)->fromArray(['email' => $value]);
                    break;
                case 'create_time':
                case 'ctime':
                case 'mtime':
                case 'mtime_created':
                    $this->{$camelCaseKey} = $this->getDateTime((int)$value);
                    break;
                case 'expire_date':
                    $this->{$camelCaseKey} = $this->getDateTime(strtotime($value));
                    break;
                default:
                    $this->{$camelCaseKey} = $value;
                    break;
            }
        }

        return $this;
    }

    /**
     * Time stamps vary a lot in Seafile. Sometimes it's seconds from 1970-01-01 00:00:00, sometimes
     * it's microseconds. You never know.
     *
     * @param int $value Int time stamp, either seconds or microseconds
     */
    public function getDateTime(int $value): DateTime
    {
        if ($value > 9999999999) { // microseconds it is
            $value = floor($value / 1000000);
        }

        return DateTime::createFromFormat("U", $value);
    }

    /**
     * Create from jsonResponse
     *
     * @param stdClass $jsonResponse Json response
     *
     * @return self
     * @throws Exception
     */
    public function fromJson(stdClass $jsonResponse) // type is given in derived class
    {
        $this->fromArray((array)$jsonResponse);

        return $this;
    }

    /**
     * Return instance as array
     *
     * @param int $mode Array mode
     *
     * @throws Exception
     */
    public function toArray(int $mode = self::ARRAY_ASSOC): array
    {
        switch ($mode) {
            case self::ARRAY_MULTI_PART:
                $caseHelper = CaseHelperFactory::make(CaseHelperFactory::INPUT_TYPE_CAMEL_CASE);
                $keyVals = $this->toArray(self::ARRAY_ASSOC);
                $multiPart = [];

                foreach ($keyVals as $key => $val) {
                    if ($val instanceof DateTime) {
                        $val = $val->format('U');
                    }

                    $multiPart[] = ['name' => $caseHelper->toSnakeCase($key), 'contents' => $val];
                }

                $array = $multiPart;
                break;
            default:
                $array = array_filter((array)$this); // removes empty values
                break;
        }

        return $array;
    }

    /**
     * Return instance as JSON string
     *
     * @return string JSON string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }
}
