create table t_qrcode(
    id int not null auto_increment,
    has_record int(1) not null,
    record_path varchar(1024) not null,
    primary key(id)
)Engine=innoDb default charset=utf8;