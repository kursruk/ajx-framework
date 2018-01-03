{ "table": "tblphoto_ref",
  "select": "select t.* from $table t $where $order $limit",
  "list_columns": "RefNo,Ref,RefFlag",
  "primary_key": "RefID",
  "default_order":"RefNo",
  "search":"Wild like :search",
  "rows_number_limit": 5,
  "select_row": "select * from $table where RefID=:id",
  "select_total": "select count(*) from $table t $where",
  "filter_parts":{
      "master":"t.id_photo=:master"
  }
}
