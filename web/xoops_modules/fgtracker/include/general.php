<?php
  function get_request($name)
  {
     return array_key_exists($name,$_REQUEST)?$_REQUEST[$name]:"";
  }
?>
