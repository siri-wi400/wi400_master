[Profile]
ID=WS
Version=5
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
[KeepAlive]
KeepAliveTimeOut=0
[Communication]
Link=telnet5250
Session=5250
[5250]
ScreenSize=27x132
HostCodePage=1144-I
WorkStationID=##ID##
BypassSignon=Y
PrinterType=IBM3812
[Keyboard]
CuaKeyboard=2
Language=Italy(141)
IBMDefaultKeyboard=N
DefaultKeyboard=##PATH##AS400.KMP
[Macro]
OpenMacro=##MACRO##.mac
[Window]
SessFlags=3CC6A
ViewFlags=CE00
IconFile=##PATH##favicon.ico 0
RuleLinePos=0 0
[LastExitView]
A=4 170 156 888 536 3 11 16 400 0 IBM3270— 1144
