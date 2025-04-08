<?php
header("Content-type: text/css");
?>

/* Global Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
    color: #333;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Header */
.header {
    background-color: #2c3e50;
    color: #fff;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.logo {
    width: 120px;
}

/* Navigation */
.nav a {
    color: #ecf0f1;
    margin: 0 15px;
    font-size: 16px;
    transition: color 0.3s ease;
}

.nav a:hover {
    color: #bdc3c7;
}

/* Content Area */
.content {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Table Styles */
.table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}

.table th,
.table td {
    border: 1px solid #ddd;
    padding: 12px 15px;
    text-align: left;
}

.table th {
    background-color: #3498db;
    color: #fff;
}

/* Buttons */
.button {
    background-color: #3498db;
    border: none;
    border-radius: 4px;
    color: #fff;
    padding: 10px 18px;
    margin: 5px 0;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.button:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
}

/* Input Fields and Textareas */
input[type="text"],
input[type="password"],
input[type="date"],
input[type="time"],
textarea,
select {
    width: 100%;
    padding: 10px 12px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

/* Login Form */
.login-form {
    width: 320px;
    margin: 80px auto;
    background: #fff;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    right: -450px;
    width: 350px;
    height: 100%;
    background: #fff;
    box-shadow: -3px 0 8px rgba(0, 0, 0, 0.2);
    transition: right 0.4s ease;
    padding: 20px;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar.active {
    right: 0;
}

/* Sidebar Form Headings */
.sidebar h3 {
    margin-top: 0;
    color: #2c3e50;
}

/* Staff & Student Info in Header */
.staff-info, .student-info {
    display: flex;
    align-items: center;
}

.staff-info img,
.student-info img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 10px;
}

/* Homework Buttons Example */
.hw-buttons {
    margin: 20px 0;
}

.hw-buttons .button {
    margin-right: 10px;
}
