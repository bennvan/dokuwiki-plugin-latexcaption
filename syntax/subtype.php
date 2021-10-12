<?php
/**
 * DokuWiki Plugin latexcaption (Subtype Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';

class syntax_plugin_latexcaption_subtype extends syntax_plugin_latexcaption_caption
{
    // Defined separately to ensure that eg <figure> does not exit with </subfigure>;    
    public function connectTo($mode) {
        $this->Lexer->addEntryPattern('<subfigure.*?>(?=.*</subfigure>)',$mode, 'plugin_latexcaption_caption');
        $this->Lexer->addEntryPattern('<subtable.*?>(?=.*</subtable>)',$mode, 'plugin_latexcaption_caption');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</subfigure>','plugin_latexcaption_caption');
        $this->Lexer->addExitPattern('</subtable>','plugin_latexcaption_caption');
    }
}

// vim:ts=4:sw=4:et:
