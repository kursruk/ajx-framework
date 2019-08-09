{ "table": "tblwild",
  "select": "select wild as id, `desc` as name from $table c $where $order $limit",
  "list_columns": "name",
  "primary_key": "id",
  "default_order":"1",
  "search":"`desc` like :search",
  "select_row": "select * from $table where id=:id",
  "select_total": "select  count(*) from $table $where"
}
