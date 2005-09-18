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
  register_wiki_function('action_link', 'wiki_fn_action_link');
  register_wiki_function('is_eq', 'wiki_fn_is_eq');
  register_wiki_function('has_action', 'wiki_fn_has_action');
  register_wiki_function('is_action_permitted', 'wiki_fn_is_action_permitted');
  register_wiki_function('exists', 'wiki_fn_exists');

  # joins arguments and returns them
  function wiki_fn_echo($args, $renderer_state) {
    return join('', $args);
  }

  # set wiki variable specified by first argument to value specified by second argument
  # multiple variables may be specified
  function wiki_fn_set($args, $renderer_state) {
    while (count($args) > 0) {
      $name = array_shift($args);
      $value = array_shift($args);
      $renderer_state->wiki_variables->set($name, $value);
    }
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
    $inc_args_str = '';
    if (count($args) > 0) {
      $inc_args_str = '|' . join('|', $args);
    }
    $inc_page = new_page($renderer_state->renderer->db, $inc_page_name, MW_REVISION_HEAD);
    if ($inc_page->load()) {
      return '{{&push_vars}}{{&set|curpage|'.$inc_page_name .$inc_args_str .'}}'.str_replace("\r", '', $inc_page->raw_content).'{{&pop_vars}}';
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

  # returns URL for given action, revision (set to revision variable if omitted; head means HEAD rvision)
  # and page (set to page variable if omitted)
  function wiki_fn_action_link($args, $renderer_state) {
    $action = array_shift($args);
    $revision = array_shift($args);
    if ($revision === null) {
      $revision = $renderer_state->wiki_variables->get('revision');
    }
    $page_name = array_shift($args);
    if ($page_name === null) {
      $page_name = $renderer_state->wiki_variables->get('page');
    }
    if ($revision == 'head') {
      $revision = MW_REVISION_HEAD;
    }
    $page = new_page($renderer_state->renderer->db, $page_name, $revision);
    if ($action == MW_ACTION_RELOGIN) {
      return $page->url_for_action($action).'&'.MW_REQVAR_OLD_USER.'='.$renderer_state->wiki_variables->get('user');
    }
    return $page->url_for_action($action);
  }
  
  # returns non-empty string if two values are equal (as strings), empty otherwise
  function wiki_fn_is_eq($args, $renderer_state) {
    $val1 = array_shift($args);
    $val2 = array_shift($args);
    return ($val1 == $val2) ? 'true' : '';
  }

  # returns non-empty string if given action is available for revision (set to revision variable if omitted; head means HEAD revision)
  # and page (set to page variable if omitted) 
  function wiki_fn_has_action($args, $renderer_state) {
    $action = array_shift($args);
    $revision = array_shift($args);
    if ($revision === null) {
      $revision = $renderer_state->wiki_variables->get('revision');
    }
    $page_name = array_shift($args);
    if ($page_name === null) {
      $page_name = $renderer_state->wiki_variables->get('page');
    }
    if ($revision == 'head') {
      $revision = MW_REVISION_HEAD;
    }
    $page = new_page($renderer_state->renderer->db, $page_name, $revision);
    global $auth;
    return ($page->has_action($action) ? 'true' : '');
  }
  
  # returns non-empty string if current user has permission on given action
  # for revision (set to revision variable if omitted; head means HEAD revision)
  # and page (set to page variable if omitted) 
  function wiki_fn_is_action_permitted($args, $renderer_state) {
    $action = array_shift($args);
    $revision = array_shift($args);
    if ($revision === null) {
      $revision = $renderer_state->wiki_variables->get('revision');
    }
    $page_name = array_shift($args);
    if ($page_name === null) {
      $page_name = $renderer_state->wiki_variables->get('page');
    }
    if ($revision == 'head') {
      $revision = MW_REVISION_HEAD;
    }
    $page = new_page($renderer_state->renderer->db, $page_name, $revision);
    global $auth;
    return ($auth->is_action_permitted($action, $page) ? 'true' : '');
  }
  
  # returns non-empty string if given page exists
  function wiki_fn_exists($args, $renderer_state) {
    $page_name = array_shift($args);
    $page = new_page($renderer_state->renderer->db, $page_name, MW_REVISION_HEAD);
    return ($page->exists() ? 'true' : '');
  }
  
?>
