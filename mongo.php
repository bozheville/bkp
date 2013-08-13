<?php
/**
 * Library for work with mongodb
 * User: bozheville
 * Date: 4/14/13
 * Time: 6:42 PM
 * To change this template use File | Settings | File Templates.
 */
$db = null;
$mongo_client = null;

init_mongo();
//mongo_set_db("test");


function init_mongo()
{
    global $mongo_client;
    $mongo_client = new Mongo();
}

function mongo_set_db($name)
{
    global $db;
    global $mongo_client;
    $db = $mongo_client->selectDB($name);
}

function mongo_find($collection, $condition = array(), $limit = 0, $skip = 0, $sort = array())
{
    global $db;
    $cursor = $db->$collection->find($condition);
    if((boolean) $sort){
        $cursor = $cursor->sort($sort);
    }
    if((int) $skip > 0){
        $cursor = $cursor->skip((int) $skip);
    }
    if((int) $limit > 0){
        $cursor = $cursor->limit((int) $limit);
    }
    $result = array();

    foreach ($cursor as $doc) {
        $doc = process_cursor($doc);
        $result[$doc["_id"]] = $doc;
    }
    return $result;
}

function mongo_find_one($collection, $condition = array())
{
    global $db;
    $cursor = $db->$collection->findOne($condition);
    $doc = process_cursor($cursor);
    if (empty($doc["_id"])) {
        $doc = null;
    }
    return $doc;
}

function process_cursor($doc)
{
    $id = '$id';
    if (isset($doc["_id"]->$id)) {
        $doc["_id"]->$id;
    } else {
        $_id = $doc["_id"];
    }
    $doc["_id"] = $_id;
    return $doc;
}

function mongo_insert($collection, $insert)
{
    global $db;
    $db->$collection->insert($insert);
}

function mongo_update($collection, $update, $condition)
{
    global $db;
    $db->$collection->update($condition, $update, array("upsert" => true));
}

function mongo_drop($collection)
{
    global $db;
    $db->drop($collection);
}

function mongo_val($collection, $condition, $field = "_id")
{
    $field = explode("::", $field);
    $return = null;
    $data = mongo_find_one($collection, $condition);
    while ($key = array_shift($field)) {
        $data = $data[$key];
    }
    return $data;
}