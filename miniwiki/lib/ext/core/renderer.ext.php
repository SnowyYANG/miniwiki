<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Renderer (bundled)
  */

  class MW_CoreRendererExtension extends MW_Extension {

    function get_name() {
      return "Core Renderer";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Default Wiki renderer implementation.";
    }

    function initialize() {
      register_renderer(new MW_CoreRenderer());
      return true;
    }

  }

  register_extension(new MW_CoreRendererExtension());

  /** internal Wiki renderer state */
  class MW_CoreRendererState extends MW_RendererState {
    # [read-only] attributes
    var $headings;
    /** headings counter (current number) */
    var $headings_counter;
    
    /** @protected
    * constructor (do not call)
    * @param renderer MW_Renderer
    * @param page MW_Page or null
    * @param raw raw text to render
    * @param super_wiki_variables super MW_Variables to use
    * @param curpage MW_Page or null for the same value as page
    */
    function MW_CoreRendererState($renderer, $page, $raw, $super_wiki_variables, $curpage = null) {
      parent::MW_RendererState($renderer, $page, $raw, $super_wiki_variables, $curpage);
      $this->headings = array();
      $this->headings_counter = '';
    }

    /** @private
    * add heading into headings array
    * @param level heading level
    * @param title heading title
    * @param anchor heading anchor
    */
    function add_heading($level, $title, $anchor) {
      if ($level < 2) {
        # super-headings (level 1, single =) are ignored
        return;
      }
      $counter = (strlen($this->headings_counter) > 0) ? explode('.', $this->headings_counter) : array();
      $counter = array_pad($counter, $level - 1, 0);
      $counter[$level - 2]++;
      for ($i = $level - 1; $i < count($counter); $i++) {
        unset($counter[$i]);
      }
      $this->headings_counter = implode('.', $counter);
      $heading = array('title' => $title, 'level' => $level, 'anchor' => $anchor, 'number' => $this->headings_counter);
      array_push($this->headings, $heading);
    }

    /** @private
    * makes anchor name from normal name
    * replaces spaces with '_'
    * @param name name to make anchor name from
    */
    function make_anchor_name($name) {
      return str_replace(' ', '_', $name);
    }

    function escape_quotes($value) {
      $value = str_replace('"', '&quot;', $value);
      $value = str_replace("'", '&#039;', $value);
      return $value;
    }

    /** @private
    * returns HTML code for link to internal Wiki page
    * @param name page name
    * @param title link title
    */
    function process_internal_link($name, $title) {
      debug('MW_Page.process_internal_link(name='.$name.', title='.$title.')');
      $fragment = null;
      if (!(strpos($name, '#') === false)) {
        list($name, $fragment) = explode('#', $name, 2);
        $fragment = $this->make_anchor_name($fragment);
      }
      $revision = MW_REVISION_HEAD;
      if (!(strpos($name, '$') === false)) {
        list($name, $revision) = explode('$', $name, 2);
      }
      $is_image = false;
      if ($name == '') {
        # we must override current actions we are rendered with
        $page =& get_current_page();
        $linked_page = $page;
        $link_exists = true;
      } else {
        if (!(strpos($name, MW_LINK_NAME_PREFIX_IMAGE) === false)) {
          $is_image = true;
          $upload_name = MW_PAGE_NAME_PREFIX_UPLOAD . substr($name, strlen(MW_LINK_NAME_PREFIX_IMAGE));
          $data_name = MW_PAGE_NAME_PREFIX_DATA . substr($name, strlen(MW_LINK_NAME_PREFIX_IMAGE));
          $data_page = new_page($data_name, $revision);
          # small hack to change link on Upload page
          $page =& get_current_page();
          $name = (is_a($page, "MW_SpecialUploadPage") ? $data_name : $upload_name);
        }
        $linked_page = new_page($name, $revision);
        $link_exists = $linked_page->exists();
      }
      $link_action = ($link_exists) ? MW_ACTION_VIEW : MW_ACTION_EDIT;
      $link_type = ($link_exists) ? ($is_image ? 'image' : 'view-link') : 'edit-link';
      return '<a href="'.url_for_page_action($linked_page, $link_action, true, $fragment)
        .'" class="'.$this->escape_quotes($link_type).'">'
        .($is_image && $link_exists
          ? '<img src="'.url_for_page_action($data_page, $link_action, true).'"'
            .' alt="'.$this->escape_quotes($title).'"'
            .' longdesc="'.url_for_page_action($linked_page, $link_action, true).'"'
            .'/>'
          : $title)
        .'</a>';
    }

    /** @private callback for preg_replace_callback in process_inline() */
    function process_inline_cb($matches) {
      $type = $matches[0];
      if (preg_match("/^'''/", $type)) {
        return $this->process_inline_cb_strong($matches);
      }
      if (preg_match("/^''/", $type)) {
        return $this->process_inline_cb_em($matches);
      }
      if (preg_match("/^\[\[/", $type)) {
        return $this->process_inline_cb_internal_link($matches);
      }
      if (preg_match("/^\[/", $type)) {
        return $this->process_inline_cb_external_link($matches);
      }
      if (preg_match("/^&lt;form-field/", $type)) {
        return $this->process_inline_cb_form_field($matches);
      }
      if (preg_match("/^&lt;form/", $type)) {
        return $this->process_inline_cb_form($matches);
      }
      if (preg_match(",^&lt;/form,", $type)) {
        return $this->process_inline_cb_form_end($matches);
      }
      if (preg_match("/^&lt;box/", $type)) {
        return $this->process_inline_cb_box($matches);
      }
      if (preg_match(",^&lt;/box,", $type)) {
        return $this->process_inline_cb_box_end($matches);
      }
      if (preg_match("/^&lt;/", $type)) {
        return $this->process_inline_cb_br($matches);
      }
    }


    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_strong($matches) {
      $text = $matches[1];
      return '<strong>'.$text.'</strong>';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_em($matches) {
      $text = $matches[1];
      return '<em>'.$text.'</em>';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_internal_link($matches) {
      $name = $matches[1];
      $title = ((count($matches) > 2) ? $matches[2] : $name);
      return $this->process_internal_link($name, $title);
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_external_link($matches) {
      $url = $matches[1];
      $title = ((count($matches) > 2) ? $matches[2] : $url);
      return '<a href="'.$this->escape_quotes(resolve_url($url)).'">'.$title.'</a>';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_br($matches) {
      return '<br/>';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_form_end($matches) {
      return '</form>';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_form($matches) {
      $method = $matches[1];
      $action = $matches[2];
      return '<form method="'.$this->escape_quotes($method). '" action="'.$this->escape_quotes($action).'">';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_form_field($matches) {
      $name = $matches[1];
      $type = $matches[2];
      $value = ((count($matches) > 3) ? $matches[3] : '');
      $add_params = '';
      if (strpos($type, '|') !== false) {
        $params = explode('|', $type);
        $type = array_shift($params);
        foreach ($params as $param) {
          if (strpos($param, '=') !== false) {
            list($param_name, $param_value) = explode('=', $param);
          } else {
            $param_name = $param_value = $param;
          }
          $add_params .= ' '.$param_name.'="'.$this->escape_quotes($param_value).'"';
        }
      }
      if ($type == 'option') {
        $ret = '<select'.(($name != '#') ? ' name="'.$this->escape_quotes($name).'"' : '').$add_params.'>'."\n";
        $options = explode('|', $value);
        foreach ($options as $option) {
          $opt_value = '';
          $opt_text = '';
          $opt_selected = false;
          if (strpos($option, '~') === 0) {
            $opt_selected = true;
            $option = substr($option, 1);
          }
          if (strpos($option, ':') === false) {
            $opt_text = $option;
          } else {
            $opts = explode(':', $option, 2);
            $opt_value = $opts[0];
            $opt_text = $opts[1];
          }
          $ret .= '<option';
          if ($opt_value != '') {
            $ret .= ' value="'.$this->escape_quotes($opt_value).'"';
          }
          if ($opt_selected) {
            $ret .= ' selected="selected"';
          }
          if ($opt_text != '') {
            $ret .= '>'.$opt_text.'</option>';
          } else {
            $ret .= '/>';
          }
          $ret .= "\n";
        }
        $ret .= '</select>';
        return $ret;
      } else {
        return '<input type="'.$this->escape_quotes($type).'"'
          .(($name != '#') ? ' name="'.$this->escape_quotes($name).'"' : '')
          .(($value != '') ? ' value="'.$this->escape_quotes($value).'"' : '')
          .$add_params.'/>';
      }
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_box_end($matches) {
      return '</div>';
    }

    /** @private sub-callback for preg_replace_callback in process_inline() */
    function process_inline_cb_box($matches) {
      $name = $matches[1];
      if ($name[0] == '#') {
        $name = substr($name, 1);
        return '<div id="'.$this->escape_quotes($name). '">';
      }
      return '<div class="'.$this->escape_quotes($name). '">';
    }

    /** @private
    * returns HTML code for inline Wiki markup:
    *   '''BOLD''', ''ITALIC'', [[PAGE_NAME:LINK_TITLE]], [[PAGE_NAME]], [URL LINK_TITLE], [URL], <br>, forms, boxes
    * @param text text to process
    */
    function process_inline($text) {
      debug('MW_Page.process_inline(text='.$text. ')');
      $text = preg_replace_callback(
        array("/'''(.*?)'''/",
              "/''(.*?)''/",
              '/\[\[([^\]]*?)\|(.*?)\]\]/',
              '/\[\[([^\]]*?)\]\]/',
              '/\[([^\]]*?)\s+([^\]].*?)\]/',
              '/\[([^\]]*?)\]/',
              '/&lt;[Bb][Rr]&gt;/',
              '/&lt;form\s+(.+?)\s+(.+?)\s*&gt;/',
              '/&lt;form-field\s+(.+?)\s+(.+?)(?:\s+(.+?))?&gt;/',
              ',&lt;/form.*?&gt;,',
              '/&lt;box\s+(.+?)\s*&gt;/',
              ',&lt;/box.*?&gt;,',
              ),
        array(&$this, 'process_inline_cb'),
        $text);
      return $text;
    }
    
    /** @private callback for preg_replace_callback in process_heading_block() */
    function process_heading_block_cb($matches) {
      debug('MW_Page.process_heading_block_cb(matches='.join(', ', $matches).')');
      $h_level = strlen($matches[1]);
      $h_name = $matches[2];
      $h_anchor = $this->make_anchor_name($h_name);
      $this->add_heading($h_level, $h_name, $h_anchor);
      return '<h'.$h_level. '><a name="'.$this->escape_quotes($h_anchor).'">'.$this->process_inline($h_name).'</a></h'.$h_level.'>';
    }
    
    /** @private
    * returns HTML code for heading block (=H1= ... ======H6======)
    * heading title is inline processed
    * @param block block to process
    */
    function process_heading_block($block) {
      debug('MW_Page.process_heading_block(block='.$block.')');
      $block = preg_replace_callback(
        '/^(=+)\s*(.*?)\s*=+\s*$/',
        array(&$this, 'process_heading_block_cb'),
        $block);
      return $block."\n";
    }
    
    /** @private
    * returns HTML code for list item (* ... **********...)
    * item content is inline processed
    * @param item item to process
    * @param depth depth of previous item in the same list block or 0
    */
    function process_list_item($item, &$depth) {
      debug('MW_Page.process_list_item(item='.$item. ', depth='.$depth. ')');
      $ret = '';
      $i = 0;
      while (($i < strlen($item)) && ($item[$i] == '*')) {
        $i++;
      }
      debug('MW_Page.process_list_item: i='.$i);
      if ($i > $depth) {
        while ($i > $depth) {
          $ret .= "<ul>\n";
          $depth++;
          if ($i != $depth) {
            $ret .= '<li>';
          }
        }
      } elseif ($i < $depth) {
        while ($i < $depth) {
          $ret .= "</li>\n</ul>\n";
          $depth--;
          if ($i > 0) {
            $ret .= "</li>\n";
          }
        }
      } else {
        $ret .= "</li>\n";
      }
      if (strlen($item) > 0) {
        $ret .= "<li>".$this->process_inline(ltrim(substr($item, $i)));
      }
      return $ret;
    }
    
    /** @private
    * returns HTML code for list block (block starting with *)
    * list block is composed of list items
    * @param block block to process
    */
    function process_list_block($block) {
      debug('MW_Page.process_list_block(block='.$block. ')');
      $ret = '';
      $lines = explode("\n", $block);
      $cur_item = '';
      $cur_depth = 0;
      foreach ($lines as $line) {
        if ($line[0] == '*') {
          if (strlen($cur_item) > 0) {
            $ret .= $this->process_list_item($cur_item, $cur_depth);
          }
          $cur_item = $line;
        } else {
          $cur_item .= ' ' . $line;
        }
      }
      if (strlen($cur_item) > 0) {
        $ret .= $this->process_list_item($cur_item, $cur_depth);
      }
      $ret .= $this->process_list_item('', $cur_depth);
      return $ret;
    }
    
    /** @private
    * returns HTML for normal block (paragraph)
    * block content is inline processed
    * @param block block to process
    */
    function process_normal_block($block) {
      $processed = $this->process_inline($block);
      # prevent div inside paragraph
      if (!(strpos($processed, '<div') === 0)) {
        return "<p>".$processed. "</p>\n";
      }
      return $processed;
    }
    
    /** @private
    * returns HTML for given block
    * detects heading, list and normal blocks
    * @param block block to process
    */
    function process_block($block) {
      if ($block{0} == '=') {
        return $this->process_heading_block($block);
      } elseif ($block{0} == '*') {
        return $this->process_list_block($block);
      } elseif ($block == '---') {
        return "<hr/>\n";
      } else {
        return $this->process_normal_block($block);
      }
    }

    /** @private
    * returns HTML for given block chain
    * chain is composed of blocks separated by empty lines
    * @param chain chain to process
    */
    function process_block_chain($chain) {
      debug('MW_Page.process_block_chain(chain='.$chain. ')');
      $chain = htmlspecialchars($chain, ENT_NOQUOTES);
      $blocks = preg_split('/(^|\n+)[ \t]*(\n+|$)/', $chain, -1, PREG_SPLIT_NO_EMPTY);
      $ret = '';
      foreach ($blocks as $block) {
        $ret .= $this->process_block($block);
      }
      return $ret;
    }
    
    /** @private callback for preg_replace_callback in process_includes() */
    function process_includes_cb($matches) {
      debug('MW_Page.process_includes_cb(matches='.join(', ', $matches).')');
      $inc_command = $matches[1];
      if ($inc_command[0] == '$') {
        # {{$var}} -> {{&echo|$var}}
        $inc_command = '&echo|' . $inc_command;
      } elseif ($inc_command[0] != '&') {
        # {{page}} -> {{&include|page}}
        $inc_command = '&include|' . $inc_command;
      } elseif (strpos($inc_command, '|') === false) {
        # backwards compatible {{&func arg}} -> {{&func|arg}}
        $inc_command = preg_replace('/\s+/', '|', $inc_command, 1);
      }
      $inc_command = substr($inc_command, 1);
      $wiki_func_args = explode('|', $inc_command);
      $wiki_func = array_shift($wiki_func_args);
      $wiki_func_args = preg_replace('/\$(\S+)/e', '$this->wiki_variables->get("$1")', $wiki_func_args);
      $wiki_func_ret = call_wiki_function($wiki_func, $wiki_func_args, $this);
      if ($wiki_func_ret !== null) {
        return $wiki_func_ret;
      }
      return $matches[1];
    }
    
    /** @private
    * process includes {{...}}
    * this function only replaces all {{...}} with its contents (NOT processed/rendered)
    * @param line line to process
    */
    function process_includes($line) {
      debug('MW_Page.process_includes(line='.$line. ')');
      $line = preg_replace_callback(
        '/{{(.*?)}}/',
        array(&$this, 'process_includes_cb'),
        $line);
      return $line;
    }
    
    /** @private
    * process header link
    * header link may be either raw URL or [[page]]
    * @param link header link value
    */
    function process_header_link($link) {
      if (strpos($link, '[[') === 0) {
        # [[page]]
        $link = substr($link, 2, strlen($link) - 4);
        $linked_page = new_page($link, MW_REVISION_HEAD);
        $link = url_for_page_action($linked_page, MW_ACTION_VIEW);
      }
      return htmlspecialchars($link, ENT_QUOTES);
    }

    /**
    * render Wiki markup to output
    * raw text is split into blocks (separated by empty lines) and then rendered,
    * text between <pre> and </pre> (must begin lines) is not Wiki-processed (regardless of blocks)
    */
    function render() {
      if (strlen($this->raw) == 0) {
        echo _('(empty)'), "\n";
        return;
      }
      $src = str_replace("\r", '', $this->raw);
      $lines = explode("\n", $src);
      $in_pre = false;
      $current_chain = '';
      $notoc = false;
      $output = '';
      $if_skip_counter = 0;
      # the count() hack is because some our lines are empty which causes while(array_shift) to terminate prematurely
      while (count($lines) > 0) {
        $line = array_shift($lines);
        if ($if_skip_counter > 0) {
          if (strpos($line, '#ENDIF') === 0) {
            $if_skip_counter--;
          } elseif (strpos($line, '#IF') === 0) {
            $if_skip_counter++;
          }
          if ($if_skip_counter > 0) {
            continue;
          }
        }
        if (!$in_pre && preg_match('/^<pre>/i', $line)) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $in_pre = true;
          $line = substr($line, 5);
          $output .= '<pre>';
        }
        if ($in_pre) {
          if (preg_match(',^</pre>,i', $line)) {
            $in_pre = 0;
            $output .= "</pre>\n";
          } else {
            $output .= htmlspecialchars($line, ENT_NOQUOTES) . "\n";
          }
        } elseif (strpos($line, '#NOTOC') === 0) {
          $notoc = true;
        } elseif (strpos($line, '#IF') === 0) {
          # for #IFEMPTY
          $empty_cond = (strpos($line, 'EMPTY') === 3);
          $tokens = explode(' ', $line, 2);
          $value = $this->process_includes($tokens[1]);
          $is_empty = (strlen(trim($value)) == 0);
          if (!(($empty_cond && $is_empty) || (!$empty_cond && !$is_empty))) {
            $if_skip_counter++;
          }
        } elseif (strpos($line, '#ENDPAGE') === 0) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $output .= "\n</body>\n</html>\n";
        } elseif (strpos($line, '#PAGE') === 0) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $output .= '<?xml version="1.0" encoding="'.config('encoding'). '"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>'."\n";
        } elseif (strpos($line, '#ENDHEADER') === 0) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $output .= "</head>\n<body>\n";
        } elseif (strpos($line, '#HEADER') === 0) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $output .= '<head>
  <meta http-equiv="Content-Type" content="text/html; charset='.config('encoding'). '"/>
  <meta name="generator" content="'. MW_NAME. '/'. MW_VERSION. '"/>'."\n";
        } elseif (strpos($line, '#META') === 0) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $tokens = explode(' ', $line);
          $meta_name = $tokens[1];
          $meta_value = $this->process_includes($tokens[2]);
          if ($meta_name == 'title') {
            $output .= '  <title>'.$meta_value .'</title>'."\n";
          } elseif ($meta_name == 'stylesheet') {
            $meta_value = $this->process_header_link($meta_value);
            $output .= '  <link rel="stylesheet" type="text/css" href="'.$meta_value. '"/>'."\n";
          } elseif ($meta_name == 'javascript') {
            $meta_value = $this->process_header_link($meta_value);
            $output .= '  <script type="text/javascript" src="'.$meta_value .'"></script>'."\n";
          }
        } elseif (strpos($line, '#REDIRECT') === 0) {
          $tokens = explode(' ', $line, 2);
          if (count($tokens) > 1) {
            $output .= '<div class="redirect-link">'.$this->process_internal_link($tokens[1], $tokens[1]).'</div>'."\n";
          }
        } elseif (strpos($line, '#FOREACH') === 0) {
          $tokens = explode(' ', $line, 3);
          if (count($tokens) > 2) {
            $array_var = $tokens[1];
            $index_var = $tokens[2];
            $array_value = $this->wiki_variables->get($array_var);
            if (!is_array($array_value)) {
              $array_value = array();
            }
            $for_counter = 1;
            $for_lines = array();
            while (count($lines) > 0) {
              $for_line = array_shift($lines);
              if (strpos($for_line, '#FOREACH') === 0) {
                $for_counter++;
              } elseif (strpos($for_line, '#ENDFOR') === 0) {
                $for_counter--;
              }
              if ($for_counter === 0) {
                break;
              }
              array_push($for_lines, $for_line);
            }
            if (count($for_lines) > 0) {
              $all_for_lines = array();
              foreach ($array_value as $item) {
                $new_for_lines = $for_lines;
                $new_for_lines[0] = '{{&set|'.$index_var.'|'.$item.'}}'.$new_for_lines[0];
                $all_for_lines = array_merge($all_for_lines, $new_for_lines);
              }
              $lines = array_merge($all_for_lines, $lines);
            }
          }
        } elseif (strpos($line, '#') === 0) {
          # omit directives
        } elseif (!(strpos($line, '{{') === false)) {
          /** @todo endless loop with multi-line includes */
          $line = $this->process_includes($line);
          $lines = array_merge(explode("\n", $line), $lines);
        } else {
          $current_chain .= $line . "\n";
        }
      }
      $output .= $this->process_block_chain($current_chain);
      # TOC
      if (!$notoc && count($this->headings)) {
        echo '<div class="toc">', "\n";
        echo '<ul>', "\n";
        foreach ($this->headings as $heading) {
          # we must override current actions we are rendered with
          $page =& get_current_page();
          echo '<li class="toc-level-', $heading["level"] - 1, '"><a href="'
            .url_for_page_action($page, MW_ACTION_VIEW, true, $heading['anchor'])
            , '">', $heading['number'], ' ', $heading['title'], '</a></li>', "\n";
        }
        echo '</ul>', "\n";
        echo '</div>', "\n";
      }
      echo $output;
    }
    
  }

  /** Wiki renderer */
  class MW_CoreRenderer extends MW_Renderer {
    
    /**
    * render Wiki markup to output
    * raw text is split into blocks (separated by empty lines) and then rendered,
    * text between <pre> and </pre> (must begin lines) is not Wiki-processed (regardless of blocks)
    * @param page MW_Page (may be null)
    * @param raw raw text (empty message is output if raw text is empty)
    * @param vars (optional): MW_Variables to be used as global variables
    * @param curpage (optional): MW_Page (may be null)
    */
    function render($page, $raw, $vars = null, $curpage = null) {
      if ($vars === null) {
        $vars = new_global_wiki_variables();
      }
      $state = new MW_CoreRendererState($this, $page, $raw, $vars, $curpage);
      $state->render();
    }
    
  }

?>
