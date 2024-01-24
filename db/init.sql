DROP DATABASE IF EXISTS `ciucci_660389`;
CREATE DATABASE `ciucci_660389`;

USE `ciucci_660389`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Creation of tables and indexes
--

CREATE TABLE `users`
(
    `ID` VARCHAR(32) NOT NULL PRIMARY KEY,
    `Password` CHAR(64) NOT NULL,
    `Email` VARCHAR(128) DEFAULT NULL,
    `Admin` BIT NOT NULL DEFAULT FALSE
) Engine=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `UsersEmailIndex`
ON `users` (`Email`);

CREATE TABLE `login`
(
    `User` VARCHAR(32) NOT NULL,
    `When` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Ip` VARCHAR(15) NOT NULL,
    `Device` VARCHAR(256) NOT NULL,

    PRIMARY KEY (`User`, `When`, `Ip`),
    FOREIGN KEY (`User` ) 
        REFERENCES `users` (`ID`)
            ON UPDATE CASCADE
            ON DELETE CASCADE
) Engine=InnoDB DEFAULT CHARSET=utf8mb4;

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
    `LastEdit` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`Author`) 
        REFERENCES `users` (`ID`)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
    CONSTRAINT `ControlloSuDate` CHECK (`LastEdit` >= `Creation`)
) Engine=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `OpeningsCreationIndex`
ON `openings` (`Creation`);

CREATE INDEX `OpeningsLastEditIndex`
ON `openings` (`LastEdit`);

CREATE TABLE `report`
(
    `ID` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `Opening` INT NOT NULL,
    `Text` TEXT DEFAULT NULL,
    `Creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Viewed` BIT NOT NULL DEFAULT FALSE,
    `Problematic` BIT NOT NULL DEFAULT FALSE,

    FOREIGN KEY (`Opening`) 
        REFERENCES `openings` (`ID`)
            ON UPDATE CASCADE
            ON DELETE CASCADE
) Engine=InnoDB DEFAULT CHARSET=utf8mb4;


DELIMITER $$

CREATE TRIGGER `UpdateReportViewedField`
BEFORE UPDATE ON `report`
FOR EACH ROW
BEGIN
    IF NEW.`Viewed` = OLD.`Viewed` THEN
        SET NEW.`Viewed` = b'1';
    END IF;
END $$

DELIMITER ;

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

-- Not yet seen reports
CREATE VIEW `UnviewedReports` AS
SELECT R.*
FROM `report` R
WHERE NOT R.`Viewed`
ORDER BY R.`Creation` DESC
LIMIT 100;

-- Intros that have been marked as problematic
CREATE VIEW `ProblematicIntros` AS
SELECT O.`ID`, O.`Title`, O.`Language`, COUNT(DISTINCT R.`ID`) AS "NumeroSegnalazioni"
FROM `openings` O
    INNER JOIN `report` R ON R.`Opening` = O.`ID`
WHERE R.`Problematic`
GROUP BY O.`ID`
ORDER BY "NumeroSegnalazioni" DESC;

-- Users whose intros have been marked as problematic
CREATE VIEW `ProblematicUsers` AS
SELECT U.`ID`, U.`Email`, 
    COUNT(DISTINCT O.`ID`) AS "RisorseCompromesse", 
    COUNT(DISTINCT R.`ID`) AS "NumeroSegnalazioni"
FROM `users` U
    INNER JOIN `openings` O ON O.`Author` = U.`ID`
    INNER JOIN `report` R ON R.`Opening` = O.`ID`
WHERE U.`Admin` = 0 AND R.`Problematic`
HAVING COUNT(DISTINCT O.`ID`) >= 2 OR COUNT(DISTINCT R.`ID`) >= 3
ORDER BY "NumeroSegnalazioni" DESC, "RisorseCompromesse" DESC;

--
-- Creation of events
--

-- Deletes logins of more than 150 days ago
CREATE EVENT `DeleteOldLoginsEvent`
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO 
    DELETE FROM `login`
    WHERE DATEDIFF(CURRENT_TIMESTAMP, `When`) > 150;


-- Deletes automatically users that have more than 5 reports confirmed
CREATE EVENT `DeleteBadUsers`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO 
    DELETE FROM `users`
    WHERE `users`.`ID` IN (
        SELECT P.`ID`
        FROM `ProblematicUsers` P
    );

-- Deletes automatically intros that have  reports confirmed
CREATE EVENT `DeleteBadIntros`
ON SCHEDULE EVERY 10 DAY
STARTS CURRENT_TIMESTAMP + INTERVAL 3 DAY
DO 
    DELETE FROM `openings`
    WHERE `openings`.`ID` IN (
        SELECT P.`ID`
        FROM `ProblematicIntros` P
    );

-- Delete old reports that have been discarded by an admin
CREATE EVENT `DeleteOldReports`
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP
DO 
    DELETE FROM `report`
    WHERE `report`.`Viewed` AND NOT `report`.`Problematic`;


INSERT INTO `users` (`ID`, `Password`, `Email`, `Admin`) VALUES
    ('admin', '$2y$10$wCcrukG8tXpfyQb/ISf8XePa2wf6DDVwxD2t6Xr0A/nzSmnoo8nCu', 'admin@test.it', b'1'),
    ('utente', '$2y$10$kiyV6xCTxhXaLt0/rxUUO.bifCNvQJyB5iABEfEmxOJ.9ieCwNP5G', 'utente@sito.it', b'0'),
    ('Maleducato', '$2y$10$FdP6/Mq0N0MwWTcTC75Bk.1KKw2XRRp3pksBuYxgNNMqVsHmWsKBu', 'maleducato@mail.it', b'0'),
    ('UtenteInattivo1', '$2y$10$yf.C12bTBZRDId/HDGg6jeN1wtox8IC7QGTNRK./dnHWEbGTmu.lS', 'inattivo@mail.com', b'0'),
    ('UtenteInattivo2', '$2y$10$yf.C12bTBZRDId/HDGg6jeN1wtox8IC7QGTNRK./dnHWEbGTmu.lS', 'inattivo@mail.com', b'0'),
    ('UtenteInattivo3', '$2y$10$yf.C12bTBZRDId/HDGg6jeN1wtox8IC7QGTNRK./dnHWEbGTmu.lS', 'inattivo@mail.com', b'0'),
    ('UtenteInattivo4', '$2y$10$yf.C12bTBZRDId/HDGg6jeN1wtox8IC7QGTNRK./dnHWEbGTmu.lS', 'inattivo@mail.com', b'0');


INSERT INTO `openings` (`ID`, `Title`, `Episode`, `Content`, `Language`, `Author`, `Creation`, `LastEdit`) VALUES
(1, 'Noè e l\'Arca', 'Canzoncina', 'Un dì Noè nella foresta andò,\r\ne tutti gli animali volse attorno a sé.\r\n\"Il Signore è arrabbiato: il diluvio manderà!\r\nVoi non avete colpa, io vi salverò\"\r\n\r\nE mentre salivano gli animali,\r\nNoè vide nel cielo, un grosso nuvolone e,\r\ngoccia dopo goccia, a piover cominciò.\r\n\"Non posso più aspettare, l\'Arca chiuderò!\"\r\n\r\nE mentre continuava a salire il mare,\r\ne l\'Arca era lontana, Noè non pensò più\r\na chi dimenticò. Da allora più nessuno vide\r\ni due liocorni.', 'it', 'admin', '2024-01-08 10:31:02', '2024-01-08 10:43:25'),
(2, 'Una mia creazione', 'Episodio XIII', 'Il mio testo qui\r\n\r\n\r\n\r\nAltro testo qui', 'it', 'admin', '2024-01-08 10:52:03', NULL),
(3, 'Lorem ipsum', 'Episodio Boh', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'it', 'admin', '2024-01-08 10:53:59', NULL),
(4, 'Altro lorem', 'Episodio $', 'Lorem ipsum dolor sit amet,\r\nconsectetur adipiscing elit,\r\nsed do eiusmod tempor incididunt\r\nut labore et dolore magna aliqua.\r\n\r\nPorttitor eget dolor morbi non\r\narcu risus quis. Tellus elementum\r\nsagittis vitae et leo duis ut diam. \r\nNibh mauris cursus mattis molestie. \r\n\r\nElementum eu facilisis sed\r\nodio morbi quis. Eget est lorem ipsum\r\ndolor sit. Curabitur vitae nunc sed velit \r\ndignissim sodales ut eu. \r\nAc turpis egestas sed tempus urna et pharetra pharetra. ', 'en', 'utente', '2024-01-08 11:13:54', NULL),
(5, 'Testo scurrile', 'Episodio Y', 'Questa intro sarà segnalata\r\n\r\n####?!####', 'it', 'Maleducato', '2024-01-15 10:25:22', NULL),
(6, 'Altra intro da cancellare', 'Episodio Z', 'Altro testo scurrile\r\n\r\nLorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.', 'en', 'Maleducato', '2024-01-15 10:34:10', NULL),
(7, 'Back to the Past', '1955', 'I\'m sure that in 1985, plutonium is available at every corner drug store, but in 1955, it\'s a little hard to come by.\r\n\r\nMarty, I\'m sorry, but I\'m afraid you\'re stuck here. Huh? Um, well it\'s a delorean, right? Cause, George, she wants to go to the dance with you, she just doesn\'t know it yet. \r\n\r\nThat\'s why we got to show her that you, George McFly, are a fighter. You\'re somebody who\'s gonna stand up for yourself, someone who\'s gonna protect her. Well that\'s your name, isn\'t it?', 'en', 'utente', '2024-01-15 10:41:38', '2024-01-15 12:55:37'),
(8, 'Pirate Ipsum', 'Episode Yo oh', 'Grapple crow\'s nest lee skysail dead men tell no tales hearties interloper gunwalls me gangway. Loot lad hogshead lee swab hearties grapple overhaul wherry flogging.\r\n\r\nShiver me timbers tack flogging Gold Road fire ship log heave down Corsair brigantine careen. Lee cackle fruit driver belay grapple scourge of the seven seas landlubber or just lubber rope\'s end aft Pieces of Eight.\r\n\r\nHandsomely rope\'s end gangway hardtack carouser chase long clothes provost nipper ho. Transom lateen sail run a rig tackle booty trysail square-rigged squiffy draught handsomely.\r\n\r\nHaul wind prow grog Sink me sutler chase rope\'s end knave fathom carouser. Deadlights topsail ye list barque bilge gally cackle fruit hang the jib jury mast.', 'en', 'utente', '2024-01-15 10:44:41', NULL);


INSERT INTO `login` (`User`, `When`, `Ip`, `Device`) VALUES
('admin', '2024-01-07 22:10:34', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0'),
('utente', '2024-01-08 11:11:01', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0');

INSERT INTO `report` (`ID`, `Opening`, `Text`, `Creation`, `Viewed`, `Problematic`) VALUES
(1, 2, 'boh', '2024-01-14 13:13:15', b'1', b'0'),
(2, 5, 'Linguaggio inaccettabile', '2024-01-15 10:28:17', b'0', b'0');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

COMMIT;
