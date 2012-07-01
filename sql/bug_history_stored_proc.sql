-- Modified phpMyAdmin SQL Dump

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `minibugz`
--

-- --------------------------------------------------------

--
-- stored procedure to:
--   if a bug's status changes,
--     * move its current status and timestamp to bug_history table
--     * set the bug's status_last_modified to NOW()
-- Lifted from http://stackoverflow.com/questions/779230/using-mysql-triggers-to-log-all-table-changes-to-a-secondary-table?rq=1
--

DELIMITER $$

DROP TRIGGER IF EXISTS `update_bug_history`$$

CREATE TRIGGER `update_bug_history` BEFORE UPDATE ON `bugs`
FOR EACH ROW
BEGIN
    IF (NEW.status_id != OLD.status_id) THEN
        INSERT INTO `bug_history`
                (`bug_id`, `modified`, `status_id`)
        VALUES 
                (OLD.bug_id, OLD.status_last_modified, OLD.status_id);
        SET NEW.status_last_modified = CURRENT_TIMESTAMP;
    END IF;
END$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
