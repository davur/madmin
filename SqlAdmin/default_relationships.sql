ALTER TABLE `H_And_S_Return`
  DROP `H_And_S_Return_Type_ID`;

DROP TABLE `H_And_S_Report`
DROP TABLE `H_And_S_Report_Dangerous_Occurrences`;
DROP TABLE `H_And_S_Report_Injury_To_Contractors`;
DROP TABLE `H_And_S_Report_Kind_Of_Accident`;
DROP TABLE `H_And_S_Report_Occupational_Diseases`;
DROP TABLE `H_And_S_Report_Serious_Visitor_Accidents`;
DROP TABLE `H_And_S_Report_Type`;
DROP TABLE `PSPI_Report`;
DROP TABLE `PSPI_Report_COMAH`;
DROP TABLE `PSPI_Report_COMAH_LOC`;





DROP TABLE IF EXISTS `fieldinfo`;

CREATE TABLE  `fieldinfo` (
 `database_name` VARCHAR( 200 ) NOT NULL ,
 `table_name` VARCHAR( 200 ) NOT NULL ,
 `column_name` VARCHAR( 200 ) NOT NULL ,
 `position` DECIMAL( 10, 0 ) DEFAULT NULL ,
 `link` VARCHAR( 200 ) DEFAULT NULL ,
 `info` TEXT,
PRIMARY KEY (  `database_name` ,  `table_name` ,  `column_name` )
) ENGINE = MYISAM DEFAULT CHARSET = latin1;

insert into pact.fieldinfo (database_name, table_name, column_name, link)
	select table_schema, table_name, column_name, left(column_name, length(column_name) - 3) 
	from information_schema.COLUMNS
	where table_schema = 'pact' 
		and column_name <> 'Parent_ID'
		and column_name like '%_ID' 
		and not column_name like concat(table_name, '_ID')
		and column_name in (select concat(table_name, '_ID') from information_schema.COLUMNS);

insert into pact.fieldinfo (database_name, table_name, column_name, link)
	select table_schema, table_name, column_name, table_name
	from information_schema.COLUMNS
	where table_schema = 'pact' 
		and column_name = 'Parent_ID';
