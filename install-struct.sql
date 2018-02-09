DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_profile`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_profile` (
  `obj_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
   PRIMARY KEY (`obj_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_profile_skill_user`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_skill_user` (
  `skill_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
   PRIMARY KEY (`skill_user_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_profile_skill`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_skill` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `skill` varchar(255) NOT NULL,
   PRIMARY KEY (`skill_id`)
){TABLE_ENGINE=MyISAM};