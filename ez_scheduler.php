<a href = "ez_index.php"> Go Query Interface</a> <p>

<form name="form_timetable" method="post" action="ez_scheduler.php" >
Please insert 希望退選的課程的代碼: <input name="HopeDrop">
<input type="submit" value="DROP">
</form>

<?php
    $dbhost = '127.0.0.1';
    $dbuser = 'gigang_user';
    $dbpass = 'test1234';
    $dbname = 'ez';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
    mysqli_query($conn, "SET NAMES 'utf8'");
    mysqli_select_db($conn, $dbname);
    //

    if(isset($_POST['TimeTableNumber'])) {
        $TimeTableNumber=$_POST["TimeTableNumber"];

        // SELECT student的資料, 用StudentAccount(input)
        $stu_class_query = "SELECT * FROM student
                            LEFT JOIN class ON student.class_id = class.class_id
                            WHERE account_id= '$TimeTableNumber'";
        $stu_class_query_result = mysqli_query($conn, $stu_class_query) or die('MySQL query error');
        $stu_class_arr = mysqli_fetch_array($stu_class_query_result);
            
        $stu_class = $stu_class_arr['class_id'];
        $stu_key = $stu_class_arr['student_id'];
        $stu_class_name = $stu_class_arr['class_name'];
        //

        //print student的 account, class
        echo "<p>STUDENT NUMBER: </P>" . "$TimeTableNumber" ."<br>";
        echo "<p>STUDENT CLASS: </P>" . "$stu_class_name" ."<br>";
        //

        // SELECT StudentAccount的class_id & 必修
        $S_require_query = "SELECT * FROM course WHERE class_id = '$stu_class' AND require_elective = '必修'";
        $S_require_query_result = mysqli_query($conn, $S_require_query) or die('MySQL query error');
        //

        // SELECT 在register(table)裡面, 跟StudentAccount(input)一樣
        $register_query = "SELECT * FROM register
                            LEFT JOIN course ON register.course_id = course.course_id
                            WHERE student_id='$stu_key'";

    
        $register_query_result = mysqli_query($conn, $register_query) or die('MySQL query error');
        //

        //print student的 credits
        $stu_credits_query = "SELECT SUM(credits) AS sum_credits FROM course
                                LEFT JOIN register ON course.course_id = register.course_id
                                WHERE register.student_id='$stu_key'";

        $stu_credits_query_result = mysqli_query($conn, $stu_credits_query) or die('MySQL query error');
        $stu_credits_arr = mysqli_fetch_array($stu_credits_query_result);
        $stu_credits = $stu_credits_arr['sum_credits'];

        echo "<p>STUDENT CREDITS: </p>"  . "$stu_credits" . "<br>";
        //

        //print input的class_id = course_id & 必修課
        while($row = mysqli_fetch_array($register_query_result)){
            echo $row['section_id'] . " ";
            echo $row['course_name'] . " ";
            echo $row['credits'] . " ";
            echo $row['require_elective'] . " ";
            echo $row['department'] ." ";
            echo $row['amount_now'] . '/' . $row['amount_limit'] . ' ' ;
           $I_want_to_go_home= $row['course_id'];

           $course_time_query = "SELECT * FROM time_table
                                LEFT JOIN course ON time_table.course_id = course.course_id
                                WHERE time_table.course_id = '$I_want_to_go_home'";
        
            $course_time_query_result = mysqli_query($conn, $course_time_query) or die('MySQL query error');
            while($row = mysqli_fetch_array($course_time_query_result)){
                echo $row['time_date'].'  ';
                if($row['time_start']==$row['time_end']){
                    echo $row['time_start'];
                }
                else{
                    echo $row['time_start'] . '~' . $row['time_end'] . '   ';
                }
                
            }
            echo "<br>";
        }
        
        //

        //
        // $course_time_query = "SELECT * FROM time_table
        //                         LEFT JOIN course ON time_table.course_id = course.course_id
        //                         WHERE time_table.course_id = '$I_want_to_go_home'";
        
        // $course_time_query_result = mysqli_query($conn, $course_time_query) or die('MySQL query error');
        // while($row = mysqli_fetch_array($course_time_query_result)){
        //     echo $row['time_start'] . '~' . $row['time_end'];
        // }
    }


    if(isset($_POST['HopeDrop'])) { 
        $HopeDrop=$_POST["HopeDrop"];
        //
        $HopeDrop_query = "SELECT * FROM register
                            LEFT JOIN course ON register.course_id = course.course_id
                            WHERE section_id = '$HopeDrop'";
        //

        //
        $HopeDrop_query_result = mysqli_query($conn, $HopeDrop_query) or die('MySQL query error');
        $drop_check1_arr = mysqli_fetch_array($HopeDrop_query_result);
        $register_course_id = $drop_check1_arr['course_id'];
        $register_section_id = $drop_check1_arr['section_id'];
        //

        
        //
        $drop_check2_query = "SELECT SUM(credits) AS sum_credits2 FROM register
                                LEFT JOIN course ON register.course_id = course.course_id";
        //

        //
        $drop_check2_query_result = mysqli_query($conn, $drop_check2_query) or die('MySQL query error');
        $drop_check2_arr = mysqli_fetch_array($drop_check2_query_result);  
        $stu_credits = $drop_check2_arr['sum_credits2']; 
        //

        //
        $drop_course_query = "SELECT * FROM course WHERE section_id= '$HopeDrop'";
        //

        //
        $drop_course_query_result = mysqli_query($conn, $drop_course_query) or die('MySQL query error');
        $drop_course_query_arr = mysqli_fetch_array($drop_course_query_result);
        $drop_course_credit = $drop_course_query_arr['credits'];
        $drop_course_status = $drop_course_query_arr['require_elective'];
        $drop_course_class = $drop_course_query_arr['class_id'];
        //

        //
        $drop_require_query = "SELECT * FROM register
                            LEFT JOIN course ON register.course_id = course.course_id
                            LEFT JOIN student ON register.student_id = student.student_id
                            WHERE section_id = '$HopeDrop'";
        $drop_require_query_result = mysqli_query($conn, $drop_require_query) or die('MySQL query error');
        $drop_require_query_arr = mysqli_fetch_array($drop_require_query_result);
        $stu_drop_class = $drop_require_query_arr['class_id'];
        //

        if($stu_credits-$drop_course_credit<9){
            echo "Students can't be credits under 9";
        }

        else if($drop_course_status=="必修" && $drop_course_class==$stu_drop_class){
            echo "Students can't drop the reqiure course";
        }

        else if($register_section_id == $HopeDrop) {
            $drop_query = "DELETE FROM register WHERE course_id = '$register_course_id'";
            $drop_query_Result = mysqli_query($conn, $drop_query) or die('MySQL query error');
            echo "Drop Success!";
        }
    }

?>