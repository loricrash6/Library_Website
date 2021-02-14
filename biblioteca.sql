DROP DATABASE IF EXISTS `biblioteca`;
CREATE DATABASE `biblioteca` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_cs;
USE `biblioteca`;

#
# table structure for table 'users'
#
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` VARCHAR(6) NOT NULL,
  `pwd` VARCHAR(8) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB;

#
# data for table 'users'
#
INSERT INTO `users` (`username`, `pwd`) VALUES
('ada76', 'Rossi'),
('%2018', 'vivaPWR'),
('', 'NoPass');

#
# table structure for table 'books'
#
DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `autori` VARCHAR(64) NOT NULL,
  `titolo` VARCHAR(64) NOT NULL,
  `prestito` VARCHAR(6),
  `data` DATETIME,
  `giorni` INT UNSIGNED,
  PRIMARY KEY (`id`, `prestito`),
  KEY `fk_books_users` (`prestito`),
  CONSTRAINT `fk_books_users` FOREIGN KEY (`prestito`) REFERENCES `users` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

#
# data for table 'books'
#
INSERT INTO `books` (`id`, `autori`, `titolo`, `prestito`, `data`, `giorni`) VALUES
(1, 'Dante Alighieri', 'La Divina Commedia', 'ada76', '2019-06-03 23:30:00', 45),
(2, 'Alessandro Manzoni', 'I promessi sposi', 'ada76', '2019-06-01 15:00:00', 10),
(3, 'Robin Nixon', 'Learning PHP, MySQL, JavaScript, CSS & HTML5', '%2018', '2019-06-02 08:00:57', 3),
(4, 'Ronald L. Krutz, Russell Dean Vines', 'Cloud security', '', '', 0),
(5, 'Brian W. Kernighan, Dennis M. Ritchie', 'The C programming language', '', '', 0);

#
# Permessi username: uReadOnly; pwd: posso_solo_leggere (solo SELECT)
#
GRANT USAGE ON `biblioteca`.* TO 'uReadOnly'@'localhost' IDENTIFIED BY PASSWORD '*0FBF5C395B1E6B971E9CBB18F95041B49D0B0947';
GRANT SELECT ON `biblioteca`.* TO 'uReadOnly'@'localhost';

#
# Permessi username: uReadWrite; pwd: SuperPippo!!! (solo SELECT, INSERT, UPDATE)
#
GRANT USAGE ON `biblioteca`.* TO 'uReadWrite'@'localhost' IDENTIFIED BY PASSWORD '*400BF58DFE90766AF20296B3D89A670FC66BEAEC';
GRANT SELECT, INSERT, UPDATE ON `biblioteca`.* TO 'uReadWrite'@'localhost';
