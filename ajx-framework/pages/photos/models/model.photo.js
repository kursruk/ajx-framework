{ "table": "tblphoto",
  "select": "select id_photo as id, phot_id,firstname,lastname,note from $table $where $order $limit",
  "list_columns": "phot_id,firstname,lastname,note",
  "primary_key": "id_photo",
  "default_order":"id_photo desc",
  "search":"firstname like :search or lastname like :search or note like :search  or phot_id=:search",
  "rows_number_limit": 8,
  "beforeUpdate":"beforeUpdateCompany",
  "select_row": "select * from $table where cid=:cid",
  "select_total": "select count(*) from $table $where",
  "filter_parts":{
      "wild":"id_photo in (select d.id_photo from tblphoto_wild d where d.Wild=:wild)",
      "fregion":"region=:fregion",
      "sic":"sic=:sic",
      "industry_group":"industry_group=:industry_group",
      "subsector":"subsector=:subsector",
      "major_group":"cid in (select d.cid from sales_divdetails d where d.sic in (select s.id from sales_sic s where s.industry_group_id in (select g.id from sales_industry_groups g where g.major_group=:major_group)))",
      "sic_code":"cid in (select d.cid from sales_divdetails d where d.sic=:sic_code)"
  }
}
