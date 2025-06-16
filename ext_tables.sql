CREATE TABLE `tx_notifications_framework_configuration`
(
    `pid`             int(11)      DEFAULT '0' NOT NULL,
    `title`           varchar(255) DEFAULT ''  NOT NULL,
    `type`            varchar(64)  DEFAULT ''  NOT NULL,
    `target_audience` varchar(16)  DEFAULT ''  NOT NULL,
    `fe_groups`       text,
    `fe_users`        text,
    `label`           varchar(255) DEFAULT ''  NOT NULL,
    `message`         tinytext,
    `record`          text,
    `table`           varchar(255) DEFAULT ''  NOT NULL,
    `push`            tinyint(3)   DEFAULT '0' NOT NULL,
    `done`            tinyint(3)   DEFAULT '0' NOT NULL
);

CREATE TABLE `tx_notifications_framework_domain_model_notification`
(
    `title`         varchar(255)     DEFAULT ''  NOT NULL,
    `fe_user`       int(11) unsigned DEFAULT '0' NOT NULL,
    `configuration` int(11) unsigned DEFAULT '0' NOT NULL,
    `read`          tinyint(3)       DEFAULT '0' NOT NULL,
    `read_date`     int(11) unsigned DEFAULT '0' NOT NULL
);