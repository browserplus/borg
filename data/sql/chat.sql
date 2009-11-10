CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `utterance` text,
  `who` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_stamp` (`stamp`),
  KEY `chat_who` (`who`),
  FULLTEXT KEY `chat_utterance` (`utterance`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
