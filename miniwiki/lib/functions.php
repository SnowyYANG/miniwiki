<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # functions accessible from Wiki pages by using {{&...}} syntax

  register_wiki_function('user_info', 'wiki_fn_user_info');

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
