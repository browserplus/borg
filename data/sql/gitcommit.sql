CREATE TABLE IF NOT EXISTS `gitcommit` (
  `project` VARCHAR( 32 ) NOT NULL ,
  `tcommit` INT NOT NULL ,
  `url` VARCHAR( 255 ) NOT NULL ,
  `msg` TEXT NOT NULL ,
  UNIQUE (
    `project`
  )
) ENGINE = MYISAM;