<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 * @copyright https://github.com/laminas/laminas-server/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-server/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Server\Method;

/**
 * Method prototype metadata
 */
class Prototype
{
    /**
     * @var string
     */
    protected $returnType = 'void';

    /**
     * Map parameter names to parameter index
     *
     * @var array
     */
    protected $parameterNameMap = [];

    /**
     * @var array
     */
    protected $parameters = [];

    public function __construct(?array $options = null)
    {
        if ((null !== $options) && is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setReturnType(string $returnType): self
    {
        $this->returnType = $returnType;
        return $this;
    }

    public function getReturnType(): string
    {
        return $this->returnType;
    }

    /**
     * Add a parameter
     *
     * @param  string|Parameter $parameter
     * @return $this
     */
    public function addParameter($parameter): self
    {
        if ($parameter instanceof Parameter) {
            $this->parameters[] = $parameter;
            if (null !== ($name = $parameter->getName())) {
                $this->parameterNameMap[$name] = count($this->parameters) - 1;
            }
        } else {
            $parameter = new Parameter([
                'type' => (string) $parameter,
            ]);
            $this->parameters[] = $parameter;
        }
        return $this;
    }

    public function addParameters(array $parameters): self
    {
        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
        return $this;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters       = [];
        $this->parameterNameMap = [];
        $this->addParameters($parameters);
        return $this;
    }

    /**
     * Retrieve parameters as list of types
     *
     * @return array
     */
    public function getParameters(): array
    {
        $types = [];
        foreach ($this->parameters as $parameter) {
            $types[] = $parameter->getType();
        }
        return $types;
    }

    public function getParameterObjects(): array
    {
        return $this->parameters;
    }

    /**
     * Retrieve a single parameter by name or index
     *
     * @param  string|int $index
     * @return null|Parameter
     */
    public function getParameter($index): ?Parameter
    {
        if (! is_string($index) && ! is_numeric($index)) {
            return null;
        }
        if (array_key_exists($index, $this->parameterNameMap)) {
            $index = $this->parameterNameMap[$index];
        }
        if (array_key_exists($index, $this->parameters)) {
            return $this->parameters[$index];
        }
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        return [
            'returnType' => $this->getReturnType(),
            'parameters' => $this->getParameters(),
        ];
    }
}
