CREATE TABLE `users`
(
    `ID` VARCHAR(32) NOT NULL PRIMARY KEY,
    `Password` CHAR(64) NOT NULL,
    `Email` VARCHAR(128) DEFAULT NULL,
    `Admin` BIT NOT NULL DEFAULT FALSE
) Engine=InnoDB;

CREATE TABLE `login`
(
    `User` VARCHAR(32) NOT NULL REFERENCES `users` (`ID`),
    `When` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Ip` VARCHAR(15) NOT NULL,
    `Device` VARCHAR(256) NOT NULL,

    PRIMARY KEY (`User`, `When`, `Ip`)
) Engine=InnoDB;

CREATE TABLE `openings`
(
    `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `Intro` VARCHAR(128) NOT NULL,
    `Title` VARCHAR(128) NOT NULL,
    `Episode` VARCHAR(64) NOT NULL,
    `Content` VARCHAR(1024) DEFAULT NULL,
    `Language` ENUM ('it', 'en') NOT NULL,
    `Author` VARCHAR(32) DEFAULT NULL REFERENCES `users` (`ID`),
    `Creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEdit` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP CHECK (`LastEdit` >= `Creation`)
) Engine=InnoDB;

CREATE VIEW `RecentOpenings` AS
SELECT `o`.* 
FROM `openings` AS `o`
ORDER BY IFNULL(`o`.`LastEdit`, `o`.`Creation`) DESC;