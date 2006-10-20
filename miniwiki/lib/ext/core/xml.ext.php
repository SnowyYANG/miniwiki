<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core XML Import/Export (bundled)
  */

  class MW_CoreXMLImportExportExtension extends MW_Extension {

    function get_name() {
      return "Core XML Import/Export";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "XML import/export.";
    }

    function initialize() {
      register_importer(new MW_XMLImporter());
      register_exporter(new MW_XMLExporter());
      return true;
    }

  }

  register_extension(new MW_CoreXMLImportExportExtension());

  define('MW_XML_FORMAT', 'XML');
  define('MW_XML_TYPE_TEXT', 'text');
  define('MW_XML_TYPE_BINARY', 'binary');
  define('MW_XML_TYPE_DATETIME', 'datetime');
  define('MW_XML_EXTENSION', 'xml');

  /*
    XML format (if miniWiki's encoding is not UTF-8 and iconv() is not available, internal encoding is used):

    <?xml version="1.0" encoding="utf-8"?>
    <resources>
      <resource dataspace="DATASPACE_NAME" name="RESOURCE_NAME">
        <key name="RESOURCE_KEY_NAME" type="RESOURCE_KEY_TYPE">RESOURCE_KEY_VALUE</key>
        ...
      </resource>
      ...
    </resources>

    RESOURCE_KEY_TYPE is one of:
      text - RESOURCE_KEY_VALUE is plain text
      binary - RESOURCE_KEY_VALUE is encoded with base64
      datetime - RESOURCE_KEY_VALUE is YYYY-MM-DDTHH:MM:SSZ (corresponds to XSD dateTime with UTC timezone)

    Resources with the same name must be grouped (no other resources between them) and newest resource must be last.
  */
        
  function explode_dataspace_name($name, &$ds_name, &$res_name) {
    $i = strpos($name, ':');
    if ($i !== false) {
      $ds_name = substr($name, 0, $i);
      $res_name = substr($name, $i + 1);
    } else {
      $ds_name = $name;
      $res_name = null;
    }
  }

  class MW_XMLImporter extends MW_Importer {

    function get_format() {
      return MW_XML_FORMAT;
    }

    /** @private */
    var $to_enc;
    /** @private */
    var $dataspaces;
    /** @private */
    var $with_history;
    /** @private */
    var $force_import;
    /** @private */
    var $cur_resource;
    /** @private */
    var $prev_resource;
    /** @private */
    var $prev_resource_name;
    /** @private */
    var $cur_dataspace;
    /** @private */
    var $cur_key;
    /** @private */
    var $cur_cdata;

    /** @private */
    function decode_str($str) {
      if (empty($str)) {
        return null;
      }
      if ($this->to_enc !== null) {
        return iconv('utf-8', $this->to_enc, $str);
      }
      return $str;
    }

    /** @private */
    function startElement($parser, $name, $attrs) {
      if ($name === 'RESOURCE') {
        $resource_name = $this->decode_str($attrs['NAME']);
        if (empty($resource_name)) {
          return;
        }
        if ($this->with_history || (($this->prev_resource !== null) && ($resource_name !== $this->prev_resource_name))) {
          $this->flush_prev_resource();
        }
        $this->cur_dataspace = $this->decode_str($attrs['DATASPACE']);
        $included = false;
        foreach ($this->dataspaces as $ds) {
          explode_dataspace_name($ds, $ds, $wanted_res);
          if ($this->cur_dataspace === $ds) {
            if (($wanted_res === null) || (strpos($resource_name, $wanted_res) === 0)) {
              $included = true;
              break;
            }
          }
        }
        if (!$included) {
          return;
        }
        $this->cur_resource = new MW_Resource();
        $this->cur_resource->set(MW_RESOURCE_KEY_NAME, $resource_name);
        $this->cur_key = null;
      } elseif ($name === 'KEY') {
        $this->cur_key = $this->decode_str($attrs['NAME']);
      }
      $this->cur_cdata = null;
    }

    /** @private */
    function endElement($parser, $name) {
      if ($this->cur_resource === null) {
        return;
      }
      if ($name === 'RESOURCE') {
        $storage =& get_storage();
        $this->prev_resource = $this->cur_resource;
        $this->prev_resource_name = $this->cur_resource->get(MW_RESOURCE_KEY_NAME);
        $this->cur_resource = null;
      } elseif ($name === 'KEY') {
        if ($this->cur_cdata !== null) {
          $data = $this->decode_str($this->cur_cdata);
          /** @todo follow import() which uses types_map */
          $storage =& get_storage();
          $ds_def = $storage->get_dataspace_definition($this->cur_dataspace);
          if (($this->cur_key === MW_RESOURCE_KEY_CONTENT) && ($ds_def->get_content_type() == MW_RESOURCE_CONTENT_TYPE_BINARY)) {
            echo $data, "<br>\n";
            $data = base64_decode($data);
            echo strlen($data), "<br>\n";
          } elseif ($this->cur_key === MW_RESOURCE_KEY_LAST_MODIFIED) {
            # is ignored by storage anyway
            return;
          } elseif ($this->cur_key === MW_RESOURCE_KEY_NAME) {
            # already there
            return;
          }
          $prev = $this->cur_resource->get($this->cur_key);
          $this->cur_resource->set($this->cur_key, $prev . $data);
        }
        $this->cur_key = null;
      }
    }

    /** @private */
    function flush_prev_resource() {
      if ($this->prev_resource === null) {
        return;
      }
      $storage =& get_storage();
      $resource_name = $this->prev_resource->get(MW_RESOURCE_KEY_NAME);
      if (!$this->force_import && $storage->exists($this->cur_dataspace, $resource_name)) {
        $resource_name .= MW_IMPORTED_RESOURCE_NAME_POSTFIX;
        show_exporting_message('Importing '.$this->prev_resource->get(MW_RESOURCE_KEY_NAME).' as '.$resource_name);
        $this->prev_resource->set(MW_RESOURCE_KEY_NAME, $resource_name);
      } else {
        show_exporting_message('Importing '.$this->prev_resource->get(MW_RESOURCE_KEY_NAME));
      }
      if (!$storage->exists($this->cur_dataspace, $resource_name)) {
        $storage->create_resource($this->cur_dataspace, $this->prev_resource);
      } else {
        $storage->update_resource($this->cur_dataspace, $this->prev_resource);
      }
      $this->prev_resource = null;
    }

    /** @private */
    function cdataHandler($parser, $data) {
      if ($this->cur_resource === null) {
        return;
      }
      if (empty($this->cur_key)) {
        return;
      }
      $this->cur_cdata .= $data;
    }
    
    function import($file, $with_history = true, $dataspaces = array(), $force_import = false) {
      if (pathinfo($file, PATHINFO_EXTENSION) !== MW_XML_EXTENSION) {
        return null;
      }
      $this->to_enc = null;
      $enc = config('encoding');
      if (strcasecmp($enc, "utf-8") != 0) {
        if (!function_exists("iconv")) {
          return "iconv() not available, but needed for conversion from $enc to UTF-8";
        }
        $this->to_enc = $enc;
      }
      # in some PHP versions utf-8 will be used always (cause they can not autodetect)), in others it will be ignored,
      # hopefully every version will return utf-8
      $xml_parser = xml_parser_create("utf-8");
      $storage =& get_storage();
      if (sizeof($dataspaces) == 0) {
        $dataspaces = $storage->get_dataspace_names();
      }
      $this->dataspaces = $dataspaces;
      $this->with_history = $with_history;
      $this->force_import = $force_import;
      $this->cur_resource = null;
      $this->prev_resource = null;
      $this->prev_resource_name = null;
      xml_set_element_handler($xml_parser, array(&$this, "startElement"), array(&$this, "endElement"));
      xml_set_character_data_handler($xml_parser, array(&$this, "cdataHandler"));
      if (!($fp = fopen($file, "r"))) {
        return "Unable to open $file";
      }
      while ($data = fread($fp, 4096)) {
        if (!xml_parse($xml_parser, $data, feof($fp))) {
          return "XML error: ".xml_error_string(xml_get_error_code($xml_parser))." at line ".xml_get_current_line_number($xml_parser);
        }
      }
      fclose($fp);
      xml_parser_free($xml_parser);
      $this->flush_prev_resource();
      return true;
    }
    
  }

  class MW_XMLExporter extends MW_Exporter {

    function export($file, $with_history = true, $dataspaces = array()) {
      $storage =& get_storage();
      $enc = config('encoding');
      $conv_enc_from = null;
      # convert to UTF-8 if possible
      if (strcasecmp($enc, "utf-8") != 0) {
        if (function_exists("iconv")) {
          $conv_enc_from = $enc;
          $enc = "utf-8";
        }
      }
      $out = fopen($file, "wb");
      fwrite($out, '<?xml version="1.0" encoding="'.$enc.'"?>'."\n");
      fwrite($out, '<resources>'."\n");
      if (sizeof($dataspaces) == 0) {
        $dataspaces = $storage->get_dataspace_names();
      }
      foreach ($dataspaces as $ds) {
        explode_dataspace_name($ds, $ds, $wanted_res);
        $ds_def = $storage->get_dataspace_definition($ds);
        $types_map = array();
        /** @todo custom keys may have (currently) only text type */
        if ($ds_def->get_content_type() == MW_RESOURCE_CONTENT_TYPE_BINARY) {
          $types_map[MW_RESOURCE_KEY_CONTENT] = MW_XML_TYPE_BINARY;
        }
        $types_map[MW_RESOURCE_KEY_LAST_MODIFIED] = MW_XML_TYPE_DATETIME;
        $res_names = $storage->get_resource_names($ds);
        foreach ($res_names as $res_name) {
          if (($wanted_res !== null) && (strpos($res_name, $wanted_res) !== 0)) {
            continue;
          }
          if ($with_history) {
            # we need it ordered from oldest to newest
            $resources = array_reverse($storage->get_resource_history($ds, $res_name, true));
          } else {
            $resources = array( $storage->get_resource($ds, $res_name, null, true) );
          }
          foreach ($resources as $res) {
            show_exporting_message('Exporting '.$res->get(MW_RESOURCE_KEY_NAME).' revision '.$res->get(MW_RESOURCE_KEY_REVISION));
            fwrite($out, '<resource dataspace="'.$ds.'" name="'.$this->convert_for_xml($conv_enc_from, $enc, $res_name).'">'."\n");
            foreach ($res->data as $key => $value) {
              $type = MW_XML_TYPE_TEXT;
              if (isset($types_map[$key])) {
                $type = $types_map[$key];
              }
              fwrite($out, '<key name="'.$this->convert_for_xml($conv_enc_from, $enc, $key).'">');
              switch ($type) {
                case MW_XML_TYPE_BINARY:
                  $value = base64_encode($value);
                  break;
                case MW_XML_TYPE_DATETIME:
                  $value = $value->format_strftime("%Y%m%dT%H%M%SZ", true);
                  break;
              }
              fwrite($out, $this->convert_for_xml($conv_enc_from, $enc, $value, false));
              fwrite($out, '</key>'."\n");
            }
            fwrite($out, '</resource>'."\n");
          }
        }
      }
      fwrite($out, '</resources>'."\n");
      fclose($out);
      return true;
    }

    function convert_for_xml($from, $to, $str, $as_attr = true) {
      if ($from !== null) {
        $str = iconv($from, $to, $str);
      }
      return htmlspecialchars($str, ($as_attr ? ENT_QUOTES : ENT_NOQUOTES));
    }
    
    function get_format() {
      return MW_XML_FORMAT;
    }
    
  }

?>
