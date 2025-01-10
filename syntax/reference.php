<?php
/**
 * DokuWiki Plugin latexcaption (Renderer Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 * @author  Till Biskup <till@till-biskup>
 */


class syntax_plugin_latexcaption_reference extends \dokuwiki\Extension\SyntaxPlugin {

    /** @var $helper helper_plugin_latexcaption */
    var $helper = null;

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
        $this->Lexer->addSpecialPattern('{{autoref>.+?}}',$mode,'plugin_latexcaption_reference');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler){
        // Strip the {{}}
        $match = substr($match,2,-2);
        list($type, $label) = explode('>',$match);
        // Set the params
        $params['label'] = $label; 
        $params['type'] = $type;

        return array($state, $match, $pos, $params);
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if (empty($data)) {
            return false;
        }

        list($state, $match, $pos, $params) = $data;
        // Only special state allowed
        if ($state !== DOKU_LEXER_SPECIAL) {
                return true;
            }

        global $caption_count;
        global $INFO;

        $type = $params['type'];
        $label = $params['label'];

        $langset = ($this->getConf('abbrev') ? 'abbrev' : 'long');
        // Should we display the reference type
        $disptype = (substr($type, 0, 4) == 'auto') || $this->getConf('alwaysautoref');
        // Retrieve the figure label from the global array or metadata
        $caption = $caption_count[$label] ? $caption_count[$label] : $INFO['meta']['plugin']['latexcaption']['references'][$label];

        // If we cant find a matching label we assume its for a mathjax equation
        $mathjaxref = false;
        if (!$caption && $this->getConf('mathjaxref')) {
            // Only allow true if mathjax plugin is available
            $mathjaxref = !plugin_isdisabled('mathjax');
        } 

        /** @var Doku_Renderer_xhtml $renderer */
        if ($mode == 'xhtml') {
            if (!$this->helper)
                $this->helper = plugin_load('helper', 'latexcaption');

            if ($mathjaxref) {
                // Only passing reference through for mathjax rendering in js
                $markup = '<a href="#mjx-eqn'.rawurlencode(':').$label.'">';
                $markup .= ($disptype) ? $this->getLang('equation'.$langset).' ' : '';
                $markup .= '\eqref{'.$label.'}</a>';
                $renderer->doc .= $markup;
                return true;
            }

            $markup = '<a href="#'.$label.'">';

            if ($caption) {
                list($type, $num, $parnum) = $caption;
                if (substr($type, 0, 3) == 'sub') {
                    $type = substr($type, 3);
                    $markup .= $disptype ? $this->getLang($type.$langset).' ' : '';
                    $markup .= $parnum.'('.$this->helper->number_to_alphabet($num).')';
                }
                else{
                    $markup .= $disptype ? $this->getLang($type.$langset).' ' : '';
                    $markup .= $num;
                }
            } else {
                $markup .= '??REF:'.$label.'??';
            }
            $markup .= '</a>';
            $renderer->doc .= $markup;

            return true;
        }
        
        if ($mode == 'latex') {
            if ($disptype) {
                $renderer->doc .= '\autoref'.'{'.$label.'}';
            } else {
                $renderer->doc .= '\ref'.'{'.$label.'}';
            }
            
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
