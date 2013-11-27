CREATE TABLE `ezx_xrowquestionnaire_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `question_id` bigint(20) NOT NULL,
  `answer_id` bigint(20) NOT NULL,
  `data_text` text,
  `created` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session` varchar(255) DEFAULT NULL,
  `contentobject_id` int(11) NOT NULL,
  `score` float DEFAULT NULL,
  `correct` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `ezx_xrowquestionnaire_optin` (
  `user_id` int(11) NOT NULL,
  `siteaccess` varchar(255) NOT NULL,
  `optin` int(11) NOT NULL,
  `optout` int(11) DEFAULT NULL,
  `random` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`, `siteaccess`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8