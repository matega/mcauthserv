CREATE TABLE `user` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200),
    `pass` CHAR(60),
    `uuid` CHAR(36),
    `selectedprofile` INT,
    `clienttoken` CHAR(36),
    `accesstoken` CHAR(36),
    `serverid` VARCHAR(41)
    );
CREATE TABLE `userprop` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `userid` INT NOT NULL,
    `name` VARCHAR(200),
    `value` VARCHAR(200)
    );
CREATE TABLE `profile` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `userid` INT NOT NULL,
    `uuid` CHAR(36),
    `name` VARCHAR(200),
    `skin` BLOB,
    `cape` BLOB,
    `slim` BOOLEAN
    );
