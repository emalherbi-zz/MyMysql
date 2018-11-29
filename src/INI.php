<?php

/*
 * Eduardo Malherbi Martins (http://emalherbi.com/)
 * Copyright @emm
 * Full Stack Web Developer.
 */

namespace MyMysql;

class INI
{
    const RETURN_NEWLINE = "\r\n";

    public static function write($filename, $ini)
    {
        $string = '';
        foreach (array_keys($ini) as $key) {
            $string .= '['.$key.']'.self::RETURN_NEWLINE;
            $string .= self::write_get_string($ini[$key], '').self::RETURN_NEWLINE;
        }

        file_put_contents($filename, $string);
    }

    public static function write_get_string(&$ini, $prefix)
    {
        $string = '';
        ksort($ini);
        foreach ($ini as $key => $val) {
            if (is_array($val)) {
                $string .= self::write_get_string($ini[$key], $prefix.$key.'.');
            } else {
                $string .= $prefix.$key.' = '.str_replace(self::RETURN_NEWLINE, "\\\r\\\n", self::set_value($val)).self::RETURN_NEWLINE;
            }
        }

        return $string;
    }

    public static function set_value($val)
    {
        if (true === $val) {
            return 'true';
        } elseif (false === $val) {
            return 'false';
        }

        return $val;
    }
}
