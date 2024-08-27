<?php

use DBA\AccessGroupUser;
use DBA\Chunk;
use DBA\ContainFilter;
use DBA\Hashlist;
use DBA\Factory;
use DBA\Hash;
use DBA\OrderFilter;
use DBA\QueryFilter;

require_once(dirname(__FILE__) . "/inc/load.php");

if (!Login::getInstance()->isLoggedin()) {
  header("Location: index.php?err=4" . time() . "&fw=" . urlencode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
  die();
}

AccessControl::getInstance()->checkPermission(DViewControl::HASHES_VIEW_PERM);

Template::loadInstance("cracks");
UI::add('pageTitle', "Show cracks");
Menu::get()->setActive("lists_cracks");

//catch actions here...
if (isset($_POST['action']) && CSRF::check($_POST['csrf'])) {
  $searchHandler = new SearchHandler();
  $searchHandler->handle($_POST['action']);
  if (UI::getNumMessages() == 0) {
    Util::refresh();
  }
}

// load groups for user
$qF = new QueryFilter(AccessGroupUser::USER_ID, Login::getInstance()->getUserID(), "=");
$userGroups = Factory::getAccessGroupUserFactory()->filter([Factory::FILTER => $qF]);
$accessGroupIds = array();
foreach ($userGroups as $userGroup) {
  $accessGroupIds[] = $userGroup->getAccessGroupId();
}

// load all hashlists which are in an access group the user has access to
$qF = new ContainFilter(Hashlist::ACCESS_GROUP_ID, $accessGroupIds);
$accessGroupHashlists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF]);
$hashlistIds = array();
foreach ($accessGroupHashlists as $accessGroupHashlist) {
  $hashlistIds[] = $accessGroupHashlist->getId();
}

$hashFactory = Factory::getHashFactory();
$qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
$qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);

$count = $hashFactory->countFilter([Factory::FILTER => [$qF1, $qF2]]);
$numPages = ceil($count / SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE));
if ($numPages * SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE) < $count) {
  $numPages++;
}

$currentPage = 1;
if (isset($_GET['page'])) {
  $currentPage = intval($_GET['page']);
}
UI::add('hashesPerPage', SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE));
UI::add('count', $count);
UI::add('numPages', $numPages);
UI::add('currentPage', $currentPage);

$qF1 = new QueryFilter(Hash::IS_CRACKED, 1, "=");
$qF2 = new ContainFilter(Hash::HASHLIST_ID, $hashlistIds);
$oF = new OrderFilter(Hash::TIME_CRACKED, "DESC LIMIT " . (SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE) * ($currentPage - 1)) . ", " . SConfig::getInstance()->getVal(DConfig::HASHES_PER_PAGE));
$hashes = $hashFactory->filter([Factory::FILTER => [$qF1, $qF2], Factory::ORDER => $oF]);

$crackDetailsPrimary = new DataSet();
$crackChunkTask = new DataSet();
$crackChunkAgent = new DataSet();
$crackHashType = new DataSet();

$oF1 = new OrderFilter(Chunk::SOLVE_TIME, "DESC LIMIT 1");
foreach ($hashes as $hash) {
  $crackDetailsPrimary->addValue($hash->getId(), $hash);
  if (!is_null($hash->getChunkId())) {
    $qF1 = new QueryFilter(Chunk::CHUNK_ID, $hash->getChunkId(), "=");
    $chunks = Factory::getChunkFactory()->filter([Factory::FILTER => $qF1, Factory::ORDER => $oF1]);
    foreach ($chunks as $chunk) {
      $crackChunkTask->addValue($hash->getId(), $chunk->getTaskId());
      $crackChunkAgent->addValue($hash->getId(), $chunk->getAgentId());
    }
  }
  else {
    $crackChunkTask->addValue($hash->getId(), null);
  }
  $qF2 = new QueryFilter(Hashlist::HASHLIST_ID, $hash->getHashlistId(), "=");
  $hashlists = Factory::getHashlistFactory()->filter([Factory::FILTER => $qF2]);
  foreach ($hashlists as $hashlist) {
    $crackHashType->addValue($hash->getId(), $hashlist->getHashTypeId());
  }
}

UI::add('cracks', $hashes);
UI::add('crackDetailsPrimary', $crackDetailsPrimary);
UI::add('crackChunkTask', $crackChunkTask);
UI::add('crackChunkAgent', $crackChunkAgent);
UI::add('crackHashType', $crackHashType);

echo Template::getInstance()->render(UI::getObjects());
