<?php
function connectDB() {
    return new mysqli("localhost", "root", "", "glance_db");
}
?>
