[Profile]
ID=WS
Version=9
[CT]
trace=Y
[Telnet5250]
HostName=##HOST##
AssociatedPrinterStartMinimized=N
AssociatedPrinterClose=N
AssociatedPrinterTimeout=0
Security=CA400
SSLClientAuthentication=Y
CertSelection=AUTOSELECT
[Communication]
Link=telnet5250
Session=5250
[5250]
ScreenSize=27x132
HostCodePage=1144-I
WorkStationID=##ID_SESSIONE##
PrinterType=IBM3812
[Keyboard]
CuaKeyboard=2
Language=Italy(142)
IBMDefaultKeyboard=N
DefaultKeyboard=##KEYBOARD_FILE##
[LastExitView]
A=3 72 50 1056 759 3 13 26 400 0 IBM3270— 1144
[Window]
ViewFlags=CE00
CaptionFormat=AC -
RuleLinePos=0 0
UserTitle=##USER_TITLE##
