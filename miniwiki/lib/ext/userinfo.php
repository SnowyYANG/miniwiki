<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension User Info (bundled)
  */
  
  class MW_UserInfoExtension extends MW_Extension {

    function get_name() {
      return "User Info";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Wiki function user_info.";
    }

    function initialize() {
      register_wiki_function('user_info', array($this, 'wiki_fn_user_info'));
      return true;
    }

    /** returns user information */
    function wiki_fn_user_info() {
      $auth =& get_auth();
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
  }

  register_extension(new MW_UserInfoExtension());

?>
