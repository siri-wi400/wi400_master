                     *******************************
                     *  Mocha TN5250 version 2.9   *
                     *  Copyright (C) MochaSoft    *
                     *  All Rights Reserved        *
                     *******************************
 
This is the README.TXT  file for Mocha TN5250

*** Installation ***

    - Create a catalog on the WWW-Server where Mocha TN5250 should
      be loaded from. 
    - Copy all files from this package to the new catalog

    This package includes 3 Examples of HTML pages from where
    Mocha TN5250 can be loaded. 

      tn52501.html : starts mocha TN5250 as an applet
      tn52502.html : starts mocha TN5250 as a popup window
      tn52503.html : starts mocha TN5250 as signed CAB file

    PLEASE NOTICE: AS TO A SECURITY FUNCTION IN ALL BROWSERS IT IS
    ONLY POSSIBLE FOR AN APPLET TO CREATE A CONNECTION TO THE
    WEB_SERVER FROM WHERE IT WAS DOWNLOADED. IN OTHER WORDS, THE WEB
    SERVER MUST RUN ON THE IBM HOST, OR A PROXY SOLUTION MUST BE USED.
    ONLY USING THE CAB FILE FUNCTION CAN OVERRULE THIS SECURITY LIMITATION.

*** Limitations ***

As to the java's slogan "Write Once, Test Everywhere", following
test details have been included:

Mocha TN5250 has been tested with:

Windows-95/NT 4.0:
==================

Microsoft Internet Explorer 3.0   : ok (in applet mode)
Microsoft Internet Explorer 4.0   : ok
Microsoft Internet Explorer 5.0   : ok
Netscape Communicator 4.7         : contains a lot of bugs. We will
                                    not recommend it
SUN SDK 1.3                       : ok

Macintosh (Power Mac OS 9.0):
=============================
Microsoft Communicator 4.5        : ok

The product could work with other versions/platforms but 
we cannot give support on such an issue.

To enable access to the local printer and the clipboard from an applet,
running in a Microsoft Internet Explorer 5.x, select security settings,
Java custom settings and enable:
 
  * Protected Scratch Space
  * printing

To make it possible to access an AS/400 which is not used as
the Web server for the mocha java tn5250 applet, in 
Microsoft Internet Explorer 5.x, select security settings,
Java custom settings and enable:

  * Access to all Network Addresses

If the browser is used on the internet, please notice there is a 
risk for hostile applets to access your workstation, after these
settings have been enabled.

If the signed CAB file version is use, it is possible with IE 4/5 to
overrule the security limitation.


*** Order ***
    
    Visit www.mochasoft.dk for latest information. You can order with
    Credit card or check/postal money order.

    Single User license :  25 USD
    Company License     : 250 USD

    Check also file register.txt
 


*** Documentation ***

    The full documentation is included in:

       user.html : User Guide in HTML format


*** Bugs ***

  If any bugs are observed, or ideas for improvement of the product,
  please to not hesitate to contact support@mochasoft.net. If possible
  include a trace file. (se parameter debug in the User Guide).

*** Change list ***

version 1.0:

  first version

version 1.1:
  - api file was missing

version 1.2:
  - Corrected bug for field exit and right adjust.
  - Correxted bug for Dup key
  - Corrected bug for CLear Unit All command
  - fixed problem with a field starting in position 1,1
  - A screen without cursor position commands, would not always
    define the cursor to the first input field on the screen.
  - A field cleared with FIELDEXIT was not reported to the host
  - Start Field marks overwritten with a normal character must be
    ignored. Seen for AS400 in System-36 mode.
  - Insert mode did not move cursor 1 position right.

version 1.3:
  - possible to overwrite a field with Order Repeat to Address
  - problem with save/restore function. Last field on a screen
    was not saved.
  - SOH did not clear the format table.
  - key TAB at a screen without fields

version 1.4: 971124
  - Sometimes the screen would be filled with ?, when
    a connection to the host was created.
  - Could not exit a screen with a field of type Mandatory + Bypass
  - if the host sends a signed field with MDT on and the field
    ends on non 0x0, the emulator
    would translate the field to a negative number. Some applications
    could send fields ending with charater '+'.
  - problem with save/restore screen functions.
  - MDT bit was always on at right adjust fields 

version 1.5: 971201
  - Empty Right adjusted fields were send to the host, as fields
    of spaces.
  - Handling of error line not correct
  - Possible to use normal +- in a Numeric only field.
  - A numeric only Right adjust field, returns full size of the field
  - if cursor at a non field and TAB was pressed, the cursor would jump
    to first field on the screen. Now it jumps to next field on the screen.
  - The cc operation in read commands from the host was executed before
    the user had pressed the key. In some applications the keyboard would
    therefore enter a wrong state (LOCK).
  - A save/restore screen operation with different screen sizes could
    result in a crash. Save/restore is normal used when selecting help
    screens or pressing SYSREQ/ATTN. Also other problems with save/restore has
    been fixed
  - Backspace key did not move 1 position left

Version 1.6 971210
  - fixed problem with netscape 2.x. Could not find Insets function in the Java base.
  - optimized code with Mocha Source

Version 1.7 971224
  - added new function: typeahead
  - possible to see hiden data fields (password)
  - possible to delete data at bypass fields. 
  - TAB could jump to a bypass field
  - better handling of double byte data
  - Handling of undocumented order 0x1f

Version 1.7.1
  - new price structure

Version 1.8 (980416)
  - added api function set_exit_way() and set_keyboard_mode()
 
Version 1.81 (980416)
  - added api function do_close()

Version 1.82 (980420)
  - An API application can with set_exit_way() define function
    hide() and not exit() to be called on close;

Version 1.83 (980421)
  - Change to 1.82. Use Dispose() and not hide()

Version 1.9 980616
  - added support for non legal character 0x1f in write commands!
  - Auto-enter fields did not work correct.
  - Added national ebcdic tables to the package 
  - A screen restore command without any fields, did not reset the current
    field table list. The result was unexpected fields on the screen.
  - wrong delete of last character in a field, when Field-exit or
    Field+/- were pressed
  - wrong jump to next field when typing last character in a 
    numeric only field. 
  - In some situations, the cursor was not placed at first input field
    when a new screen was displayed. Cursor was at position (1,1), and the
    user had to press TAB to continue.
  - Mandatory fill fields did require the operator to enter data
  - Restore Screen command from host would always open keyboard. The 
    result could be a wrong position of the cursor
  - Problem if AS400 program includes Magnetic Stripe Reader Features
  - If undocumented field control word 0x8280 was included in
    a resequencing field, the read response to the AS400 would be wrong
  - Auto-enter fields did not check if a Read command (42) had been send
    from the as400. As a result data returned to the host could containd SBA
    orders, which some old RPG programs could not accept
  - Read Input command on a signed field (not negative) was 1 byte short
  - Fields with format: Auto Enter and Right Adjust blank fill, 
    did not right adjust if Field-exit key was used.
  - Problem with numeric fields and field- key has been solved
  - If in error state (X-HELP in status line) and a write command was send
    from the host, tn5250 would exit the error state and open the keyboard.
  - Support for SOCKS Version 4 protocol. This protocol can be used 
    with a Telnet Proxy Server solution.
  - If the browser enables access to the clipboard it is possible to
    copy/paste data to the workstations clipboard. If running as an
    application, there is full access to the clipboard.
  - If the browser enables access to the local printer, it is possible
    in menu edit to select print...
  - New keys (woks on some browser versions):
      - Insert (toggles insert/overwrite mode) 
      - Scroll lock (mapped as default to field-)
      - Pause/Break (mapped as default to field+)
  - added test results to this readme file

version 1.9a
  - added 2 api calls:

    void goto_front()   
    void goto_back()

    These 2 calls control the window, if running as a frame

version 2.0 990518
  - Updated to mocha w32 tn5250 version 3.2 level:
  - Cursor problems have been solved
  - A Write Error code command from the AS400, could overwrite elements in
    the field table, which as a result would change fields at 
    the error line (default line 23) to bypass fields
  - If only Field-exit is used for a right adjust blank fill field, 
    the Read MDT command would return only SBA row column. Some AS400 
    programs needs blanks to work correct.
  - A field defined as AUTOENTER + FIELDEXIT REQUIRED will now
    ignore the autoenter request 
  - DUP will now also move cursor to the next field
  - A wrong WTD from the AS/400 when in prehelp error mode (X_HELP)
    will not delete the error message
  - A "Cancel Invite" at RFC 1205 level, would lock the keyboard,
    and a later WSF command could therefore get a wrong cursor
    position. 
  - Clear format table did not lock the keyboard. Some special 
    as/400 applications as telnet would have a wrong cursor position
  - a WTD command from the AS/400 with characters below 0x1f,
    which should not be send, if the AS/400 program follows the
    AS/400 manual SA21-9247-6, can result in a wrong display !.
  - Field-exit function would add spaces on a non fill field
  - Field+ key in an empty right adjust zero fill field, should display
    a field with zero characters

version 2.1 991008
  - Better handling of printer and font size.

versiom 2.2 000212
  - IE 5.0 could give an error, if the applet was running in DEMO mode.

version 2.3 000706
  - Focus problem
  - Could sometimes start as a very small window
  - Released as a jar file 
  - Tested on new release of different Web Browsers

version 2.4 010725
  - Using API in hide mode can give a crash with some Java environments

version 2.5 010801
  - fixed problem with SUN SDK 1.3 and blinking screen
  - added signed CAB file version.

version 2.6 010809
  - fixed problem with signed CAB file

version 2.7 020321
  - Added support for Euro. Notice not all font types in Java supports
    the Euro symbol. This version accept altgr e as euro 
    character

version 2.8 020423
  - added "alive" parameter option. Can force tn5250 to send keep alive
    frames to the AS/400 every 15 second.

version 2.9 020424
  - problem with API call do_connect has been corrected. Bug was included in version 2.8

*** List Of Files ***

The Mocha TN5250 package consists of the following files.

    README.TXT          Introductory information
    tn52501.html        Example of a HTML page
    tn52502.html        Example of a HTML page 
    tn52503.html        Example of a HTML page (signed CAB file) 
    user.html           User Guide in HTML format
    tn5250.ebc          EBCDIC <-> ASCII table US (ASCII file)
    ebcdic52.ita        EBCDIC <-> ASCII table Italy (ASCII file)
    ebcdic52.ger        EBCDIC <-> ASCII table Austria/Germany (ASCII file)
    ebcdic52.spa        EBCDIC <-> ASCII table Spain (ASCII file)
    ebcdic52.fr         EBCDIC <-> ASCII table French (ASCII file)
    ebcdic52.uk         EBCDIC <-> ASCII table United Kingdom (ASCII file)
    ebcdic52.fin        EBCDIC <-> ASCII table Finland/Sweden (ASCII file)
    ebcdic52.dk         EBCDIC <-> ASCII table Spain (Danish/Norway)
    tn5250.cab          Signed CAB file
    tn5250.jar          Packed JAR file
    api_tn5250.class    Binary java
    tn5250.class        Binary java 
    tnnet.class         Binary java
    tntimer.class       Binary java
    alive.class         Binary java
    tnpop.class         Binary java
    tn5250exe.class (*) Binary java
    tnvt.class          Binary java
    tnvid.class         Binary java
    keys52              Function key mapping (ASCII file)
    tn5250.cfg (*)      Configuration file (ASCII file)
    LICENSE.TXT         license agreement/warranty disclaimer
    REGISTER.TXT        order form

    (*) only used if running as stand alone java application (mode 1)

    If you did not receive all these files please contact 
    support@mochasoft.net, or check www.mochasoft.dk
