<?php

require_once(dirname(__FILE__) . "/inc/load.php");

AccessControl::getInstance()->checkPermission(DViewControl::LOGIN_VIEW_PERM);

$SSO_VARIABLE=SConfig::getInstance()->getVal(DConfig::SSO_LOGIN_VAR);

if ( !isset($_SERVER[$SSO_VARIABLE]) ) {
  header("Location: index.php?err=5" . time());
  die();
}

$user = $_SERVER[$SSO_VARIABLE];


$fw = (isset($_POST['fw'])) ? $_POST['fw'] : "";


Login::getInstance()->SSOlogin($user);

if (Login::getInstance()->isLoggedin()) {
  if (strlen($fw) > 0) {
    $fw = urldecode($fw);
    $url = Util::buildServerUrl() . ((Util::startsWith($fw, '/')) ? "" : "/") . $fw;
    header("Location: " . $url);
    die();
  }
  header("Location: index.php");
  die();
}

header("Location: index.php?err=6" . time());

