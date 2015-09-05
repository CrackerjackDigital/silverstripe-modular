<?php


class ModularUtils extends Object {

    /**
     * Replace {token} in string from provided map of token => value
     *
     * @param       $string
     * @param array $replaceWith
     * @return mixed
     */
    public static function detokenise($string, array $replaceWith) {
        $tokens = array_map(
            function ($token) {
                return '{' . $token . '}';
            },
            array_keys(
                $replaceWith
            )
        );

        return str_replace(
            $tokens,
            array_values($replaceWith),
            $string
        );
    }

    /**
     * Returns provided camel-case string as converted to spaced 'proper case' preserving acronyms.
     * @param        $in
     * @param string $join
     * @return string
     */
    public static function decamel($in, $join = ' ') {
        $parts = preg_split("/((?<=[a-z])(?=[A-Z])|(?=[A-Z][a-z]))/", $in);
        return implode($join, $parts);
    }
}