// mySQL 串接
const mysql = require('mysql2/promise');

let db;

// 建立 MySQL pool（建議用 pool）
if(process.env.MYSQL_CONNECT_TYPE === "socket") {
  db = mysql.createPool({
    socketPath: '/var/run/mysqld/mysqld.sock',
    user: process.env.MYSQL_USER,
    password: process.env.MYSQL_PASSWORD,
    database: process.env.MYSQL_USE_DATABASE,
    charset: "utf8mb4"
  });
} else {
  db = mysql.createPool({
    host: 'localhost',
    user: process.env.MYSQL_USER,
    password: process.env.MYSQL_PASSWORD,
    database: process.env.MYSQL_USE_DATABASE,
    charset: "utf8mb4"
  });
}

module.exports = {
    db
}