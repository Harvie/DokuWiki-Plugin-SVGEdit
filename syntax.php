<?php 
/** 
 * SVGEdit Plugin: Nice way, to create, store, edit and embed SVG images into DokuWiki
 * Usage: 
 * embed svg using do=export_svg
 *   {{svg>page.svg}}
 *   {{svg>namespace:page.svg}}
 * base64 encode svg directly (requires ~~NOCACHE~~)
 *   {{SVG>page.svg}}
 *   {{SVG>namespace:page.svg}}
 * base64 encode inline svg directly
 *   <svg args...>...code...</svg>
 * 
 * @license    Copylefted
 * @author     Thomas Mudrunka <harvie--email-cz>
 */ 
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/'); 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/'); 
require_once(DOKU_PLUGIN.'syntax.php'); 
  
class syntax_plugin_svgedit extends DokuWiki_Syntax_Plugin { 

    var $helper = null;

    function getInfo() { 
            return array('author' => 'Thomas Mudrunka',
                         'email'  => 'harvie--email-cz',
                         'date'   => '2010-02-21',
                         'name'   => 'SVG-Edit Plugin',
                         'desc'   => 'Nice way, to create, store, edit and embed SVG images into DokuWiki',
                         'url'    => 'http://www.dokuwiki.org/plugin:svgedit'
                 );
    } 

    function getType() { return 'substition'; }
    function getSort() { return 303; }
    function getPType() { return 'block'; }

    function connectTo($mode) {  
        $this->Lexer->addSpecialPattern("{{svg>.+?}}", $mode, 'plugin_svgedit');  
        $this->Lexer->addSpecialPattern("{{SVG>.+?}}", $mode, 'plugin_svgedit');  
				$this->Lexer->addSpecialPattern("<svg.+?</svg>", $mode, 'plugin_svgedit');
    } 

    function handle($match, $state, $pos, &$handler) {
				$type = substr($match,0,4);
        return array($type, $match); 
    }

    function render($format, &$renderer, $data) {
				if ($format!='xhtml') return;
				global $ID;

				$svg_wiki_page = trim(substr($data[1], 6, -2)); //name of wiki page containing SVG image

				//detect image size for stupid browsers (like firefox) - ugly (fails if svg does not contain information about it's size)
				$svg_dimensions = '';
				preg_match('/width="[0-9]+" height="[0-9]+"/', $data[1].rawWiki($svg_wiki_page), $_);
				if(isset($_[0])) $svg_dimensions = $_[0];

				//use object tag for stupid browsers (like firefox) - ugly (relies on browser identification)
				$is_webkit= preg_match('/webkit/', strtolower($_SERVER['HTTP_USER_AGENT']));
				if ($is_webkit)
					$svgtag='<img src="';
				else
					$svgtag='<object '.$svg_dimensions.' data="';


				if($data[0]==='<svg') {
					$svgenc = 'data:image/svg+xml;base64,'.base64_encode($data[1]).'" type="image/svg+xml';
					$renderer->doc .= '<a href="'.$svgenc.'" type="image/svg+xml" />'.$svgtag.$svgenc.'" alt="svg-image@'.$ID.'" /></a>'."<br />";
					return true;
				}
				if($data[0]==='{{sv') {
					$svgenc = exportlink($svg_wiki_page,'svg');
					$renderer->doc .= '<a href="'.$svgenc.'" type="image/svg+xml" />'.$svgtag.$svgenc.'" alt="image:'.htmlspecialchars($svg_wiki_page).'" type="image/svg+xml"/></a><br />';
					$renderer->doc .= html_wikilink($svg_wiki_page,'svg@'.$svg_wiki_page);
        	return true;
				}
				if($data[0]==='{{SV') {
					$svgenc = 'data:image/svg+xml;base64,'.base64_encode(rawWiki($svg_wiki_page)).'" type="image/svg+xml';
					$renderer->doc .= '<a href="'.$svgenc.'" type="image/svg+xml" />'.$svgtag.$svgenc.'" alt="image:'.htmlspecialchars($svg_wiki_page).'" /></a><br />'; 
					$renderer->doc .= html_wikilink($svg_wiki_page,'SVG@'.$svg_wiki_page);
        	return true;
				}
    }
}
