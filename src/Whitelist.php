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
 *          'flop' => [
 *              ...
 *          ],
 *          'quux',
 *      ]
 * ]);
 *
 * $result = $whitelist([ ...]);
 * @package Reliv\WhiteRat
 */
class Whitelist
{
    /**
     * @var array
     */
    protected $whitelist;

    /**
     * Whitelist constructor.
     * @param array $whitelist
     * @throws StructureException
     */
    public function __construct(array $whitelist) {
        $this->validateWhitelist($whitelist, []);
        $this->whitelist = $whitelist;
    }

    /**
     * @param array $whitelist
     * @param array $keyPath
     * @throws StructureException
     */
    private function validateWhitelist(array $whitelist, array $keyPath)
    {
        foreach ($whitelist as $key => $rule) {
            if (is_int($key)) {
                if (is_array($rule)) {
                    if (count($whitelist) > 1) {
                        $m = "$path: Only one item is allowed when it is indexed and is an array";
                        throw new StructureException($m);
                    }
                    $this->validateWhitelist($rule, array_merge($keyPath, ['[#]']));
                    continue;
                } elseif (!is_string($rule)) {
                    $path = implode(' => ', $keyPath);
                    $m = "$path: Indexed values after [0] must be strings";
                    throw new StructureException($m);
                }
                $key = $rule;
            } elseif (is_array($rule)) {
                $this->validateWhitelist($rule, array_merge($keyPath, [$key]));
            } elseif (!is_string($rule) && !is_bool($rule)) {
                $path = implode(' => ', array_merge($keyPath, [$key]));
                throw new StructureException("$path: Keyed values must be string, bool, or array");
            }
        }
    }

    /**
     * @param array $subject Array to be filtered
     * @return array
     */
    public function filter(array $subject) : array
    {
        return $this->filterRecurse($this->whitelist, $subject);
    }

    /**
     * @param array $whitelist
     * @param array $subject
     * @param array $keyPath
     * @return array
     */
    private function filterRecurse(array $whitelist, array $subject) : array
    {
        $filteredSubject = [];
        foreach ($whitelist as $key => $rule) {
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
