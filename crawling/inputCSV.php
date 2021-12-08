<?php

    include 'src/connection/connection.php';
    // error_reporting(0);
    if(isset($_POST["submit"]))
    {
        if($_FILES['file']['name'])
        {
            $filename = explode(".", $_FILES['file']['name']);
            if($filename[1] == 'csv')
            {

                $handle = fopen($_FILES['file']['tmp_name'], "r");
                while($data = fgetcsv($handle))
                {
                    $tweets                 = mysqli_real_escape_string($connect, $data[1]);
                    $username               = mysqli_real_escape_string($connect, $data[2]);
                    $isPositive             = mysqli_real_escape_string($connect, $data[3]);  

                    if($tweets !== 'content'){
                        $query = "INSERT INTO tweets (id_tweets, id_username, username, tweets, simil1, simil2, simil3) 
                        values 
                        (null, '', '$username','$tweets','$isPositive','$isPositive','$isPositive')";
                        mysqli_query($connect, $query);
                    }

            }
            }
        }
    }

header("location: evaluasi.php");    