    create table passes (
    id bigint unsigned not null auto_increment primary key,
    lat double not null,
    lon double not null,
    rounded_lat double not null,
    rounded_long double not null,
    ip char(16) default null,
    sat_id int not null,
    start_utc int unsigned not null,
    end_utc int unsigned not null,
    created timestamp not null default current_timestamp,
    duration int not null,
    magnitude double not null,
    key `rounded_lat_rounded_lon_start_utc_index`(rounded_lat, rounded_long, start_utc),
    key `ip_start_utc_index` (ip, start_utc)
    );
