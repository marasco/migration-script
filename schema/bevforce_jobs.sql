-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `bf_files`;
CREATE TABLE `bf_files` (
  `fid` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'other',
  `name` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `fileurl` varchar(255) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `converted` int(1) NOT NULL DEFAULT '0',
  `old_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `bf_job_applications`;
CREATE TABLE `bf_job_applications` (
  `aid` int(10) NOT NULL AUTO_INCREMENT,
  `nid` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `submitted` datetime NOT NULL,
  `resume_fid` int(10) NOT NULL,
  `cover_letter_fid` int(10) NOT NULL,
  `additional_info` text NOT NULL,
  `answers` longtext NOT NULL,
  `read` varchar(255) NOT NULL DEFAULT 'n',
  `favorite` varchar(255) NOT NULL DEFAULT 'n',
  `status` varchar(255) NOT NULL DEFAULT 'published',
  PRIMARY KEY (`aid`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`),
  KEY `submitted` (`submitted`),
  KEY `read` (`read`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `content_type_job`;
CREATE TABLE `content_type_job` (
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `field_type_value` longtext,
  `field_name_value` longtext,
  `field_country_value` longtext,
  `field_city_value` longtext,
  `field_email_value` longtext,
  `field_website_value` longtext,
  `field_state_value` longtext,
  `field_zip_value` longtext,
  `field_job_reports_to_value` longtext,
  `field_job_direct_reports_value` longtext,
  `field_job_base_pay_value` longtext,
  `field_job_bonus_value` longtext,
  `field_job_expiration_value` varchar(20) DEFAULT NULL,
  `field_job_status_value` varchar(80) DEFAULT NULL,
  `field_brand_name_value` longtext,
  `field_external_job_board_value` longtext,
  `field_job_requirements_value` longtext,
  `field_job_requirements_format` int(10) unsigned DEFAULT NULL,
  `field_confidential_value` int(11) DEFAULT NULL,
  `field_company_description_value` longtext,
  `field_allow_to_post_value` int(11) DEFAULT NULL,
  `field_old_id_value` int(11) DEFAULT NULL,
  `insightuser` int(11) NOT NULL,
  PRIMARY KEY (`vid`),
  KEY `nid` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `node`;
CREATE TABLE `node` (
  `nid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT '',
  `language` varchar(12) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `created` int(11) NOT NULL DEFAULT '0',
  `changed` int(11) NOT NULL DEFAULT '0',
  `comment` int(11) NOT NULL DEFAULT '0',
  `promote` int(11) NOT NULL DEFAULT '0',
  `moderate` int(11) NOT NULL DEFAULT '0',
  `sticky` int(11) NOT NULL DEFAULT '0',
  `tnid` int(10) unsigned NOT NULL DEFAULT '0',
  `translate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nid`),
  UNIQUE KEY `vid` (`vid`),
  KEY `node_changed` (`changed`),
  KEY `node_created` (`created`),
  KEY `node_moderate` (`moderate`),
  KEY `node_promote_status` (`promote`,`status`),
  KEY `node_status_type` (`status`,`type`,`nid`),
  KEY `node_title_type` (`title`,`type`(4)),
  KEY `node_type` (`type`(4)),
  KEY `uid` (`uid`),
  KEY `tnid` (`tnid`),
  KEY `translate` (`translate`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `node_revisions`;
CREATE TABLE `node_revisions` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` longtext NOT NULL,
  `teaser` longtext NOT NULL,
  `log` longtext NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `format` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid`),
  KEY `nid` (`nid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `term_data`;
CREATE TABLE `term_data` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` longtext,
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `taxonomy_tree` (`vid`,`weight`,`name`),
  KEY `vid_name` (`vid`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `term_node`;
CREATE TABLE `term_node` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0',
  `vid` int(10) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`vid`),
  KEY `vid` (`vid`),
  KEY `nid` (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2017-07-18 13:59:07
