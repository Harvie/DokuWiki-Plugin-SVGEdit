var svgeditor_path = 'https://cdn.rawgit.com/SVG-Edit/svgedit/stable/editor/';	//online stable
//var svgeditor_path = 'https://raw.githubusercontent.com/SVG-Edit/svgedit/master/editor/';	//online latest (unstable)
//var svgeditor_path = DOKU_BASE+'lib/plugins/svgedit/svg-edit/';		//offline

//load embedapi.js
var head = document.getElementsByTagName("head")[0];
script = document.createElement('script');
script.type = 'text/javascript';
script.src = svgeditor_path + 'embedapi.js';
head.appendChild(script);

function svgedit_load() {
	var field = jQuery('#wiki__text');
	if (!field) return;
	field = field[0];
	var timeout = setTimeout('svgedit_load();', 500);	//load ASAP
	window.svgedit.setSvgString(field.value) (function(a) {
						  clearTimeout(timeout);
						  }
	);
}
function svgedit_save(page) {
	window.svgedit.getSvgString()(function(data) {
				      var field = jQuery('#wiki__text');
				      if (!field) return;
				      field = field[0];
				      field.value = data; 
				      if (page) {
				      	field = jQuery('#edbtn__save'); 
				      	field.click();
				      }
	}) ;
}

function showhide(elem) {
	elem.style.display = (elem.style.display == 'none' ? '' : 'none');
}

function insertAfter(newNode, preNode) {
	if (preNode.nextSibling)
		preNode.parentNode.insertBefore(newNode, preNode.nextSibling);
	else
		preNode.parentNode(newNode);
}

var svgedit = null;

function svgedit_init() {
	var field = jQuery('#wiki__text');
	if (!field) return;
	field = field[0];

	//toggle view
	showhide(field);
	showhide(jQuery('#tool__bar')[0]);
	showhide(jQuery('#edbtn__save')[0]);

	//lock
	if (jQuery('#svg__edit').length) return;

	//create iframe

	var el = document.createElement('iframe');
	el.setAttribute("src", svgeditor_path + 'svg-editor.html');
	el.setAttribute("id", "svg__edit");
	el.setAttribute("name", "svg__edit");
	el.setAttribute("frameborder", "0");
	el.setAttribute("width", "100%");
	el.setAttribute("height", "70%");
	el.setAttribute("style", "min-height: 600px;");
	insertAfter(el, field);

	//create save button
	field = jQuery('#edbtn__save');
	if (!field) return;
	field = field[0];

	el = document.createElement('input');
	el.setAttribute("type", "button");
	el.setAttribute("onclick", "svgedit_save(true)");
	el.setAttribute("value", "SVG-SAVE");
	el.setAttribute("title", "Save SVG to server");
	el.setAttribute("class", "button");
	field.parentNode.insertBefore(el, field);

	el = document.createElement('input');
	el.setAttribute("type", "button");
	el.setAttribute("onclick", "svgedit_load()");
	el.setAttribute("value", "TXT->SVG");
	el.setAttribute("title", "Copy SVG from textarea to svg-editor");
	el.setAttribute("class", "button");
	field.parentNode.insertBefore(el, field);

	el = document.createElement('input');
	el.setAttribute("type", "button");
	el.setAttribute("onclick", "svgedit_save()");
	el.setAttribute("value", "SVG->TXT");
	el.setAttribute("title", "Copy SVG from svg-editor to textarea");
	el.setAttribute("class", "button");
	field.parentNode.insertBefore(el, field);

	//create embedapi
	window.svgedit = new EmbeddedSVGEdit(jQuery('#svg__edit')[0]);

	//load image
	svgedit_load();
};


jQuery(function() {
	     if (!jQuery('#wiki__text').length || jQuery('#wiki__text').attr("readOnly")) return;
	     var field = jQuery('#tool__bar');
	     if (!field.length) return;
	     field = field[0];
	     field.style.float = 'left';
	     var el = document.createElement('button');
	     el.setAttribute("id", "TZT");
	     el.setAttribute("class", "toolbutton");
	     el.setAttribute("onclick", "svgedit_init();");
	     el.setAttribute("title", "Edit this page as SVG!");
	     el.setAttribute("style", "float: left;");
	     field.parentNode.insertBefore(el, field);
	     el.appendChild(document.createTextNode("SVG"));
	     var el = document.createElement('br');
	     el.setAttribute('style', "clear: left;");
	     field.appendChild(el);}) ;
