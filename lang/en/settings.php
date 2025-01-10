<?php
/**
 * DokuWiki Plugin latexcaption Settings
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 */

// keys need to match the config setting name
$lang['abbrev'] = 'Use abbreviations (Fig., Tab.) in caption instead of full names (Figure, Table)?';
$lang['captiontag'] = 'Tag to use for caption. Change this if the caption tag conflicts with your template.';
$lang['mathjaxref'] = 'If enabled, in-text references not matched by this plugin are presumed to be equation references and are output for rendering by MathJax in Javascript. Ensure the MathJax plugin is installed before enabling this option.';
$lang['alwaysautoref'] = 'If enabled, <code>{{ref>mylabel}}</code> or <code>{{autoref>mylabel}}</code>  will output the reference type along with the number. If disabled, <code>{{ref>mylabel}}</code> will output only the reference number.';
//Setup VIM: ex: et ts=4 :
