CREATE TABLE IF NOT EXISTS `fraternity`.`positions` (
 `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
 `position_title` varchar(64) NOT NULL COMMENT 'varchar name of position'
 PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';