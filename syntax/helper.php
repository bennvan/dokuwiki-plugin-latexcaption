<?php

/**
 * DokuWiki Plugin latexcaption (Helper functions)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 */

/**
 * Common functions
 */
class helper_plugin_latexcaption extends DokuWiki_Plugin {

    function number_to_alphabet($number) {
        $number = intval($number);
        if ($number <= 0) {
            return '';
        }
        $alphabet = '';
        while($number != 0) {
            $p = ($number - 1) % 26;
            $number = intval(($number - $p) / 26);
            $alphabet = chr(65 + $p) . $alphabet;
        }
        return strtolower($alphabet);
    }
}

