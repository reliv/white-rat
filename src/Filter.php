<?php

namespace Reliv\WhiteRat;

class Filter implements FilterInterface
{
    /**
     * Validate a rule set.
     * 
     * @param array $rules
     * @return FilterInterface
     * @throws WhitelistValidationException
     */
    public function validate(array $rules) : FilterInterface
    {
        $this->validateRules($rules, ['(root)']);
        return $this;
    }
    
    /**
     * @param array $rules
     * @param array $keyPath
     * @return void
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
     * Filter a subject by given rules
     * 
     * Does NOT validate the rule set in advance.
     * 
     * @param array $subject
     * @param array $rules
     * @return array
     */
    public function __invoke(array $subject, array $rules) : array
    {
        $filteredSubject = [];
        foreach ($rules as $key => $rule) {
            if (is_int($key)) {
                if (is_array($rule)) {
                    foreach ($subject as $childVal) {
                        $filteredSubject[] = $this($childVal, $rule);
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
                    $filteredSubject[$key] = $this($subject[$key], $rule);
                }
            }
        }
        return $filteredSubject;
    }
}
