<?php

namespace Reliv\WhiteRat;

/**
 * Filter that will remove non-whitelisted values from a subject.
 *
 * @package Reliv\WhiteRat
 */
class Whitelist
{
    /**
     * @var array
     */
    protected $rules;

    /**
     * Whitelist constructor.
     * @param array $rules See README.md for format
     * @throws WhitelistValidationException
     */
    public function __construct(array $rules) {
        $this->validateRules($rules, ['(root)']);
        $this->rules = $rules;
    }

    /**
     * Return the rules array that was given to the whitelist at construction.
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     * @param array $keyPath
     * @throws WhitelistValidationException
     */
    private function validateRules(array $rules, array $keyPath)
    {
        foreach ($rules as $key => $rule) {
            $pathStr = ('[' . implode('] => [', array_merge($keyPath, [$key])) . ']');
            if (is_int($key)) {
                if (is_array($rule)) {
                    if (count($rules) > 1) {
                        throw new WhitelistValidationException("$pathStr: Double-array should have exactly one child");
                    }
                    $this->validateRules($rule, array_merge($keyPath, ['[#]']));
                    continue;
                } elseif (!is_string($rule)) {
                    throw new WhitelistValidationException(($key == 0) ?
                        "$pathStr: First indexed value must be string or array" :
                        "$pathStr: Indexed values after [0] must be strings"
                    );
                }
                $key = $rule;
            } elseif (is_array($rule)) {
                $this->validateRules($rule, array_merge($keyPath, [$key]));
            } elseif (!is_string($rule) && !is_bool($rule)) {
                throw new WhitelistValidationException("$pathStr: Keyed values must be string, bool, or array");
            }
        }
    }

    /**
     * @param array $subject Array to be filtered
     * @return array
     */
    public function filter(array $subject) : array
    {
        return $this->filterRecurse($this->rules, $subject);
    }

    /**
     * @param array $rules
     * @param array $subject
     * @param array $keyPath
     * @return array
     */
    private function filterRecurse(array $rules, array $subject) : array
    {
        $filteredSubject = [];
        foreach ($rules as $key => $rule) {
            if (is_int($key)) {
                if (is_array($rule)) {
                    foreach ($subject as $childVal) {
                        $filteredSubject[] =
                            $this->filterRecurse($rule, $childVal);
                    }
                    continue;
                }
                $key = $rule;
            }
            if (is_string($rule) || (is_bool($rule) && $rule)) {
                if (array_key_exists($key, $subject)) {
                    $filteredSubject[$key] = $subject[$key];
                }
            } elseif (is_array($rule)) {
                if (array_key_exists($key, $subject)) {
                    $filteredSubject[$key] =
                        $this->filterRecurse($rule, $subject[$key]);
                }
            }
        }
        return $filteredSubject;
    }
}
