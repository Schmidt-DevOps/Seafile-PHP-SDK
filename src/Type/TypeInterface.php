<?php

namespace Seafile\Client\Type;

use DateTime;
use Exception;
use stdClass;

/**
 * Interface TypeInterface
 * @package Seafile\Client\Type
 */
interface TypeInterface
{
    /**
     * Populate from array
     *
     * @param array $fromArray Create from array
     *
     * @return self
     * @throws Exception
     */
    public function fromArray(array $fromArray); // type is given in implementing class

    /**
     * Time stamps vary a lot in Seafile. Sometimes it's seconds from 1970-01-01 00:00:00, sometimes
     * it's microseconds. You never know.
     *
     * @param int $value Int time stamp, either seconds or microseconds
     *
     * @return DateTime
     */
    public function getDateTime(int $value): DateTime;

    /**
     * Create from jsonResponse
     *
     * @param stdClass $jsonResponse Json response
     *
     * @return self
     * @throws Exception
     */
    public function fromJson(stdClass $jsonResponse); // type is given in implementing class

    /**
     * Return instance as array
     *
     * @param int $mode Array mode
     *
     * @return array
     * @throws Exception
     */
    public function toArray(int $mode = Type::ARRAY_ASSOC): array;

    /**
     * Return instance as JSON string
     *
     * @return string JSON string
     */
    public function toJson(): string;
}
