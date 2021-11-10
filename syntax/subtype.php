<?php
/**
 * DokuWiki Plugin latexcaption (Subtype Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 */


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
