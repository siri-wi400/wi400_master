<?php
// Istanzio un pò di campi standard per le routine .. faccio il lavoro subito che poi ci guadagno
$AS400_TEXT_1 = new java("com.ibm.as400.access.AS400Text", 1);
$AS400_TEXT_2 = new java("com.ibm.as400.access.AS400Text", 2);
$AS400_TEXT_3 = new java("com.ibm.as400.access.AS400Text", 3);
$AS400_TEXT_4 = new java("com.ibm.as400.access.AS400Text", 4);
$AS400_TEXT_5 = new java("com.ibm.as400.access.AS400Text", 5);
$AS400_TEXT_6 = new java("com.ibm.as400.access.AS400Text", 6);
$AS400_TEXT_7 = new java("com.ibm.as400.access.AS400Text", 7);
$AS400_TEXT_8 = new java("com.ibm.as400.access.AS400Text", 8);
$AS400_TEXT_9 = new java("com.ibm.as400.access.AS400Text", 9);
$AS400_TEXT_10 = new java("com.ibm.as400.access.AS400Text", 10);
// Campi Zoned
$AS400_DECIMAL_1_0 = new java("com.ibm.as400.access.AS400ZonedDecimal", 1, 0);
$AS400_DECIMAL_2_0 = new java("com.ibm.as400.access.AS400ZonedDecimal", 2, 0);
$AS400_DECIMAL_3_0 = new java("com.ibm.as400.access.AS400ZonedDecimal", 3, 0);
// Campi Packed
$AS400_PACKED_1_0 = new java("com.ibm.as400.access.AS400PackedDecimal", 1, 0);
$AS400_PACKED_8_0 = new java("com.ibm.as400.access.AS400PackedDecimal", 8, 0);
$AS400_PACKED_9_0 = new java("com.ibm.as400.access.AS400PackedDecimal", 9, 0);