<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

$installer = $this;

$installer->startSetup();

// Create the database structure
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('blog/blog')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/blog')} (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `post_content` text NOT NULL,
  `post_image` varchar(255) NOT NULL DEFAULT '',
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `update_user` varchar(255) NOT NULL DEFAULT '',
  `meta_keywords` text NOT NULL,
  `meta_description` text NOT NULL,
  `comments` tinyint(11) NOT NULL,
  `tags` text NOT NULL,
  `short_content` text NOT NULL,
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/cat')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/cat')} (
  `cat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(6) NOT NULL,
  `meta_keywords` text NOT NULL,
  `meta_description` text NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/cat_store')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/cat_store')} (
  `cat_id` smallint(6) unsigned DEFAULT NULL,
  `store_id` smallint(6) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/comment')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/comment')} (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` smallint(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `created_time` datetime DEFAULT NULL,
  `user` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/post_cat')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/post_cat')} (
  `cat_id` smallint(6) unsigned DEFAULT NULL,
  `post_id` smallint(6) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/store')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/store')} (
  `post_id` smallint(6) unsigned DEFAULT NULL,
  `store_id` smallint(6) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('blog/tag')};
CREATE TABLE IF NOT EXISTS {$this->getTable('blog/tag')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `tag_count` int(11) NOT NULL DEFAULT '0',
  `store_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`,`tag_count`,`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

// Insert the sample data
$installer->run("

INSERT INTO {$this->getTable('blog/cat')} (`cat_id`, `title`, `identifier`, `sort_order`, `meta_keywords`, `meta_description`) VALUES
(NULL, 'Blog', 'blog', 0, '', '');

INSERT INTO {$this->getTable('blog/blog')} (`post_id`, `title`, `post_content`, `status`, `created_time`, `update_time`, `identifier`, `user`, `update_user`, `meta_keywords`, `meta_description`, `comments`, `tags`, `short_content`) VALUES (NULL, 'Sample Blog Post', 'Sample Content', 1, NOW( ), NULL, 'sampleblogpost', 'ShopShark', 'ShopShark', '', '', 0, '', 'Sample Short Content');

INSERT INTO {$this->getTable('blog/post_cat')} (`cat_id`, `post_id`) VALUES (1, 1);

");

$installer->endSetup();