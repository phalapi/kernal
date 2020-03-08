<?php
namespace PhalApi\Model;

use PhalApi\Exception\InternalServerErrorException;

class DataModel extends NotORMModel {

    /** ---------------- 更多数据库基本操作 ---------------- **/

    /** ---------------- 聚合操作 ---------------- **/

    /**
     * 获取总数
     * @param string|array|NULL $where 统计条件
     * @param string $countBy 需要统计的字段名
     * @return int 总数
     */
    public function count($where = NULL, $countBy = '*') {
        $orm = $this->getORM();

        // 条件
        if (!empty($where)) {
            $orm->where($where);
        }

        $total = $orm->count($countBy);
        return intval($total);
    }

    public function sum($where, $sumBy) {
        return $this->getORM()->where($where)->sum($sumBy);
    }

    /** ---------------- 查询操作 ---------------- **/

    // 获取字段值
    public function getValueBy($field, $value, $selectFiled, $default = FALSE) {
        $rows = $this->getValueMoreBy($field, $value, $selectFiled, 1);
        return $rows ? $rows[0] : $default;
    }

    // 获取字段值（多个）
    public function getValueMoreBy($field, $value, $selectFiled, $limit = 0, $isDistinct = FALSE) {
        $orm = $this->getORM()->select($isDistinct ? 'DISTINCT ' : '' . $selectFiled)->where($field, $value);
        $limit = intval($limit);
        if ($limit > 0) {
            $orm->limit(0, $limit);
        }
        $rows = $orm->fetchAll();
        return $rows ? array_column($rows, $selectFiled) : array();
    }

    // 获取一条纪录
    public function getDataBy($field, $value, $select = '*', $default = FALSE) {
        $rows = $this->getDataMoreBy($field, $value, 1, $select);
        return !empty($rows) ? $rows[0] : $default; 
    }

    // 获取多条纪录
    public function getDataMoreBy($field, $value, $limit = 0, $select = '*') {
        $orm = $this->getORM()
            ->select($select)
            ->where($field, $value);
        $limit = intval($limit);
        if ($limit > 0) {
            $orm->limit(0, $limit);
        }
        return $orm->fetchAll();
    }

    // 根据条件，取一条纪录数据
    public function getData($where = NULL, $whereParams = array(), $select = '*', $default = FALSE) {
        $rows = $this->getList($where, $whereParams, $select, NULL, 1, 1);
        return !empty($rows) ? $rows[0] : $default;
    }

    // 根据条件，取列表数组
    public function getList($where = NULL, $whereParams = array(), $select = '*', $order = NULL, $page = 1, $perpage = 100) {
        $page = intval($page);
        $perpage = intval($perpage);

        $orm = $this->getORM();

        // 条件
        if (!empty($where) && !empty($whereParams)) {
            $orm->where($where, $whereParams);
        } else if (!empty($where)) {
            $orm->where($where);
        }

        // 字段选择
        $select = is_array($select) ? implode(',', $select) : $select;
        $orm->select($select);

        // 排序
        $order = is_array($order) ? implode(', ', $order) : $order;
        if (!empty($order)) {
            $orm->order($order);
        }

        // 分页
        return $orm->page($page, $perpage)->fetchAll();
    }

    public function __call($name, $arguments) {
        if (substr($name, 0, 9) == 'getDataBy') {
            $field = lcfirst(substr($name, 9));
            $value = isset($arguments[0]) ? $arguments[0] : NULL;
            $select = isset($arguments[1]) ? $arguments[1] : '*';
            $default = isset($arguments[2]) ? $arguments[2] : FALSE;
            return $this->getDataBy($field, $value, $select, $default);
        } else if (substr($name, 0, 13) == 'getDataMoreBy') {
            $field = lcfirst(substr($name, 13));
            $value = isset($arguments[0]) ? $arguments[0] : NULL;
            $limit = isset($arguments[1]) ? $arguments[1] : 0;
            $select = isset($arguments[2]) ? $arguments[2] : '*';
            return $this->getDataMoreBy($field, $value, $limit, $select);
        }

        throw new InternalServerErrorException(
            \PhalApi\T('Error: Call to undefined function PhalApi\Model\DataModel::${name}()', array('name' => $name))
        );

    }

    /** ---------------- 删除操作 ---------------- **/

    public function deleteAll($where, $whereParams = array()) {
        $orm = $this->getORM();

        // 条件
        if (!empty($whereParams)) {
            $orm->where($where, $whereParams);
        } else {
            $orm->where($where);
        }

        return $orm->delete();
    }

    public function deleteIds($ids) {
        return $this->getORM()->where('id', $ids)->delete();
    }

    /** ---------------- 更新操作 ---------------- **/

    public function updateAll($where, array $updateData) {
        return $this->getORM()->where($where)->update($updateData);
    } 

    public function updateCounter($where, $updateData) {
        return $this->getORM()->where($where)->updateMultiCounters($updateData);
    }

    /** ---------------- 插入操作 ---------------- **/

    public function insertMore($datas, $isIgnore = FALSE) {
        return $this->getORM()->insert_multi($datas, $isIgnore);
    }
}
