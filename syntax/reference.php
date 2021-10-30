<?php
/**
 * DokuWiki Plugin latexcaption (Renderer Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 * @author  Till Biskup <till@till-biskup>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_PLUGIN.'syntax.php';
require_once 'caption_helper.php';

class syntax_plugin_latexcaption_reference extends DokuWiki_Syntax_Plugin {

    /**
     * Array containing the types of environment supported by the plugin
     */

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/../plugin.info.txt');
    }

    public function getType() {
        return 'substition';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'container', 'protected');
    }

    public function getPType() {
        return 'normal';
    }

    public function getSort() {
        return 319;
    }


    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{ref>.+?}}',$mode,'plugin_latexcaption_reference');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler){
        if (substr($match,0,6) != '{{ref>') {
            return array();
        }
        return array($state, substr($match,6,-2));
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if (empty($data)) {
            return false;
        }

        list($state,$match) = $data;
        global $caption_count;

        $label = $match;
        $langset = ($this->getConf('abbrev') ? 'abbrev' : 'long');

        // Only special state allowed
        if ($state !== DOKU_LEXER_SPECIAL) {
                return true;
            }

        /** @var Doku_Renderer_metadata $renderer */
        // Store refs from global variable into metadata
        if ($mode == 'metadata') {
            if ($caption_count[$label]) {
                $renderer->meta['plugins']['latexcaption']['references'][$label] = $caption_count[$label];   
            }
            return true;
        }

        /** @var Doku_Renderer_metadata $renderer */
        if ($mode == 'xhtml') {
            global $INFO;

            $markup = '<a href="#'.$label.'">';
            // Retrieve the figure label from the global array or metadata
            $caption = ($caption_count[$label]) ? $caption_count[$label] : $INFO['meta']['plugins']['latexcaption']['references'][$label];
            if ($caption) {
                list($type, $num, $parnum) = $caption;
                if (substr($type, 0, 3) == 'sub') {
                    $type = substr($type, 3);
                    $markup .= $this->getLang($type.$langset).' '.$parnum.'('.number_to_alphabet($num).')';
                }
                else{
                    $markup .= $this->getLang($type.$langset).' '.$num;
                }
            } else {
                $markup .= '??REF:'.$label.'??';
            }
            $markup .= '</a>';
            $renderer->doc .= $markup;

            return true;
        }
        
        if ($mode == 'latex') {
            $renderer->doc .= '\ref{'.$label.'}';
            return true;
        }

        if ($mode == 'odt') {
            $renderer->doc .= '<text:sequence-ref text:reference-format="value" text:ref-name="'.$label.'">';
            $renderer->doc .= $caption_count[$label];
            $renderer->doc .= '</text:sequence-ref>';
            return true;
        }

        // unsupported $mode
        return true;
    }
}

// vim:ts=4:sw=4:et:
