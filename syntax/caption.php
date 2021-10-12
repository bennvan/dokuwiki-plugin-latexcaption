<?php
/**
 * DokuWiki Plugin latexcaption (Main Syntax Component)
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

class syntax_plugin_latexcaption_caption extends DokuWiki_Syntax_Plugin 
{

    /**
     * Static variables set to keep track when scope is left.
     */
    private static $_types = array('figure', 'table','codeblock','fileblock');
    private static $_type = '';
    private static $_incaption = false;
    private static $_label = '';
    private static $_opts = array();
    private static $_parOpts = array();
    private static $_nested = false;
    
    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/../plugin.info.txt');
    }

    public function getType() {
        return 'container';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'container', 'protected');
    }

    public function getPType() {
        return 'block';
    }

    public function getSort() {
        return 319;
    }


    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{setcounter [a-z0-9=]+?}}',$mode,'plugin_latexcaption_caption');
        $this->Lexer->addEntryPattern('<figure.*?>(?=.*</figure>)',$mode,'plugin_latexcaption_caption');
        $this->Lexer->addEntryPattern('<table.*?>(?=.*</table>)',$mode,'plugin_latexcaption_caption');
        $this->Lexer->addEntryPattern('<codeblock.*?>(?=.*</codeblock>)',$mode,'plugin_latexcaption_caption');
        $this->Lexer->addEntryPattern('<fileblock.*?>(?=.*</fileblock>)',$mode,'plugin_latexcaption_caption');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</figure>','plugin_latexcaption_caption');
        $this->Lexer->addExitPattern('</table>','plugin_latexcaption_caption');
        $this->Lexer->addExitPattern('</codeblock>','plugin_latexcaption_caption');
        $this->Lexer->addExitPattern('</fileblock>','plugin_latexcaption_caption');

        $captiontag = $this->getConf('captiontag');
        $this->Lexer->addPattern('<'.$captiontag.'>','plugin_latexcaption_caption');
        $this->Lexer->addPattern('</'.$captiontag.'>','plugin_latexcaption_caption');
    }

    private function isSubType($type) {
        return (substr($type, 0, 3) == 'sub');
    }

    private function getParType($type) {
        return substr($type, 3);
    }

    public function handle($match, $state, $pos, Doku_Handler $handler){
        $params = [];
        if ($state == DOKU_LEXER_ENTER){
            global $caption_count;
            // INPUT $match:<type opts|label>
            // Strip the <>
            $match = substr($match,1,-1);
            // Retrieve the label name if any
            list($matches, $label) = explode('|', $match, 2);
            // retrieve type and options if any
            list($type, $opts) = explode(' ', trim($matches), 2);

            // explode again in the case of multiple options;
            $opts = (!empty($opts) ? explode(' ', $opts) : ['noalign',]);

            // Set dynamic class counter variable
            $type_counter_def = '$_'.$type.'_count';

            // Increment the counter of relevant type
            // Store the type in class for caption match to determine what html tag to use
            // This is ok since we will never have nested figures and more than one caption
            $this::$_type = $type;
            $this::$_label = $label;
            $this::$_opts = $opts;
            $this->{$type_counter_def} = (!isset($this->{$type_counter_def}) ? 1 : $this->{$type_counter_def}+1);

            // save params to class variables (cached for use in render)
            $type_counter = $this->{$type_counter_def};

            // Set global variable if theres a label (used for ref)
            if ($label) {
                $label = trim($label);
                // Check if we are counting a subtype to store parent in array for references
                if ($this->isSubType($type)) {
                    $partype = $this->getParType($type);
                    $parcount = $this->{'$_'.$partype.'_count'};
                }
                $caption_count[$label] = array($type, $type_counter, $parcount);
            }

            //Save parent options for use later
            if (!$this->isSubType($type)){
                $this::$_parOpts = $opts;
            } else {
                $this::$_nested = true;
            }

            // Set the params
            $params['xhtml']['tagtype'] = (in_array($type, ['figure', 'subfigure']) ? 'figure' : 'div');
            $params['label'] = $label; 
            $params['opts'] = $opts;
            $params['type'] = $type;

            return array($state, $match, $pos, $params);
        }
        if ($state == DOKU_LEXER_MATCHED){
            // Case of caption. 
            // Toggle the incaption flag
            $this::$_incaption = !$this::$_incaption;
            $type = $this::$_type;
            $params['label'] = $this::$_label;
            $params['xhtml']['captagtype'] = (in_array($type, ['figure', 'subfigure']) ? 'figcaption' : 'div');
            $params['incaption'] = $this::$_incaption;
            $params['type_counter'] = $this->{'$_'.$type.'_count'};
            $params['type'] = $type;
            // Decide what caption options to send to renderer
            $params['opts'] = ($this::$_nested ? $this::$_opts : $this::$_parOpts);
            

            return array($state, $match, $pos, $params);
        }
        if ($state == DOKU_LEXER_UNMATCHED){
            return array($state, $match, $pos, $params);
        }
        if ($state == DOKU_LEXER_EXIT){
            $type = $this::$_type;

            if (substr($type, 0, 3) == 'sub') {
                // Change environment back to non sub type
                $this::$_type = substr($type, 3);
                $this::$_nested = false;
            }
            else {
                // reset subtype counter
                $this->{'$_sub'.$type.'_count'} = 0;
            }
            $params['type'] = $type;
            $params['xhtml']['tagtype'] = (in_array($type, ['figure', 'subfigure']) ? 'figure' : 'div');
            return array($state, $match, $pos, $params);
        }
        if ($state == DOKU_LEXER_SPECIAL){
            if (substr($match,0,13) != '{{setcounter ') {
                return true;
            }

            $match = substr($match,13,-2);
            list($type,$num) = explode('=',trim($match));

            $type = trim($type);
            $num = (int) trim($num);

            if (!in_array($type,$this::$_types)) {
                return false;
            }

            // Update the counter. offset by 1 since counter is incremented on caption enter
            $this->{'$_'.$type.'_count'} = $num-1;

            return true;
        }
        // this should never be reached
        return true;
    }

    public function render($mode, Doku_Renderer $renderer, $data) {

        if (empty($data)) {
            return false;
        }

        list($state, $match, $pos, $params) = $data;

        $langset = ($this->getConf('abbrev') ? 'abbrev' : 'long');

        if (!in_array($mode, ['xhtml','odt', 'latex'])) {
            return true;
        }

        if ($mode == 'xhtml') {
            if ($state == DOKU_LEXER_ENTER) {
                // We know we already have a valid type on entering this
                $type = $params['type'];
                $tagtype = $params['xhtml']['tagtype'];
                $label = $params['label'];
                $opts = $params['opts'];

                // print alignment/additional classes
                $classes = implode(' plugin_latexcaption_', $opts);
                // Rendering
                $markup = '<'.$tagtype.' class="plugin_latexcaption_'.$type.' plugin_latexcaption_'.$classes.'" ';

                if ($label){
                    $markup .= 'id="'.$renderer->_xmlEntities($label).'"';
                }

                $markup .= '>';
                $renderer->doc .= $markup;
                return true; 
            }

            if ($state == DOKU_LEXER_MATCHED) {
                $captagtype = $params['xhtml']['captagtype'];
                $incaption = $params['incaption'];
                $count = $params['type_counter'];
                $type = $params['type'];
                $label = $params['label'];
                $opts = $params['opts'];

                $separator = (in_array('blank', $opts) ? '' : ': ');

                // Rendering a caption
                if ($incaption) {
                    $markup .= '<'.$captagtype.' class="plugin_latexcaption_caption"><span class="plugin_latexcaption_caption_number"';
                    // Set title to label
                    // if ($label) $markup .= ' title="'.$label.'"';
                    $markup .= '>';
                    if (substr($type, 0, 3) == 'sub') {
                        $markup .= '('.number_to_alphabet($count).') ';
                    }
                    else {
                        $markup .= $this->getLang($type.$langset).' '. $count.$separator;
                    }
                    $markup .= '</span><span class="plugin_latexcaption_caption_text">';

                    $renderer->doc .= $markup;
                } 
                else {
                    $renderer->doc .= "</span></$captagtype>";
                    
                }
                return true;
            }

            if ($state == DOKU_LEXER_UNMATCHED) {
                // return the dokuwiki markup within the figure tags
                $renderer->doc .= $renderer->_xmlEntities($match);
            }

            if ($state == DOKU_LEXER_EXIT) {
                $tagtype = $params['xhtml']['tagtype'];
                $renderer->doc .= "</$tagtype>";
                return true;
            }
        }

        if ($mode == 'latex') { 
            // All Doku $states get type param
            // Only figure and table supported.
            $type = $params['type'];
            if (!in_array($type, ['figure', 'table', 'subfigure'])){
                return true;
            }

            if ($state == DOKU_LEXER_ENTER) {
                // Render
                $renderer->doc .= "\begin{$type}";
                return true;
            }   
            if ($state == DOKU_LEXER_MATCHED) {
                $incaption = $params['incaption'];
                $label = $params['label'];
                if ($incaption) {
                    $out = '\caption{';
                } else {
                    $out = '}';
                    if ($label){
                        $out .= "\n" . "\label{$label}";
                    }
                }
                $renderer->doc .= $out;
                return true;
            }
            if ($state == DOKU_LEXER_UNMATCHED) {
                // Pass it through
                $renderer->doc .= $match;
                return true;
            }
            if ($state == DOKU_LEXER_EXIT) {
                $renderer->doc .= "\end{$type}";
                return true;
            }
        }
        
        /**
         * WARNING: The odt mode seems to work in general, but strange things happen
         *          with the tables - therefore, within the table tags only a table
         *            is allowed, without any additional markup.
         */
        if ($mode == 'odt') {
            // All Doku $states get type param
            // Only figure and table supported.
            $type = $params['type'];
            if (!in_array($type, ['figure', 'table'])) {
                return true;
            }

            if ($state == DOKU_LEXER_ENTER) {
                $renderer->p_open();
                return true;
            }   
            if ($state == DOKU_LEXER_MATCHED) {
                $incaption = $params['incaption'];
                $count = $params['type_counter'];
                $type = $params['type'];
                $label = $params['label'];

                if ($incaption) {
                    $renderer->p_close();
                    $style_name = ($type == 'figure' ? 'Illustration' : 'Table');
                    $labelname = ($label ? $label : 'ref'.$style_name.$count);

                    $out = '<text:p text:style-name="'.$style_name.'">';
                    $out .= $this->getLang($type.$langset);
                    $out .= '<text:sequence text:ref-name="'.$labelname.'"';
                    $out .= 'text:name="'.$style_name.'" text:formula="ooow:'.$style_name.'+1" style:num-format="1">';
                    $out .= ' ' . $count . '</text:sequence>: ';

                    $renderer->doc .= $out;
                } else {
                    $renderer->doc .= '</text:p>';
                    $renderer->p_open();
                }
                return true;
            }
            if ($state == DOKU_LEXER_UNMATCHED) {
                // Pass it through
                $renderer->cdata($match);
                return true;
            }
            if ($state == DOKU_LEXER_EXIT) {
                $renderer->p_close();
                return true;
            }           
        }
    }
}

// vim:ts=4:sw=4:et:
