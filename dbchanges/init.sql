CREATE TABLE IF NOT EXISTS `configurations` (
  `key` varchar(150) NOT NULL,
  `value` text NOT NULL,
  `description` text,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;