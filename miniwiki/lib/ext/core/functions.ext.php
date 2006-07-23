<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Functions (bundled)
  */

  class MW_CoreFunctionsExtension extends MW_Extension {

    var $start_time;

    function MW_CoreFunctionsExtension() {
      $this->start_time = microtime_float();
    }

    function get_name() {
      return "Core Functions";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Core Wiki functions.";
    }

    function initialize() {
      register_wiki_function('echo', array($this, 'wiki_fn_echo'));
      register_wiki_function('set', array($this, 'wiki_fn_set'));
      register_wiki_function('call', array($this, 'wiki_fn_call'));
      register_wiki_function('include', array($this, 'wiki_fn_include'));
      register_wiki_function('push_vars', array($this, 'wiki_fn_push_vars'));
      register_wiki_function('pop_vars', array($this, 'wiki_fn_pop_vars'));
      register_wiki_function('action_link', array($this, 'wiki_fn_action_link'));
      register_wiki_function('req_param', array($this, 'wiki_fn_req_param'));
      register_wiki_function('is_eq', array($this, 'wiki_fn_is_eq'));
      register_wiki_function('has_action', array($this, 'wiki_fn_has_action'));
      register_wiki_function('is_action_permitted', array($this, 'wiki_fn_is_action_permitted'));
      register_wiki_function('exists', array($this, 'wiki_fn_exists'));
      register_wiki_function('process_time', array($this, 'wiki_fn_process_time'));
      register_wiki_function('include_layout', array($this, 'wiki_fn_include_layout'));
      register_wiki_function('noredir_link', array($this, 'wiki_fn_noredir_link'));
      register_wiki_function('item', array($this, 'wiki_fn_item'));
      register_wiki_function('set_item', array($this, 'wiki_fn_set_item'));
      register_wiki_function('import', array($this, 'wiki_fn_import'));
      register_wiki_function('list_pages', array($this, 'wiki_fn_list_pages'));
      register_wiki_function('special_pages', array($this, 'wiki_fn_special_pages'));
      register_wiki_function('list_users', array($this, 'wiki_fn_list_users'));
      register_wiki_function('list_uploads', array($this, 'wiki_fn_list_uploads'));
      register_wiki_function('is_special', array($this, 'wiki_fn_is_special'));
      register_wiki_function('list_page_namespaces', array($this, 'wiki_fn_list_page_namespaces'));
      register_wiki_function('list_upload_namespaces', array($this, 'wiki_fn_list_upload_namespaces'));
      register_wiki_function('page_attr', array($this, 'wiki_fn_page_attr'));
      register_wiki_function('list_extensions', array($this, 'wiki_fn_list_extensions'));
      register_wiki_function('mw_version', array($this, 'wiki_fn_mw_version'));
      register_wiki_function('php_version', array($this, 'wiki_fn_php_version'));
      return true;
    }

    /** joins arguments and returns them */
    function wiki_fn_echo($args, $renderer_state) {
      return join('', $args);
    }

    /**
    * set wiki variable specified by first argument to value specified by second argument
    * multiple variables may be specified
    */
    function wiki_fn_set($args, $renderer_state) {
      while (count($args) > 0) {
        $name = array_shift($args);
        $value = array_shift($args);
        $renderer_state->wiki_variables->set($name, $value);
      }
      return '';
    }

    /** set wiki variable specified by first argument to result of function call specified in rest of the arguments */
    function wiki_fn_call($args, $renderer_state) {
      $name = array_shift($args);
      $call_func = array_shift($args);
      $call_args = $args;
      $value = call_wiki_function($call_func, $call_args, $renderer_state);
      $renderer_state->wiki_variables->set($name, $value);
      return '';
    }

    /** returns raw content of page specified by first argument */
    function wiki_fn_include($args, $renderer_state) {
      $inc_page_name = array_shift($args);
      $inc_page = new_page($inc_page_name, MW_REVISION_HEAD);
      if ($inc_page->load()) {
        return wiki_include($inc_page, $args, true);
      }
      return '[['.$inc_page_name .']]';
    }

    /** returns raw content of layout page specified by first argument */
    function wiki_fn_include_layout($args, $renderer_state) {
      $inc_page_name = array_shift($args);
      $inc_page = load_layout_page($inc_page_name);
      if ($inc_page !== null) {
        return wiki_include($inc_page, $args, true);
      }
      return '[['.$inc_page_name .']]';
    }

    /** push new wiki variables on the stack */
    function wiki_fn_push_vars($args, $renderer_state) {
      $renderer_state->push_variables();
      return '';
    }

    /** remove last pushed wiki variables from the stack */
    function wiki_fn_pop_vars($args, $renderer_state) {
      $renderer_state->pop_variables();
      return '';
    }

    /**
    * returns URL for given action, revision (set to revision variable if omitted; head means HEAD revision)
    * and page (set to page variable if omitted)
    */
    function wiki_fn_action_link($args, $renderer_state) {
      $action_name = array_shift($args);
      $revision = array_shift($args);
      if ($revision === null) {
        $revision = $renderer_state->wiki_variables->get('revision');
      }
      $page_name = array_shift($args);
      if ($page_name === null) {
        $page_name = $renderer_state->wiki_variables->get('page');
      }
      $fragment = array_shift($args);
      if ($revision == 'head') {
        $revision = MW_REVISION_HEAD;
      }
      $page = new_page($page_name, $revision);
      $link = link_for_page_action($page, $action_name);
      $link->set_page($page);
      if (!empty($fragment)) {
        $link->set_fragment($fragment);
      }
      while (count($args) > 0) {
        $name = array_shift($args);
        $value = array_shift($args);
        $link->set_param($name, $value);
      }
      return $link->to_url(false);
    }
  
    function wiki_fn_req_param($args, $renderer_state) {
      $req_param = array_shift($args);
      $default = array_shift($args);
      $req =& get_request('MW_RawRequest');
      return $req->get_raw_param($req_param, $default);
    }
  
    /** returns non-empty string if two values are equal (as strings), empty otherwise */
    function wiki_fn_is_eq($args, $renderer_state) {
      $val1 = array_shift($args);
      $val2 = array_shift($args);
      return ($val1 == $val2) ? 'true' : '';
    }

    /**
    * returns non-empty string if given action is available for revision (set to revision variable if omitted; head means HEAD revision)
    * and page (set to page variable if omitted)
    */
    function wiki_fn_has_action($args, $renderer_state) {
      $action_name = array_shift($args);
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
      $page = new_page($page_name, $revision);
      $action = get_action($action_name);
      return (($action !== null) && $page->has_action($action) ? 'true' : '');
    }
  
    /**
    * returns non-empty string if current user has permission on given action
    * for revision (set to revision variable if omitted; head means HEAD revision)
    * and page (set to page variable if omitted)
    */
    function wiki_fn_is_action_permitted($args, $renderer_state) {
      $action_name = array_shift($args);
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
      $page = new_page($page_name, $revision);
      $auth =& get_auth();
      $action = get_action($action_name);
      return (($action !== null) && $auth->is_action_permitted($action, $page) ? 'true' : '');
    }
  
    /** returns non-empty string if given page exists */
    function wiki_fn_exists($args, $renderer_state) {
      $page_name = array_shift($args);
      $page = new_page($page_name, MW_REVISION_HEAD);
      return ($page->exists() ? 'true' : '');
    }

    function wiki_fn_process_time($args, $renderer_state) {
      return microtime_float() - $this->start_time;
    }
    
    function wiki_fn_noredir_link($args, $renderer_state) {
      $page_name = array_shift($args);
      $page = new_page($page_name, MW_REVISION_HEAD);
      $link = link_for_page_action($page, MW_ACTION_VIEW);
      $link->set_redirect(false);
      return $link->to_url();
    }
  
    function wiki_fn_item($args, $renderer_state) {
      $name = array_shift($args);
      $index = array_shift($args);
      $var = $renderer_state->wiki_variables->get($name);
      if (is_array($var)) {
        return $var[$index];
      }
      return '';
    }
  
    function wiki_fn_set_item($args, $renderer_state) {
      $name = array_shift($args);
      $index = array_shift($args);
      $value = array_shift($args);
      $var = $renderer_state->wiki_variables->get($name);
      if ($var === null) {
        $var = array();
      }
      if (is_array($var)) {
        $var[$index] = $value;
        # copy of array is used - we must set it back
        $renderer_state->wiki_variables->set($name, $var);
      }
      return '';
    }
  
    function wiki_fn_import($args, $renderer_state) {
      $name = array_shift($args);
      $var = $renderer_state->wiki_variables->get($name);
      if (is_array($var)) {
        foreach ($var as $key => $value) {
          $renderer_state->wiki_variables->set($key, $value);
        }
      }
      return '';
    }

    function wiki_fn_list_pages($args, $renderer_state) {
      $namespace = array_shift($args);
      $storage =& get_storage();
      return $storage->get_resource_names(MW_DS_PAGES, $namespace);
    }

    function wiki_fn_special_pages($args, $renderer_state) {
      $namespace = array_shift($args);
      $storage =& get_storage();
      $special_pages = $storage->get_resource_names(MW_DS_PAGES, MW_PAGE_NAME_PREFIX_MINIWIKI . MW_PAGE_NAME_PREFIX_SPECIAL);
      for ($i = 0; $i < count($special_pages); $i++) {
        $special_pages[$i] = substr($special_pages[$i], strlen(MW_PAGE_NAME_PREFIX_MINIWIKI));
      }
      $coded_special_pages = get_special_pages();
      $special_pages = array_merge($special_pages, $coded_special_pages);
      $special_pages = array_unique($special_pages);
      sort($special_pages);
      return $special_pages;
    }

    function wiki_fn_list_page_namespaces($args, $renderer_state) {
      $namespace = array_shift($args);
      $storage =& get_storage();
      return $storage->get_namespaces(MW_DS_PAGES, $namespace);
    }

    function wiki_fn_list_uploads($args, $renderer_state) {
      $namespace = array_shift($args);
      $storage =& get_storage();
      return $storage->get_resource_names(MW_DS_UPLOADS, $namespace);
    }

    function wiki_fn_list_upload_namespaces($args, $renderer_state) {
      $namespace = array_shift($args);
      $storage =& get_storage();
      return $storage->get_namespaces(MW_DS_UPLOADS, $namespace);
    }

    function wiki_fn_list_users($args, $renderer_state) {
      $users_mgr =& get_users_manager();
      return $users_mgr->get_all_usernames();
    }

    function wiki_fn_is_special($args, $renderer_state) {
      $page_name = array_shift($args);
      return (strpos($page_name, MW_PAGE_NAME_PREFIX_SPECIAL) === 0 ? 'true' : '');
    }
  
    function wiki_fn_page_attr($args, $renderer_state) {
      $page_name = array_shift($args);
      $attr_name = array_shift($args);
      $page = new_page($page_name, MW_REVISION_HEAD);
      $page->load();
      return $page->get_attr($attr_name);
    }
  
    function wiki_fn_list_extensions($args, $renderer_state) {
      $ret = array();
      $exts = get_extensions();
      foreach ($exts as $ext) {
        /** @todo multiline bug strikes here */
#        $ret[] = '===='.$ext->get_name()."====\n\nVersion: ".$ext->get_version()."\n\n".$ext->get_description();
        $ret[] = '*'.$ext->get_name()."<br><br>Version: ".$ext->get_version()."<br><br>".$ext->get_description();
      }
      sort($ret);
      return $ret;
    }

    function wiki_fn_mw_version($args, $renderer_state) {
      return MW_VERSION;
    }
  
    function wiki_fn_php_version($args, $renderer_state) {
      return phpversion();
    }
  
  }

  register_extension(new MW_CoreFunctionsExtension());

?>
