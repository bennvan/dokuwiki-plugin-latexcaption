<?php
/**
 * DokuWiki Plugin latexcaption (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Ben van Magill <ben.vanmagill16@gmail.com>
 * @author  Till Biskup <till@till-biskup>
 */
 

class action_plugin_latexcaption extends \dokuwiki\Extension\ActionPlugin {

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook("TOOLBAR_DEFINE", "AFTER", $this, "insert_button", array ());
    }

    /**
    * Inserts a toolbar button
    */
    public function insert_button(&$event, $param) {
        $captiontag = $this->getConf('captiontag');
        $event->data[] = array (
            'type' => 'picker',
            'title' => $this->getLang('latexcaption'),
            'icon' => '../../plugins/latexcaption/images/picker.png',
            'class' => 'captionpicker',
            'list' => array(
                array(
                     'type' => 'format',
                     'title' => $this->getLang('figure'),
                     'icon' => '../../plugins/latexcaption/images/fig.png',
                     'open' => '<figure center|fig_label>\n',
                     'sample' => '{{:img |title}}',
                     'close' => '\n<'.$captiontag.'>caption</'.$captiontag.'>\n</figure>',
                ),
                array(
                     'type' => 'format',
                     'title' => $this->getLang('subfigure'),
                     'icon' => '../../plugins/latexcaption/images/subfig.png',
                     'open' => '<figure center|fig_label>\n<subfigure|subfig_a_label>\n',
                     'sample' => '{{:img1 |title}}',
                     'close' => '\n<'.$captiontag.'>subfigure a caption</'.$captiontag.'>\n</subfigure>\n<subfigure|subfig_b_label>\n{{:img2 | title}}\n<'.$captiontag.'>subfigure b caption</'.$captiontag.'>\n</subfigure>\n<'.$captiontag.'>Main caption</'.$captiontag.'>\n</figure>',
                ),
                array(
                     'type' => 'format',
                     'title' => $this->getLang('table'),
                     'icon' => '../../plugins/latexcaption/images/tab.png',
                     'open' => '<table |tab_label>\n<'.$captiontag.'>caption</'.$captiontag.'>\n',
                     'sample' => '^ Header1 ^ Header2 ^\n| foo    | bar    |\n',
                     'close' => '</table>',
                ),
                array(
                     'type' => 'format',
                     'title' => $this->getLang('code'),
                     'icon' => '../../plugins/latexcaption/images/code.png',
                     'open' => '<codeblock |code_label>\n<'.$captiontag.'>caption</'.$captiontag.'>\n',
                     'sample' => '<code>\n...\n</code>\n',
                     'close' => '</codeblock>',
                ),
                array(
                     'type' => 'format',
                     'title' => $this->getLang('file'),
                     'icon' => '../../plugins/latexcaption/images/file.png',
                     'open' => '<fileblock |file_label>\n<'.$captiontag.'>caption</'.$captiontag.'>\n',
                     'sample' => '<file "" foo.txt>\n...\n</file>\n',
                     'close' => '</fileblock>',
                ),
                array(
                     'type' => 'insert',
                     'title' => $this->getLang('reference'),
                     'icon' => '../../plugins/latexcaption/images/ref.png',
                     'insert' => '{{ref>label}}',
                )
            )
        );
    }
}
