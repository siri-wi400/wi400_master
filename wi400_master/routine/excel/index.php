<?php
require_once 'Writer.php';

// We give the path to our file here
$workbook = new Spreadsheet_Excel_Writer('test.xls');

$worksheet = $workbook->addWorksheet('My first worksheet');


$format_vertical = $workbook->addFormat();
$format_vertical->setTextRotation(270);


$worksheet->freezePanes(array(0, 1));

$worksheet->write(0, 0, 'Name',$format_vertical);
$worksheet->write(0, 1, 'Age',$format_vertical);
$worksheet->write(1, 0, 'John Smith');
$worksheet->write(1, 1, 30);
$worksheet->write(2, 0, 'Johann Schmidt');
$worksheet->write(2, 1, 31);
$worksheet->write(3, 0, 'Juan Herrera');
$worksheet->write(3, 1, 32);

// We still need to explicitly close the workbook
$workbook->close();
?> 