/* $Id$
 * (c)2005 Stepan Roh <src@srnet.cz>
 * Free to copy, free to modify, NO WARRANTY
 */

/*
 * adds given text to textarea to current cursor position
 * if text contains % cursor will be positioned there after add
 * id: textarea ID
 * text: text to add
 */
function add_to_textarea(id, text) {
  textarea = document.getElementById(id);

  // enhanced code from http://www.alexking.org/blog/2003/06/02/inserting-at-the-cursor-using-javascript/ and MediaWiki
  if (document.selection) {
    // IE
    textarea.focus();
    sel = document.selection.createRange();
    sel.text = text;
  } else if (textarea.selectionStart || textarea.selectionStart == 0) {
    // Mozilla
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var scroll = textarea.scrollTop;
    i = text.indexOf('%');
    if (i > -1) {
      text = text.substring(0, i) + text.substring(i + 1);
    }
    textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(end);
    textarea.focus();
    if (i > -1) {
      textarea.selectionStart = start + i;
      textarea.selectionEnd = end + i;
    } else {
      textarea.selectionStart = start + text.length;
      textarea.selectionEnd = end + text.length;
    }
    textarea.scrollTop = scroll;
  } else {
    // others
    textarea.value += text;
  }
}
