CREATE TABLE `tx_notifications_framework_configuration`
(
    `pid`               int(11)      DEFAULT '0' NOT NULL,
    `title`             varchar(255) DEFAULT ''  NOT NULL,
    `type`              varchar(64)  DEFAULT ''  NOT NULL,
    `target_audience`   varchar(16)  DEFAULT ''  NOT NULL,
    `be_groups`         text,
    `be_users`          text,
    `fe_groups`         text,
    `fe_users`          text,
    `label`             varchar(255) DEFAULT ''  NOT NULL,
    `notification_text` tinytext
);