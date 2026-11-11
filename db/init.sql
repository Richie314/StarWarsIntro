-- The library user contract is Id:int, Name:string, Admin:bool.
-- Persistence, login history and intro tables are application-owned.
CREATE TABLE `users`
(
    `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `Name` VARCHAR(32) NOT NULL,
    `PasswordHash` VARCHAR(255) NOT NULL,
    `Email` VARCHAR(128) DEFAULT NULL,
    `Admin` BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT `UsersNameUnique` UNIQUE (`Name`),
    CONSTRAINT `UsersNameNotBlank` CHECK (CHAR_LENGTH(TRIM(`Name`)) > 0),
    CONSTRAINT `UsersPasswordNotBlank` CHECK (CHAR_LENGTH(`PasswordHash`) > 0),
    CONSTRAINT `UsersAdminBoolean` CHECK (`Admin` IN (0, 1)),
    INDEX `UsersEmailIndex` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login`
(
    `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `UserId` INT UNSIGNED NOT NULL,
    `When` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `Ip` VARCHAR(64) NOT NULL,
    `Device` VARCHAR(256) NOT NULL,
    CONSTRAINT `LoginUserForeignKey` FOREIGN KEY (`UserId`)
        REFERENCES `users` (`Id`) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX `LoginWhenIndex` (`When`),
    INDEX `LoginIpIndex` (`Ip`),
    INDEX `LoginUserWhenIndex` (`UserId`, `When`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `openings`
(
    `Id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `Title` VARCHAR(128) NOT NULL,
    `Episode` VARCHAR(64) NOT NULL,
    `Content` VARCHAR(4096) DEFAULT NULL,
    `Language` ENUM ('it', 'en') NOT NULL,
    `AuthorId` INT UNSIGNED DEFAULT NULL,
    `Creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEdit` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `OpeningsEditDate` CHECK (`LastEdit` >= `Creation`),
    CONSTRAINT `OpeningsAuthorForeignKey` FOREIGN KEY (`AuthorId`)
        REFERENCES `users` (`Id`) ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX `OpeningsCreationIndex` (`Creation`),
    INDEX `OpeningsLastEditIndex` (`LastEdit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE VIEW `RecentOpenings` AS
SELECT O.*, U.`Name` AS `AuthorName`
FROM `openings` O LEFT JOIN `users` U ON U.`Id` = O.`AuthorId`
ORDER BY COALESCE(O.`LastEdit`, O.`Creation`) DESC;

CREATE VIEW `RecentLogs` AS
SELECT L.`Id`, L.`UserId`, L.`When`, L.`Ip`, L.`Device`, U.`Name`, U.`Email`, U.`Admin`
FROM `login` L INNER JOIN `users` U ON L.`UserId` = U.`Id`
ORDER BY L.`When` DESC, L.`Id` DESC LIMIT 100;

CREATE VIEW `InactiveUsers` AS
SELECT U.`Id`, U.`Name`, U.`PasswordHash`, U.`Email`, U.`Admin`
FROM `users` U
WHERE U.`Admin` = FALSE AND NOT EXISTS (
    SELECT 1 FROM `login` L
    WHERE L.`UserId` = U.`Id` AND L.`When` >= CURRENT_TIMESTAMP - INTERVAL 366 DAY
);

CREATE EVENT `DeleteOldLoginsEvent`
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO DELETE FROM `login` WHERE `When` < CURRENT_TIMESTAMP - INTERVAL 150 DAY;
