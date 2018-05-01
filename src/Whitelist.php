<?php

namespace Reliv\WhiteRat;

/**
 * Filter that will remove non-whitelisted values from a subject.
 *
 * The array is a mix of associative and indexed values, although the
 * order of indexed values is irrelevant. When a value is indexed, it must
 * be a string. When it is associative, it must be either an array or a
 * boolean. Each string, whether it is a key or a value, correlates with a
 * key in the block config.
 *
 * If a string appears as an indexed value, the correlating key in the block
 * config, including all fields below it, are whitelisted.
 *
 * If a string appears as a key, and the value is a boolean, it indicates
 * whether the associated config is whitelisted or not.
 *
 * If a string appears as a key, and the value is an array, this indicates
 * a more specific whitelist rule for sub-keys of the associated config
 * item. Whitelisting rules then proceed recursively.
 * 
 * It is also possible to whitelist indexed arrays. To do this, create an array
 * within in array, where the sub-array is the only child of its parent and is
 * an indexed child. This looks like a set of double brackets, and we refer to
 * it as the "double-array."
 *
 * Whitelist rules are validated upon construction of the whitelist. An
 * exception of type Reliv\WhiteRat\WhitelistValidationException will be thrown
 * if there are any problems detected in the rules given, and the path to the
 * rule and an explanation of the error will be provided.
 *
 * By default, all fields are NOT whitelisted, and no config will be
 * encoded to JSON.
 *
 * @example
 *
 * $whitelist = new Whitelist([
 *      'foo',
 *      'bar' => true,
 *      'baz' => [
 *          'flip' => true,
 *          'flop' => [ ['flummox'] ],
 *          'quux',
 *      ]
 * ]);
 * $result = $whitelist->filter([ ... ]);
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
     * @param array $rules See class docs for format instructions.
     * @throws WhitelistValidationException
     */
    public function __construct(array $rules) {
        $this->validateRules($rules, ['(root)']);
        $this->rules = $rules;
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
