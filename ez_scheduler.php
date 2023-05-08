<head>
  <title>Bootstrap 5 Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<div class="container-fluid p-3 bg-primary text-white text-center mb-3">
	<h3>IECS課程檢索系統</h3>
</div>

<a href="ez_index.php" class="btn btn-primary mb-2">重新查詢</a>

<form name="form_timetable" method="post" action="ez_scheduler.php" >
Please insert 希望退選的課程代碼: <input name="HopeDrop" required>
<input type="submit" value="Drop">
</form>

<style>
	table, thead{
		justify-content: center;
		text-align: center;
		margin: auto;
		border: solid 1px;
		border-collapse: collapse;
		padding: auto;
        margin-bottom: 30px;
	}

	tr, th,td {
		justify-content: center;
		text-align: center;
		margin: auto;
		border: solid 1px;
		border-collapse: collapse;
		padding: 5px;
	}

    h2 {
        text-align: center;
		margin bottom: 3em;
    }
    
</style>

<?php
    error_reporting(0);

    $dbhost = '127.0.0.1';
    $dbuser = 'gigang_user';
    $dbpass = 'test1234';
    $dbname = 'ez';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
    mysqli_query($conn, "SET NAMES 'utf8'");
    mysqli_select_db($conn, $dbname);
    //

    //TimeTableNumber(input) = drop하고 싶은 課程代碼
    if(isset($_POST['TimeTableNumber'])) {
        $TimeTableNumber=$_POST["TimeTableNumber"];

        // student + class = 可以找class_id
        $stu_class_query = "SELECT * FROM student
                            LEFT JOIN class ON student.class_id = class.class_id
                            WHERE account_id= '$TimeTableNumber'";
        $stu_class_query_result = mysqli_query($conn, $stu_class_query) or die('MySQL query error');
        $stu_class_arr = mysqli_fetch_array($stu_class_query_result);
            
       // input의 class_id, student_id, class_name 저장 
        $stu_class = $stu_class_arr['class_id'];
        $stu_key = $stu_class_arr['student_id'];
        $stu_class_name = $stu_class_arr['class_name'];
        //

        // 입력한 學號랑 같은 반 수업의 必修課
        $S_require_query = "SELECT * FROM course WHERE class_id = '$stu_class' AND require_elective = '必修'";
        $S_require_query_result = mysqli_query($conn, $S_require_query) or die('MySQL query error');
        //

        // regigster 테이블에 input이 있는지 없는지
        $register_query = "SELECT * FROM register
                            LEFT JOIN course ON register.course_id = course.course_id
                            WHERE student_id='$stu_key'";

        $register_query_result = mysqli_query($conn, $register_query) or die('MySQL query error');

        //register + course 그리고 sum(credits) for my credits
        $stu_credits_query = "SELECT SUM(credits) AS sum_credits FROM course
                                LEFT JOIN register ON course.course_id = register.course_id
                                WHERE register.student_id='$stu_key'";

        $stu_credits_query_result = mysqli_query($conn, $stu_credits_query) or die('MySQL query error');
        $stu_credits_arr = mysqli_fetch_array($stu_credits_query_result);
        $stu_credits = $stu_credits_arr['sum_credits'];

 //       echo "<p>STUDENT CREDITS: </p>"  . "$stu_credits" . "<br>";

        //my_info
        echo "<h2>My Information</h2>";
        echo "<table>";
	    echo "<thead>
            <tr>
                <th>My Student Number</th>
            </tr>
            <tr>
                <th>$TimeTableNumber</th>
            </tr>
            <tr>
                <th>My Class</th>
            </tr>
            <tr>
                <th>$stu_class_name</th>
            </tr>
            <tr>
                <th>My Credits</th>
            </tr>
            <tr>
                <th>$stu_credits</th>
            </tr>
            </thead>";
            echo "<tbody>";
    
        echo "<table>";
        echo "<thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Credits</th>
                    <th>Type</th>
                    <th>Department</th>
                    <th>Quota</th>
                    <th>Week</th>
                    <th>Time</th>
                    <th>Week</th>
                    <th>Time</th>
                </tr>
            </thead>";
        echo "<tbody>";
        // (1)register_query_result 맨처음에 같은 반 必修 insert (2)는 課表관리 --- $register_query_result=84항
        while($row = mysqli_fetch_array($register_query_result)){
            echo "<tr>";
            echo "<td>" .$row['section_id'] . "</td>";
            echo "<td>" .$row['course_name'] . "</td>";
            echo "<td>" .$row['credits'] . "</td>";
            echo "<td>" .$row['require_elective'] . "</td>";
            echo "<td>" .$row['department'] . "</td>";
            echo "<td>" .$row['amount_now'] . "/".$row['amount_limit'] . "</td>";
            
            $I_want_to_go_home= $row['course_id'];
            
            $course_time_query = "SELECT * FROM time_table
                                LEFT JOIN course ON time_table.course_id = course.course_id
                                WHERE time_table.course_id = '$I_want_to_go_home'";

            $course_time_query_result = mysqli_query($conn, $course_time_query) or die('MySQL query error');
            while($row = mysqli_fetch_array($course_time_query_result)){
                echo "<td>" .$row['time_date']. "</td>";
                if($row['time_start']==$row['time_end']){
                    echo "<td>" .$row['time_start']. "</td>";
                }
                else{
                    echo "<td>" .$row['time_start'] . '~' . $row['time_end'] . "</td>";
                } 
            }
            echo "</tr>";
        }
        echo "<h2>My Timetable</h2>";
    }


    if(isset($_POST['HopeDrop'])) { 
        $HopeDrop=$_POST["HopeDrop"];

        $HopeDrop_query = "SELECT * FROM register
                            LEFT JOIN course ON register.course_id = course.course_id
                            WHERE section_id = '$HopeDrop'";

        // input的course_id, section_id
        $HopeDrop_query_result = mysqli_query($conn, $HopeDrop_query) or die('MySQL query error');
        $drop_check1_arr = mysqli_fetch_array($HopeDrop_query_result);
        $register_course_id = $drop_check1_arr['course_id'];
        $register_section_id = $drop_check1_arr['section_id'];
        //

        
        //register안에 course의 sum(credits)
        $drop_check2_query = "SELECT SUM(credits) AS sum_credits2 FROM register
                                LEFT JOIN course ON register.course_id = course.course_id";
        //

        //
        $drop_check2_query_result = mysqli_query($conn, $drop_check2_query) or die('MySQL query error');
        $drop_check2_arr = mysqli_fetch_array($drop_check2_query_result);  
        $stu_credits = $drop_check2_arr['sum_credits2']; 
        //

        //course 테이블 안에서 내가 입력하는 課程代碼랑 같은 수업
        $drop_course_query = "SELECT * FROM course WHERE section_id= '$HopeDrop'";
        //

        //$drop_course_credit register的sum(credits)-希望drop的credit <9 不能退選 ///// $drop_course_class, $drop_course_status = course的class 比較 學生的class 218항
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
        $stu_drop_class = $drop_require_query_arr['class_id']; // 208항이랑 비교하기 위해서
        //

        // $stu_credits = register的credit 189,196 //// drop_course_credit 課程代買(input) 200,206
        if($HopeDrop != $register_section_id){
            echo "<h2>不好意思，課表上沒有這門課，再重新做一下</h2>";
        }
            

        else if($stu_credits-$drop_course_credit<9){
            echo "<h2>Students can't be credits under 9</h2>";
        }

        else if($drop_course_status=="必修" && $drop_course_class==$stu_drop_class){
            echo "<h2>Students can't drop the require course</h2>";
        }

        else if($register_section_id == $HopeDrop) {
            $drop_query = "DELETE FROM register WHERE course_id = '$register_course_id'";
            $drop_query_Result = mysqli_query($conn, $drop_query) or die('MySQL query error');
            echo "<h2>Drop Success!</h2>";
        }
    }
?>
