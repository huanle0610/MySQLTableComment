<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function db_list()
	{
	    $this->load->database();
        $sql = "SELECT
  distinct TABLE_SCHEMA
FROM information_schema.tables
WHERE TABLE_SCHEMA NOT IN ('information_schema', 'performance_schema', 'mysql')";
        $query = $this->db->query($sql);
        $dbs = $query->result_array();

        $ret = array(
            'items' => $dbs,
            'totalCount' => count($dbs)
        );
        die(json_encode($ret));
	}

	public function table_list()
	{
	    $query = $this->input->post('query');
	    $this->load->database();
        $sql = "SELECT
  TABLE_SCHEMA,
  TABLE_NAME,
  concat(TABLE_SCHEMA, '.', TABLE_NAME) as `table`,
  ENGINE,
  TABLE_ROWS,
  TABLE_COMMENT,
  TABLE_COLLATION
FROM information_schema.tables
WHERE table_schema NOT IN ('information_schema', 'performance_schema', 'mysql')";

        if($query)
        {
            $sql .= " and concat(TABLE_SCHEMA, '.', TABLE_NAME)  like '%$query%' ";
        }


        $query = $this->db->query($sql);
        $dbs = $query->result_array();


        $columns = $this->getQueriedFieldColumns($query);
        $this->addActionColumn($columns, 'table');


        $ret = array(
            'metaData' => array(
                'unique_key' => array('TABLE_SCHEMA', 'TABLE_NAME'),
                'columns' => $columns
            ),

            'items' => $dbs,
            'totalCount' => count($dbs)
        );
        die(json_encode($ret));
	}

	public function field_list()
	{
        $where_or = array();
        $tables = $this->input->post('tables');
        if($tables)
        {
            $tables = json_decode($tables);
            if($tables)
            {
                foreach ($tables as $table)
                {
                    list($table_schema, $table_name) = explode('.', $table);
                    $where_or[] = sprintf('(table_name="%s" and table_schema="%s")', $table_name, $table_schema);
                }
            }
        }

        $this->load->database();
        $sql = "SELECT
  TABLE_SCHEMA,
  TABLE_NAME,
  COLUMN_NAME,
  DATA_TYPE,
  IS_NULLABLE,
  COLUMN_DEFAULT,
  COLUMN_COMMENT,
  COLUMN_KEY,
  EXTRA
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
table_schema NOT IN ('information_schema', 'performance_schema', 'mysql') ";
        if($where_or)
        {
            $sql .= sprintf(" and (%s)", join(' OR ', $where_or));
        }
        else
        {
            $sql .= ' limit 50';
        }

        $query = $this->db->query($sql);
        $dbs = $query->result_array();


        $columns = $this->getQueriedFieldColumns($query);
        $this->addActionColumn($columns, 'column');

        $ret = array(
            'metaData' => array(
                'unique_key' => array('TABLE_SCHEMA', 'TABLE_NAME', 'COLUMN_NAME'),
                'columns' => $columns
            ),

            'items' => $dbs,
            'totalCount' => count($dbs)
        );
        die(json_encode($ret));
	}

	function getQueriedFieldColumns($query)
    {
        $columns = array();
        foreach ($query->list_fields() as $field)
        {
            $formatedFieldName = $this->formatField($field);
            $columns[] = array(
                'dataIndex'  => $field,
                'tooltip' => $formatedFieldName,
                'text' => $formatedFieldName
            );
        }
        return $columns;
    }

    function addActionColumn(&$columns, $type)
    {
        $editHandler = array(
            'table' => 'editTableComment',
            'column' => 'editColumnComment'
        );

        $actionColumn = array(
            'xtype' => 'actioncolumn',
            'minWidth' => 85,
            'align' => 'center',
            'items' => array(
//                array(
//                    'iconCls' => 'x-fa fa-eye',
//                    'tooltip' => 'Show Detail',
//                    'handler' => 'showTableDetail'
//                ),
                array(
                    'iconCls' => 'x-fa fa-edit',
                    'tooltip' => 'Edit Comment',
                    'handler' => $editHandler[$type]
                ),
                array(
                    'tooltip' => 'Add or remove to collection',
                    'getClass' => 'getCollectionCls',
                    'handler' => 'editCollection'
                )
            )
        );

        $columns[] = $actionColumn;
//        array_unshift($columns, $actionColumn);
    }

    function formatField($str)
    {
        $str = strtolower($str);

        if(strpos($str, '_') !== false)
        {
            $arr = explode('_', $str);
            $arr = array_map('ucfirst', $arr);

            return implode(' ', $arr);
        }
        else
        {
            $str = ucfirst($str);
        }

        return $str;
    }

    function saveComment()
    {
        $type = $this->input->post('type');
        $saveFunc = 'save' . ucfirst($type) . 'Comment';
        $saveRet = $this->$saveFunc();
//
        $ret = array(
            'success' => $saveRet,
            'msg' => $this->getSaveCommentMesage()
        );
//
//        var_dump($type);
        die(json_encode($ret));
    }

    function getSaveCommentMesage()
    {
        return 'ok';
    }

    function saveTableComment()
    {
        $table_schema = $this->input->post('TABLE_SCHEMA');
        $table_name = $this->input->post('TABLE_NAME');
        $comment = trim($this->input->post('comment'));

        $table = sprintf('%s.%s', $table_schema, $table_name);


        return $this->updateTableComment($table, $comment);
    }

    function saveColumnComment()
    {
        $table_schema = $this->input->post('TABLE_SCHEMA');
        $table_name = $this->input->post('TABLE_NAME');
        $column_name = $this->input->post('COLUMN_NAME');
        $comment = trim($this->input->post('comment'));

        $table = sprintf('%s.%s', $table_schema, $table_name);


        return $this->updateColumnComment($table, $column_name, $comment);
    }

    function updateTableComment($table, $comment)
    {
        $this->load->database();
        $sql = sprintf("ALTER TABLE %s COMMENT = '%s'", $table, urldecode($comment));

        return $this->db->query($sql);
    }

    function getTableColumnDefine($column, $createSql)
    {
        preg_match("/^\s*(`$column` .*?),*$/m", $createSql, $matches);

        if(isset($matches[1]) && $matches[1])
        {
            $removed_comment = preg_replace('/ COMMENT .*?$/', '', $matches[1]);
            return $removed_comment;
        }

        return false;
    }

    function updateColumnComment($table, $column, $comment)
    {
        $old_column = $this->getTableColumnDefine($column, $this->getCreateTableSql($table));
        $alter_sql = sprintf('ALTER TABLE %s CHANGE %s %s COMMENT "%s"',
            $table,
            $column,
            $old_column,
            $comment
        );
        return $this->db->query($alter_sql);
    }

    function getCreateTableSql($table)
    {
        $this->load->database();
        $sql = sprintf('SHOW CREATE TABLE %s', $table);
        $row = $this->db->query($sql)->row_array();
        return $row ? $row['Create Table'] : false;
    }
}
