create table departments
( id integer not null auto_increment,
  department varchar(255) not null,
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table job_postions
( id integer not null auto_increment,
  job_position varchar(255) not null,
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table staff
( id integer not null auto_increment,
  firstname varchar(255) not null,
  lastname varchar(255) not null,
  thirdname varchar(255),
  tabno varchar(10) not null,
  department_id integer not null,
  position_id integer not null,
  employe_date date not null,
  dismissal_date date null,
  unique(tabno),
  constraint foreign key (department_id) references departments (id)  on update cascade,
  constraint foreign key (position_id) references job_postions (id)  on update cascade,
  primary key (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
