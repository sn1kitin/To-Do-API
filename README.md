API built as a school assignment of the Software Development course.


## SETUP

Requires XAMPP with PHP 8+ and MySQL. Start Apache and MySQL from the XAMPP
Control Panel.

1. Copy this folder to `C:\xampp\htdocs\todo-api`
2. Open `http://localhost/phpmyadmin`, go to **Import**, select `schema.sql`
   and click **Go** — this creates the database, the table and three test rows
3. Open `http://localhost/todo-api/api/index.php?cmd=all`