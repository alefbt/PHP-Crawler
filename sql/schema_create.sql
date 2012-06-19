delimiter $$

CREATE DATABASE `crowler_db` /*!40100 DEFAULT CHARACTER SET utf8 */$$

delimiter $$

CREATE TABLE `hosts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `https` tinyint(1) NOT NULL DEFAULT '0',
  `host` varchar(255) NOT NULL,
  `port` int(6) NOT NULL DEFAULT '80',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`https`,`host`,`port`),
  KEY `host` (`host`),
  KEY `http_host` (`https`,`host`),
  KEY `http_host_port` (`https`,`host`,`port`),
  KEY `host_port` (`host`,`port`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8$$


CREATE TABLE `urls` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `host_id` bigint(20) NOT NULL,
  `path` varchar(255) NOT NULL,
  `get_params` varchar(255) DEFAULT NULL,
  `type` enum('lead','blocked','indexed','robots_not_allowed','error_no_data') NOT NULL DEFAULT 'lead',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `priority` int(11) DEFAULT '1000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8$$



CREATE TABLE `url_data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `url_id` bigint(20) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_url` (`url_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8$$


CREATE ALGORITHM=UNDEFINED  SQL SECURITY DEFINER VIEW `random_leads` AS select 
    u.id AS url_id,
    h.id AS host_id,
    h.https AS https,
    h.host AS host,
    h.port AS port,
    u.path AS path,
    u.get_params AS get_params
from
    (urls u
    join hosts h ON ((u.host_id = h.id)))
where
    (u.type = 'lead')
order by u.priority , rand()
limit 500$$

