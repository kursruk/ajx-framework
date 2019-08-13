-- update
alter table md_views add edit_width smallint default 1;
alter table md_views drop  master_view;

alter table md_fields add sortable smallint not null default 0;
alter table md_refs add unique(refname);
