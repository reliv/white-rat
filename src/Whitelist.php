<?php

namespace Reliv\WhiteRat;

/**
 * Invokable that will remove non-whitelisted values from a subject.
 * 
 * Internally uses Reliv\WhiteRat\Filter.
 *
 * @package Reliv\WhiteRat
 */
class Whitelist implements WhitelistInterface
{
    /** @var array */
    protected $rules;

    /** @var Filter */
    protected $filterObj;

    /**
     * Whitelist constructor.
     * @param array $rules See README.md for format
     * @throws WhitelistValidationException
     */
    public function __construct(array $rules) {
        $this->filterObj = new Filter();
        $this->filterObj->validate($rules);
        $this->rules = $rules;
    }

    /**
     * @param array $subject Array to be filtered
     * @return array
     */
    public function __invoke(array $subject) : array
    {
        return $this->filterObj->__invoke($subject, $this->rules);
    }

    /**
     * @deprecated Use __invoke() instead
     */
    public function filter(array $subject) : array
    {
        return $this($subject);
    }
}
