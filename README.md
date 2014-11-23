lsbc
====

Financial Application Database System (FADS) for Living Sanctuary Brethren Church


This is a muti-user management system based on web server and mysql server implemented by PHP.

There are three types of users: super user, administrator and system user.

There are two kinds of operations, user related operation and financial application (FA) related operation.
User related operations include create user, view user, edit user, delete user.
Financial application related operation include create FA, search FA, view FA, edit FA, delete FA, generate report, issue disbursement.

Super users have authority to do all the operations.
Administrators have authority to do all the financial application related operations.
System users only have authority to create FA and view FA.
