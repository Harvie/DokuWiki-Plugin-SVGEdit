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
                         'date'   => '2010-06-20',
                         'name'   => 'SVG-Edit Plugin',
                         'desc'   => 'Nice way, to create, store, edit and embed SVG images into DokuWiki',
                         'url'    => 'http://www.dokuwiki.org/plugin:svgedit'
                 );
    } 

    function getType() { return 'substition'; }
    function getSort() { return 303; }
    function getPType() { return 'block'; }

    function connectTo($mode) {  
        $this->Lexer->addSpecialPattern("{{ ?svg>.+?}}", $mode, 'plugin_svgedit');
        $this->Lexer->addSpecialPattern("{{ ?SVG>.+?}}", $mode, 'plugin_svgedit');
				$this->Lexer->addSpecialPattern("<svg.+?</svg>", $mode, 'plugin_svgedit');
    } 

    function handle($match, $state, $pos, Doku_Handler $handler) {
				$type = substr($match,0,4);
        return array($type, $match); 
    }

		function svg_base64_encode($svg) { //create base64 encoded svg for use as svglink in svg_format_embed
			return 'data:image/svg+xml;base64,'.base64_encode($svg).'" type="image/svg+xml';
		}

		function svg_format_embed($svglink, $title, $svg_parameters, $align='') { //create xhtml code for svg embeding
				global $ID;

				//use object tag for stupid browsers (like firefox) - ugly (relies on browser identification)
				$is_webkit= preg_match('/webkit/', strtolower($_SERVER['HTTP_USER_AGENT']));
				if ($is_webkit) $svgtag='img src';
				else $svgtag='object '.$svg_parameters.' data';
				$svgtag_close = array_shift(preg_split('/ /', $svgtag, 2));

				return '<a href="'.$svglink.'" type="image/svg+xml" /><'.$svgtag.'="'.$svglink.'" class="media'.$align.'" alt="'.$title.'" title="'.$title.'" type="image/svg+xml">'."</$svgtag_close></a>";
		}

    function render($format, Doku_Renderer $renderer, $data) {
				if ($format!='xhtml') return;
				global $ID;

				$svg_wiki_page = trim(substr($data[1], 6, -2)); //name of wiki page containing SVG image
				resolve_pageid(getNS($ID),$svg_wiki_page,$exists); //resolve relative IDs

				//detect image size for stupid browsers (like firefox) - ugly (fails if svg does not contain information about it's size)
				$svg_dimensions = '';
				preg_match('/width="[0-9]+" height="[0-9]+"/', $data[1].rawWiki($svg_wiki_page), $_);
				if(isset($_[0])) $svg_dimensions = $_[0];

				// Check alignment
				$ralign = (bool)preg_match('/^\{\{ /',$data[1]);
				$lalign = (bool)preg_match('/ \}\}$/',$data[1]);

				switch(true) {
					case $lalign & $ralign: $align='center'; break;
					case $ralign: $align='right'; break;
					case $lalign: $align='left'; break;
					default: $align='';
				}

				if($data[0]==='<svg') {
					$svgenc = $this->svg_base64_encode($data[1]);
					$renderer->doc .= $this->svg_format_embed($svgenc, 'inline-svg@'.$ID, $svg_dimensions);
					return true;
				}
				if($data[0]==='{{sv' || $data[0]==='{{ s') {
					$svglink = exportlink($svg_wiki_page,'svg');
					$renderer->doc .= $this->svg_format_embed($svglink, 'image:'.htmlspecialchars($svg_wiki_page), $svg_dimensions, $align);
					$renderer->doc .= '<br /><small>'.html_wikilink($svg_wiki_page,'svg@'.$svg_wiki_page).'</small>';
        	return true;
				}
				if($data[0]==='{{SV' || $data[0]==='{{ S') {
					$svgenc = $this->svg_base64_encode(rawWiki($svg_wiki_page));
					$renderer->doc .= $this->svg_format_embed($svgenc, 'image:'.htmlspecialchars($svg_wiki_page), $svg_dimensions, $align);
					$renderer->doc .= '<br /><small>'.html_wikilink($svg_wiki_page,'SVG@'.$svg_wiki_page).'</small>';
        	return true;
				}
    }
}
