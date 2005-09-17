<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # functions accessible from Wiki pages by using {{&...}} syntax

  register_wiki_function('echo', 'wiki_fn_echo');
  register_wiki_function('set', 'wiki_fn_set');
  register_wiki_function('call', 'wiki_fn_call');
  register_wiki_function('include', 'wiki_fn_include');
  register_wiki_function('push_vars', 'wiki_fn_push_vars');
  register_wiki_function('pop_vars', 'wiki_fn_pop_vars');
  register_wiki_function('user_info', 'wiki_fn_user_info');

  # joins arguments and returns them
  function wiki_fn_echo($args, $renderer_state) {
    return join('', $args);
  }

  # set wiki variable specified by first argument to value specified by second argument
  function wiki_fn_set($args, $renderer_state) {
    $name = array_shift($args);
    $value = array_shift($args);
    $renderer_state->wiki_variables->set($name, $value);
    return '';
  }

  # set wiki variable specified by first argument to result of function call specified in rest of the arguments
  function wiki_fn_call($args, $renderer_state) {
    $name = array_shift($args);
    $call_func = array_shift($args);
    $call_args = $args;
    $value = call_wiki_function($call_func, $call_args, $renderer_state);
    $renderer_state->wiki_variables->set($name, $value);
    return '';
  }

  # returns raw content of page specified by first argument
  function wiki_fn_include($args, $renderer_state) {
    $inc_page_name = array_shift($args);
    $inc_page = new_page($renderer_state->renderer->db, $inc_page_name, MW_REVISION_HEAD);
    if ($inc_page->load()) {
      return '{{&push_vars}}{{&set|curpage|'.$inc_page_name .'}}'.str_replace("\r", '', $inc_page->raw_content).'{{&pop_vars}}';
    }
    return '[['.$inc_page_name .']]';
  }

  # push new wiki variables on the stack
  function wiki_fn_push_vars($args, $renderer_state) {
    $renderer_state->push_variables();
    return '';
  }

  # remove last pushed wiki variables from the stack
  function wiki_fn_pop_vars($args, $renderer_state) {
    $renderer_state->pop_variables();
    return '';
  }

  # returns user information
  function wiki_fn_user_info() {
    global $auth;
    if ($auth->is_logged) {
      $f = get_user_info_file($auth->user);
      if (!file_exists($f)) {
        return null;
      }
      $user_info = file_get_contents($f);
      if ($user_info === false) {
        return null;
      }
      return $user_info;
    }
    return null;
  }

?>
