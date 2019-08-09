-- Конфигурация
create table md_conf
( id integer not null auto_increment,
  conf varchar(255) not null,
  version integer not null default 1,
  minor_version integer not null default 0,
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

insert into md_conf values (1,'Test config',1,0);

-- Представления
create table md_views
( id integer not null auto_increment,
  name varchar(200) not null,
  vtitle varchar(255) not null,
  tname varchar(100) not null,
  conf_id integer not null,
  edit_width smallint default 1,
  primary key (id),
  constraint unique (name),
  key (tname),
  constraint foreign key (conf_id) references md_conf (id)  on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- Группы полей
create table md_groups
( id integer not null auto_increment,
  view_id integer not null,
  gtitle varchar(255) not null,
  primary key (id),
  constraint foreign key (view_id) references md_views (id) on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- Ссылки между представлениями
create table md_refs
( id integer not null auto_increment,
  conf_id integer not null,
  refname varchar(200) not null,
  rtitle varchar(200) not null,
  view_id integer not null,     
  master_view_id integer not null,
  primary key (id),
  constraint foreign key (view_id) references md_views (id) on delete cascade on update cascade,
  constraint foreign key (master_view_id) references md_views (id) on delete cascade on update cascade,
  constraint foreign key (conf_id) references md_conf (id)on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- Поля связей для внешних ключей
create table md_refs_fields 
( id integer not null auto_increment,
  ref_id integer not null,
  fk_field varchar(200) not null,
  pk_field varchar(200) not null,
  primary key (id),
  constraint foreign key (ref_id) references md_refs (id) on delete cascade on update cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

create table md_widgets
( id integer not null auto_increment,
  wname varchar(200) not null,
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

insert into md_widgets (id, wname) values (1,'справочное поле'),
(2,'подчинённая таблица'),(3,'многострочный текст'),(4,'флажок'),
(5,'дата'),(6,'дата/время'),(7,'время');

create table md_fields
( id integer not null auto_increment,
  view_id integer not null,
  group_id integer,
  fname varchar(255) not null,
  ftitle varchar(255),
  ref_id integer,
  visable smallint not null default 1,
  searchable smallint not null default 0,
  ingrid smallint not null default 1,
  widget_id integer,
  width integer,
  pkey smallint not null default 0,
  required smallint not null default 1,
  default_value text,
  primary key (id),
  constraint foreign key (view_id) references md_views (id) on delete cascade on update cascade,
  constraint foreign key (widget_id ) references md_widgets (id),
  constraint foreign key (ref_id) references md_refs (id),
  constraint foreign key (group_id) references md_groups (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- alter table md_fields CONVERT TO CHARACTER SET 'utf8'; 
create table md_lookups
( field_id integer not null,
  display_field_id integer not null,
  constraint foreign key (field_id) references md_fields (id) on delete cascade ,
  constraint foreign key (display_field_id) references md_fields (id) on delete cascade,
  primary key (field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table md_view_translations
( view_id integer not null,
  lang varchar(8) not null,
  json text not null,
  constraint foreign key (view_id) references md_views (id) on delete cascade on update cascade,
  primary key (view_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

