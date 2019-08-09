-- update
alter table md_views add edit_width smallint default 1;
alter table md_views drop  master_view;
