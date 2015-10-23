-- ----------------------------
--  Table structure for `sirportly_contacts`
-- ----------------------------
DROP TABLE IF EXISTS `sirportly_contacts`;
CREATE TABLE `sirportly_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `sirportly_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;