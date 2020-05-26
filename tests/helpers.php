<?php

use Illuminate\Support\Arr;

if (!function_exists('create')) {
    function create($class, $attributes = [], $times = null, $states = null)
    {
        if (is_null($states)) {
            return factory($class, $times)->create($attributes);
        }

        return factory($class, $times)->states($states)->create($attributes);
    }
}
if (!function_exists('make')) {
    function make($class, $attributes = [], $times = null, $states = null)
    {
        if (is_null($states)) {
            return factory($class, $times)->make($attributes);
        }

        return factory($class, $times)->states($states)->make($attributes);
    }
}
if (!function_exists('raw')) {
    function raw($class, $attributes = [], $times = null, $states = null)
    {
        if (is_null($states)) {
            return factory($class, $times)->raw($attributes);
        }

        return factory($class, $times)->states($states)->raw($attributes);
    }
}
if (!function_exists('hmap')) {

  /**
   * Simple function which aids in converting the tree hierarchy into something
   * more easily testable...
   *
   * @param array   $nodes
   * @return array
   */
    function hmap(array $nodes, $preserve = null)
    {
        $output = [];

        foreach ($nodes as $node) {
            if (is_null($preserve)) {
                $output[$node['name']] = empty($node['children']) ? null : hmap($node['children']);
            } else {
                $preserve = is_string($preserve) ? [$preserve] : $preserve;

                $current = Arr::only($node, $preserve);
                if (array_key_exists('children', $node)) {
                    $children = $node['children'];

                    if (count($children) > 0) {
                        $current['children'] = hmap($children, $preserve);
                    }
                }

                $output[] = $current;
            }
        }

        return $output;
    }
}

if (!function_exists('array_ints_keys')) {

  /**
   * Cast provided keys's values into ints. This is to wrestle with PDO driver
   * inconsistencies.
   *
   * @param   array $input
   * @param   mixed $keys
   * @return  array
   */
    function array_ints_keys(array $input, $keys='id')
    {
        $keys = is_string($keys) ? [$keys] : $keys;

        array_walk_recursive($input, function (&$value, $key) use ($keys) {
            if (array_search($key, $keys) !== false) {
                $value = (int) $value;
            }
        });

        return $input;
    }
}
