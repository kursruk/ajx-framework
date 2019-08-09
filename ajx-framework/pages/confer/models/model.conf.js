{ "table": "md_conf",
  "select": "select * from $table $where $order $limit",
  "list_columns": "conf,version,minor_version",
  "primary_key": "id",
  "default_order":"id",
  "search":"conf like :search",
  "rows_number_limit": 10,
  "select_row": "select * from $table where id=:id",
  "select_total": "select count(*) from $table $where"  
}
