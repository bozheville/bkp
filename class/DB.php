<?php
/**
 * Created by JetBrains PhpStorm.
 * User: bozheville
 * Date: 8/25/13
 * Time: 5:56 PM
 * To change this template use File | Settings | File Templates.
 */

class DB {
    public $db = null;
    private $client = null;

    public function __construct($name = null) {
        $this->init();
        if ((boolean) $name) $this->useDB($name);
    }

    private function init() {
        $this->client = new Mongo();
    }

    public function useDB($name) {
        $this->db = $this->client->selectDB($name);
    }

    public function find($collection, $condition = array(), $limit = 0, $skip = 0, $sort = array()) {
        $cursor = $this->db->$collection->find($condition);
        if ((boolean) $sort) $cursor = $cursor->sort($sort);
        if ((int) $skip > 0) $cursor = $cursor->skip((int) $skip);
        if ((int) $limit > 0) $cursor = $cursor->limit((int) $limit);
        $result = array();
        foreach ($cursor as $doc) {
            $result[$doc["_id"]] = $this->processCursor($doc);
        }
        return $result;
    }

    public function findOne($collection, $condition = array()) {
        $doc = $this->processCursor($this->db->$collection->findOne($condition));
        if (empty($doc["_id"])) {
            $doc = null;
        }
        return $doc;
    }

    private function processCursor($doc) {
        $id = '$id';
        if (isset($doc["_id"]->$id)) {
            $doc["_id"]->$id;
        } else {
            $_id = $doc["_id"];
        }
        $doc["_id"] = $_id;
        return $doc;
    }

    public function insert($collection, $insert) {
        $this->db->$collection->insert($insert);
    }

    public function update($collection, $update, $condition) {
        $this->db->$collection->update($condition, $update, array("upsert" => true));
    }

    public function drop($collection) {
        $this->db->drop($collection);
    }

    public function getVal($collection, $condition, $field = "_id") {
        $field = explode("::", $field);
        $return = null;
        $data = $this->findOne($collection, $condition);
        while ($key = array_shift($field)) {
            $data = $data[$key];
        }
        return $data;
    }
}