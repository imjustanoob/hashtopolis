<?php
use Bricky\Template;
require_once(dirname(__FILE__)."/inc/load.php");

if(!$LOGIN->isLoggedin()){
	header("Location: index.php?err=4".time()."&fw=".urlencode($_SERVER['PHP_SELF']));
	die();
}
else if($LOGIN->getLevel() < 5){
	$TEMPLATE = new Template("restricted");
	die($TEMPLATE->render($OBJECTS));
}

$TEMPLATE = new Template("supertasks");
$MENU->setActive("tasks_super");
$message = "";

//catch actions here...
if(isset($_POST['action'])){
	switch($_POST['action']){
		//currently no actions
	}
}

$supertasks = array();
$res = $DB->query("SELECT * FROM Supertask,tasks INNER JOIN SupertaskTask ON SupertaskTask.supertaskId=Supertask.supertaskId WHERE tasks.id=SupertaskTask.taskId ORDER BY Supertask.supertaskId");
$res = $res->fetchAll();
foreach($res as $supertask){
	$set = new DataSet();
	$set->setValues($supertask);
	$supertasks[] = $set;
}

$OBJECTS['supertasks'] = $supertasks;
$OBJECTS['numSupertasks'] = sizeof($supertasks);
$OBJECTS['message'] = $message;

echo $TEMPLATE->render($OBJECTS);



