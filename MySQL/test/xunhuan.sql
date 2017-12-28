use easydb;
/* Procedure structure for procedure `dowhile_say` */

/*!50003 DROP PROCEDURE IF EXISTS  `dowhile_say` */;

DELIMITER $$

/*!50003 create procedure dowhile_say(
	OUT `_COMBACK` char(20),
	IN `_dotimes` int
)
begin
declare i int;
set i = 1;
while i < _dotimes do 
set _COMBACK=i; 
set i = i +1; 
end while; 
end*/$$
DELIMITER ;
