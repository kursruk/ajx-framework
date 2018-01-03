{ "table": "tblphoto_wild",
  "select": "select t.*, w.desc from $table t join tblwild w on t.Wild=w.wild  $where $order $limit",
  "list_columns": "WildNo,Wild,desc",
  "primary_key": "Wild_ID",
  "default_order":"WildNo",
  "search":"Wild like :search",
  "rows_number_limit": 5,
  "select_row": "select * from $table where Wild_ID=:id",
  "select_total": "select count(*) from $table t join tblwild w on t.Wild=w.wild $where",
  "filter_parts":{
      "master":"t.id_photo=:master"
  }
}
