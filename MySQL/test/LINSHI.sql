/* FUNCTION structure for FUNCTION `my_fun` */
/*!50003 DROP FUNCTION IF EXISTS  `my_fun` */;
DELIMITER $$ 

/*!50003 CREATE FUNCTION my_fun_SUM(a INT(2),b INT(2))
RETURNS INT(4)
BEGIN
DECLARE sum_ INT(2) DEFAULT 0;
SET sum_ = a + b;
RETURN sum_ ;
END */$$
DELIMITER ;