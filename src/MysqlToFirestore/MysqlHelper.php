<?php

namespace MysqlToFirestore;

use mysqli;

// TODO : Use try catch
// TODO : Use JSON returns
// TODO : Commit Mechanism for import
// TODO : Remove connect fuction - check for connection errors 

class MysqlHelper
{
    private static function connect($server, $user, $pass, $db)
    {
        $conn = new mysqli($server, $user, $pass, $db);
        // print_r($conn);
        if ($conn->connect_error) {
            die("Connection Error : " . $conn->connect_error);
        } else {
            return $conn;
        }
    }

    public static function tables($server, $user, $pass, $db)
    {
        $conn = self::connect($server, $user, $pass, $db);
        // print_r($conn);
        if ($conn != null) {
            self::retrieve_tables($conn);
        }
        return array();
    }

    private static function retrieve_tables($conn)
    {
        $tb_name = $conn->query("SHOW TABLES");
        // print_r($tb_name);
        $tables = array();
        while ($tb = $tb_name->fetch_row()) {
            // print_r($tb);
            $tables[] = $tb[0];
        }
        return $tables;
    }

    public static function save_sql($server, $user, $pass, $db, $path, $name = "")
    {
        $name = ($name != "") ? $name : 'backup_' . $db . '_' . date('d-m-Y');
        $file = fopen($path . $name . ".sql", "w+");
        $fw = fwrite($file, self::export_database($server, $user, $pass, $db));
        if (!$fw) {
            // echo $path . $name . ".sql Failed to Save!";
            return false;
        } else {
            // echo $path . $name . ".sql Saved Successfully!";
            return true;
        }
    }

    private static function export_database($server, $user, $pass, $db)
    {
        $conn = self::connect($server, $user, $pass, $db);
        $conn->query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
        $table_sql = array();
        $db_tables = self::retrieve_tables($conn);
        foreach ($db_tables as $key => $table) {
            // print_r($table);
            $tbl_query = $conn->query("SHOW CREATE TABLE " . $table);
            // print_r($tbl_query);
            $row2 = $tbl_query->fetch_row();
            // print_r($row2);
            $table_sql[] = $row2[1];
        }
        // print_r($table_sql);
        $solid_tablecreate_sql = implode("; \n\n", $table_sql);
        // print_r($solid_tablecreate_sql);
        $all_table_data = array();
        foreach ($db_tables as $key => $table) {
            $show_field = self::view_fields($conn, $table);
            $solid_field_name = implode(", ", $show_field);
            $create_field_sql = "INSERT INTO `$table` ( " . $solid_field_name . ") VALUES \n";

            //Start checking data available
            $table_data = $conn->query("SELECT * FROM " . $table);
            if ($table_data->num_rows > 0) {
                $data_viewig = self::view_data($conn, $table);
                $splice_data = array_chunk($data_viewig, 50);
                foreach ($splice_data as $each_datas) {
                    $solid_data_viewig = implode(", \n", $each_datas) . "; ";
                    $all_table_data[] = $create_field_sql . $solid_data_viewig;
                }
            } else {
                $all_table_data[] = null;
            }
            //End checking data available
        }
        $entire_table_data = implode(" \n\n\n", $all_table_data);

        // $exported_database = $solid_tablecreate_sql . "; \n \n" . $entire_table_data;
        // print_r($exported_database);
        return $solid_tablecreate_sql . "; \n \n" . $entire_table_data;
    }

    private static function view_fields($conn, $tablename)
    {
        //Getting the fields list by table
        $all_fields = array();
        $fields = $conn->query("SHOW COLUMNS FROM " . $tablename);
        if ($fields->num_rows > 0) {
            while ($field = $fields->fetch_assoc()) {
                $all_fields[] = "`" . $field["Field"] . "`";
            }
        }
        return $all_fields;
    }

    private static function view_data($conn, $tablename)
    {
        $all_data = array();
        $table_data = $conn->query("SELECT * FROM `" . $tablename . "`");
        if ($table_data->num_rows > 0) {
            while ($t_data = $table_data->fetch_row()) {

                $per_data = array();
                foreach ($t_data as $key => $tb_data) {
                    $per_data[] = "'" . str_replace("'", "\'", $tb_data) . "'";
                }
                $solid_data = "(" . implode(", ", $per_data) . ")";
                $all_data[] = $solid_data;
            }
        }
        return $all_data;
    }

    public static function download_sql($server, $user, $pass, $db, $name = "")
    {
        $name = ($name != "") ? $name : 'backup_' . $db . '_' . date('d-m-Y');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename=' . $name . '.sql');
        echo self::export_database($server, $user, $pass, $db);
    }

    public static function import($server, $user, $pass, $db, $file_path)
    {
        $conn = self::connect($server, $user, $pass, $db);
        foreach (self::retrieve_tables($conn) as $key => $table) {
            $conn->query("DROP TABLE IF EXISTS " . $table);
        }

        $templine = '';
        // Read the entire sql file
        $lines = file($file_path);
        // Loop through each line
        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                // Perform the query
                $conn->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . $conn->error . '<br /><br />');
                // Reset temp variable to empty
                $templine = '';
            }
        }
        echo "Database imported successfully!";
        return true;
    }
}
