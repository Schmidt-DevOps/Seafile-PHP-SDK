<?php

namespace Seafile\Client\Type;

use DateTime;
use Exception;
use stdClass;

/**
 * Interface TypeInterface
 */
interface TypeInterface
{
    /**
     * Populate from array
     *
     * @param array $fromArray Create from array
     *
     * @throws Exception
     */
    public function fromArray(array $fromArray): TypeInterface; // type is given in implementing class

    /**
     * Time stamps vary a lot in Seafile. Sometimes it's seconds from 1970-01-01 00:00:00, sometimes
     * it's microseconds. You never know.
     *
     * @param int $value Int time stamp, either seconds or microseconds
     */
    public function getDateTime(int $value): DateTime;

    /**
     * Create from jsonResponse
     *
     * @param stdClass $jsonResponse Json response
     *
     * @throws Exception
     *
     * @return self
     */
    public function fromJson(stdClass $jsonResponse): TypeInterface; // type is given in implementing class

    /**
     * Return instance as array
     *
     * @param int $mode Array mode
     *
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
