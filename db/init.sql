
--
-- Creation of tables and indexes
--

CREATE TABLE `users`
(
    `ID` VARCHAR(32) NOT NULL PRIMARY KEY,
    `Password` CHAR(64) NOT NULL,
    `Email` VARCHAR(128) DEFAULT NULL,
    `Admin` BIT NOT NULL DEFAULT FALSE
) Engine=InnoDB;

CREATE INDEX `UsersEmailIndex`
ON `users` (`Email`);

CREATE TABLE `login`
(
    `User` VARCHAR(32) NOT NULL,
    `When` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Ip` VARCHAR(64) NOT NULL,
    `Device` VARCHAR(256) NOT NULL,

    PRIMARY KEY (`User`, `When`, `Ip`),
    FOREIGN KEY (`User` ) 
        REFERENCES `users` (`ID`)
            ON UPDATE CASCADE
            ON DELETE CASCADE
) Engine=InnoDB;

CREATE INDEX `LoginWhenIndex`
ON `login` (`When`);

CREATE INDEX `LoginIpIndex`
ON `login` (`Ip`);

CREATE TABLE `openings`
(
    `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `Title` VARCHAR(128) NOT NULL,
    `Episode` VARCHAR(64) NOT NULL,
    `Content` VARCHAR(2048) DEFAULT NULL,
    `Language` ENUM ('it', 'en') NOT NULL,
    `Author` VARCHAR(32) DEFAULT NULL,
    `Creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEdit` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP CHECK (`LastEdit` >= `Creation`),

    FOREIGN KEY (`Author`) 
        REFERENCES `users` (`ID`)
            ON UPDATE CASCADE
            ON DELETE SET NULL
) Engine=InnoDB;

CREATE INDEX `OpeningsCreationIndex`
ON `openings` (`Creation`);

CREATE INDEX `OpeningsLastEditIndex`
ON `openings` (`LastEdit`);

--
-- Creation of views: common queries to store
--

-- All the openings ordered by the
-- most recently created or edited
CREATE VIEW `RecentOpenings` AS
SELECT `o`.* 
FROM `openings` AS `o`
ORDER BY IFNULL(`o`.`LastEdit`, `o`.`Creation`) DESC;

-- The most recent 100 log ins
CREATE VIEW `RecentLogs` AS
SELECT L.*, U.`Email`, U.`Admin`
FROM `login` L
    INNER JOIN `users`U ON L.`User` = U.`ID`
ORDER BY L.`When` DESC
LIMIT 100;

-- Users that haven't logged in in a year 
CREATE VIEW `InactiveUsers` AS
SELECT U.`ID`, U.`Email`
FROM `users` U
WHERE U.`Admin` = 0 AND NOT EXISTS (
    SELECT *
    FROM `login` L
    WHERE L.`User` = U.`ID` AND DATEDIFF(CURRENT_TIMESTAMP, L.`When`) <= 366
);

--
-- Creation of events
--

DROP EVENT IF EXISTS `DeleteOldLoginsEvent`;

-- Deletes logins of more than 150 days ago
CREATE EVENT `DeleteOldLoginsEvent`
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO 
    DELETE FROM `login`
    WHERE DATEDIFF(CURRENT_TIMESTAMP, `When`) > 150;