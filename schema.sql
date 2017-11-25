CREATE TABLE `user` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(200), `pass` CHAR(60), `uuid` CHAR(36), `selectedprofile` INT, `accesstoken` CHAR(36), `serverid` VARCHAR(41));
CREATE TABLE `userprop` (`userid` INT NOT NULL, `name` VARCHAR(200), `value` VARCHAR(200));
CREATE TABLE `profile` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `uuid` CHAR(36), `name` VARCHAR(200), `skin` BLOB, `cape` BLOB);
