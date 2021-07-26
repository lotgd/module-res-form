<?php
declare(strict_types=1);

namespace LotGD\Core\Events;

use LotGD\Core\Exceptions\ArgumentException;

/**
 * EventContextData to provide a basic structure for managing contextual data of an event.
 *
 * This class must be immutable and returns always a new instance of itself for any change.
 * @immutable
 */
class EventContextData
{
    protected static ?array $argumentConfig = null;

    /**
     * protected constructor..
     * @see self::create
     * @param array $data
     */
    protected function __construct(private array $data) {}

    /**
     * Creates a new instance of a data container.
     *
     * Sub types can change this method to force certain parameters.
     * @param array $data
     * @return EventContextData
     */
    public static function create(array $data): self
    {
        if (isset(static::$argumentConfig)) {
            static::checkConfiguration($data);
        }

        return new static($data);
    }

    /**
     * Checks a field configuration given in self::$argumentConfig.
     * @param array $data
     * @throws ArgumentException
     */
    public static function checkConfiguration(array $data)
    {
        $configuration = static::$argumentConfig;
        $types = [
            "mixed" => function ($x) {
                return true;
            },
            "int" => function ($x) {
                return \is_int($x);
            },
            "float" => function ($x) {
                return \is_float($x);
            },
            "numeric" => function ($x) {
                return \is_numeric($x);
            },
            "string" => function ($x) {
                return \is_string($x);
            },
        ];

        $keys = \array_keys($data);
        foreach ($keys as $key) {
            if (!isset($configuration[$key])) {
                throw new ArgumentException(\sprintf("%s does not accept a field called %s", static::class, $key));
            }
        }
        foreach ($configuration as $key => $config) {
            if ($config["required"] === true and !isset($data[$key])) {
                throw new ArgumentException(\sprintf("%s must have a field called %s.", static::class, $key));
            }

            if (isset($types[$config["type"]])) {
                if ($types[$config["type"]]($data[$key]) === false) {
                    throw new ArgumentException(\sprintf("The field %s of %s must be of type %s.", $key, static::class, $config["type"]));
                }
            } else {
                if (!$data[$key] instanceof $config["type"]) {
                    throw new ArgumentException(\sprintf("The field %s of %s must be of type %s", $key, static::class, $config["type"]));
                }
            }
        }
    }

    /**
     * Returns true if container has a certain field.
     * @param string $field
     * @return bool
     */
    public function has(string $field): bool
    {
        return \array_key_exists($field, $this->data);
    }

    /**
     * Returns the value of a field.
     * @param string $field
     * @return mixed
     */
    public function get(string $field)
    {
        if ($this->has($field)) {
            return $this->data[$field];
        }
        $this->throwException($field);
    }

    /**
     * Sets a field to a new value and returns a new data container.
     * @param string $field
     * @param mixed $value
     * @return EventContextData
     */
    public function set(string $field, mixed $value): self
    {
        if ($this->has($field)) {
            $data = $this->data;
            $data[$field] = $value;

            return new static($data);
        }
        $this->throwException($field);
    }

    /**
     * Sets multiple fields at once.
     * @param array $data array of $field=>$value pairs
     * @return EventContextData
     */
    public function setFields(array $data): self
    {
        $data = $this->data;

        foreach ($data as $field => $value) {
            if ($this->has($field)) {
                $data[$field] = $value;
            } else {
                $this->throwException($field);
            }
        }

        return new static($data);
    }

    /**
     * Returns a list of fields in this context.
     * @return array
     */
    private function getListOfFields(): array
    {
        return \array_keys($this->data);
    }

    /**
     * Returns a comma separated string with all allowed fields, for debugging reasons.
     * @return string
     */
    private function getFormattedListOfFields(): string
    {
        return \substr(
            \implode(", ", $this->getListOfFields()),
            0,
            -2
        );
    }

    /**
     * internal use only - throws an ArgumentException a field is given that's not valid.
     * @param $field
     * @throws ArgumentException
     */
    private function throwException($field)
    {
        throw new ArgumentException(
            "{$field} is not valid in this context, only {$this->getFormattedListOfFields()} are allowed."
        );
    }
}
