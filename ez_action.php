<?php

	//連接 data server
	$dbhost = '127.0.0.1';
	$dbuser = 'gigang_user';
	$dbpass = 'test1234';
	$dbname = 'ez';
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
	mysqli_query($conn, "SET NAMES 'utf8'");
	mysqli_select_db($conn, $dbname);
	

	if(isset($_POST['StudentAccount'])) {
		$StudentAccount=$_POST["StudentAccount"];

		// SELECT student的資料, 用StudentAccount(input)
		$stu_class_query = "SELECT * FROM student
							LEFT JOIN class ON student.class_id = class.class_id
							WHERE account_id= '$StudentAccount'";
		$stu_class_query_result = mysqli_query($conn, $stu_class_query) or die('MySQL query error');
		$stu_class_arr = mysqli_fetch_array($stu_class_query_result);
			
		$stu_class = $stu_class_arr['class_id'];
		$stu_key = $stu_class_arr['student_id'];

		// SELECT StudentAccount的class_id & 必修
		$S_require_query = "SELECT * FROM course WHERE class_id = '$stu_class' AND require_elective = '必修'";
		$S_require_query_result = mysqli_query($conn, $S_require_query) or die('MySQL query error');
		//

		// SELECT 在register(table)裡面, 跟StudentAccount(input)一樣
		$register_query = "SELECT * FROM register
							LEFT JOIN course ON register.course_id = course.course_id
							WHERE student_id='$stu_key'";
		$register_query_result = mysqli_query($conn, $register_query) or die('MySQL query error'); //없음
		//

		//if 在register(table)裡面row=0, 跟StudentAccount的class_id, 必修 都加在register(table)
		if(mysqli_num_rows($register_query_result)==0){
			while($require_temp = mysqli_fetch_assoc($S_require_query_result)){
				$add_query = "INSERT INTO register(student_id,course_id) VALUES ('$stu_key','" . $require_temp['course_id'] . "')";
				$add_query_result = mysqli_query($conn, $add_query) or die('MySQL query error');
			}
		}
		//
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>



<a href = "ez_index.php"> Go Query Interface</a> <p>

<form name="form_search" method="post" action="ez_action.php" >
Please insert course name or number: <input name="SectionNumber">
<input type="submit" value="SEARCH">
</form>

<form name="form_timetable" method="post" action="ez_scheduler.php" >
Please insert student number: <input name="TimeTableNumber" value="<?php echo $StudentAccount;?>">
<input type="submit" value="TIMETABLE">
</form>

<form name="form3" method="post" action="ez_action.php" >
Please insert student number: <input name="studentid" value="<?php echo $StudentAccount;?>">
Please insert course number: <input name="addcourseid">
<input type="submit" value="Add" name="add">
</form>



<?php

		//print 選課目錄
		echo "<p>Course List: </p>";
		$course_list = "SELECT * FROM course WHERE department = 'IECS'";
			$course_list_query =  mysqli_query($conn, $course_list) or die('MySQL query error');
			while($row = mysqli_fetch_array($course_list_query)){
				echo $row['section_id'] . " ";
				echo $row['course_name'] . " ";
				echo $row['credits'] . " ";
				echo $row['require_elective'] . " ";
				echo $row['department'] . " ";
				echo $row['amount_now'] . "/".$row['amount_limit'] . "<br>";
			}
		//
	}
?>



<?php


	
	//course search
	// 課程代碼, 課程名字 -> 課程
	if(isset($_POST['SectionNumber'])) {
		$SectionNumber=$_POST["SectionNumber"];
	
		if($SectionNumber==null){
			$SECTION_NUMBER = "SELECT * FROM course";
			$SECTION_NUMBER_QUERY =  mysqli_query($conn, $SECTION_NUMBER) or die('MySQL query error');
			while($row = mysqli_fetch_array($SECTION_NUMBER_QUERY)){
				echo $row['section_id'] . " ";
				echo $row['course_name'] . " ";
				echo $row['credits'] . " ";
				echo $row['require_elective'] . " ";
				echo $row['department'] . " ";
				echo $row['amount_now'] . "/".$row['amount_limit'] . "<br>";
			}
		}
		//if
		$SECTION_NUMBER = "SELECT * FROM course WHERE section_id = '$SectionNumber'";
		$SECTION_NUMBER_QUERY =  mysqli_query($conn, $SECTION_NUMBER) or die('MySQL query error');
		while($row = mysqli_fetch_array($SECTION_NUMBER_QUERY)){
			echo $row['section_id'] . " ";
			echo $row['course_name'] . " ";
			echo $row['credits'] . " ";
			echo $row['require_elective'] . " ";
			echo $row['department'] . " ";
			echo $row['amount_now'] . "/".$row['amount_limit'] . "<br>";
		}
		//else if
		$COURSE_NAME = "SELECT * FROM course WHERE course_name = '$SectionNumber'";
		$COURSE_NAME_QUERY = mysqli_query($conn, $COURSE_NAME) or die('MySQL query error');
		while($row = mysqli_fetch_array($COURSE_NAME_QUERY)){
			echo $row['section_id'] . " ";
			echo $row['course_name'] . " ";
			echo $row['credits'] . " ";
			echo $row['require_elective'] . " ";
			echo $row['department'] . " ";
			echo $row['amount_now'] . "/".$row['amount_limit'] . "<br>";
		}
	}
	//add course
	if(isset($_POST['addcourseid']) && isset($_POST['studentid'] ) && isset($_POST['add'])){

		$addcourseid=$_POST["addcourseid"];
		$StudentAccount=$_POST["studentid"];

		// STUDENT INFO QUERY
		$S_info_query = "SELECT * FROM student WHERE account_id= '$StudentAccount'";
		$S_info_query_result = mysqli_query($conn, $S_info_query) or die('MySQL query error');
		$row = mysqli_fetch_assoc($S_info_query_result);
		$stu_class = $row['class_id'];
		$stu_key = $row['student_id'];
		$C_info_query = "SELECT * FROM course WHERE section_id= '$addcourseid'";
		$C_info_query_result = mysqli_query($conn, $C_info_query) or die('MySQL query error');
		$row = mysqli_fetch_assoc($C_info_query_result);
		$C_id = $row['course_id'];
		$C_amountnow = $row['amount_now'];
		$C_amountlimit = $row['amount_limit'];
		$C_credit = $row['credits'];
		$C_name=$row['course_name'];

		$S_credit_query = "SELECT SUM(credits) AS S_credit FROM course join register on course.course_id = register.course_id WHERE register.student_id='$stu_key'";
		$S_credit_query_result = mysqli_query($conn, $S_credit_query) or die('MySQL query error');
		$row = mysqli_fetch_assoc($S_credit_query_result);
		$S_credit = $row['S_credit'];
		
		//TIME
		$C_time_query = "SELECT * FROM time_table 
		LEFT JOIN course ON time_table.course_id = course.course_id
		where course.course_id='$C_id'";
		$flag = 0;
		$C_time_duplicate = "SELECT * FROM register join time_table on register.course_id = time_table.course_id WHERE student_id='$stu_key'";
		$C_time_duplicate_result = mysqli_query($conn, $C_time_duplicate) or die('MySQL query error');
		$C_time_duplicate2 = "SELECT * FROM register join time_table on register.course_id = time_table.course_id WHERE student_id='$stu_key'";
		$C_time_duplicate_result2 = mysqli_query($conn, $C_time_duplicate2) or die('MySQL query error');
		$C_time_query_result = mysqli_query($conn, $C_time_query) or die('MySQL query error');
		$row = mysqli_fetch_assoc($C_time_query_result);
		$C_time_date=$row['time_date'];
		$C_time_start=$row['time_start'];
		$C_time_end=$row['time_end'];
		if(mysqli_num_rows($C_time_query_result)>1){
			$row = mysqli_fetch_assoc($C_time_query_result);
			$C_time_date2=$row['time_date'];
			$C_time_start2=$row['time_start'];
			$C_time_end2=$row['time_end'];
		}

		//check time duplicate
		for($i=$C_time_start;$i<=$C_time_end;$i++){
			while($row = mysqli_fetch_array($C_time_duplicate_result)){
				if($row['time_date']==$C_time_date){
					if($row['time_start']==$i){
						$flag=1;
					}
					if($row['time_end']==$i){
						$flag=1;
					}					
				}

			}
			
		}
		if(mysqli_num_rows($C_time_query_result)>1){
		for($i=$C_time_start2;$i<=$C_time_end2;$i++){
			while($row2 = mysqli_fetch_array($C_time_duplicate_result2)){
				if($row2['time_date']==$C_time_date2){
					if($row2['time_start']==$i){
						$flag=1;
					}
					if($row2['time_end']==$i){
						$flag=1;
					}					
				}

			}
			
		}
	}



		$check_duplicate ="SELECT * FROM register WHERE student_id ='$stu_key' AND course_id='$C_id'";
		$check_duplicate_result =mysqli_query($conn, $check_duplicate) or die('MySQL query error');
		//$temp = $check_duplicate_result['section_id'];
		$check_duplicate_name ="SELECT * FROM register join course on course.course_id = register.course_id WHERE student_id ='$stu_key' AND course_name='$C_name'";
		$check_duplicate_name_result =mysqli_query($conn, $check_duplicate_name) or die('MySQL query error');

		if(mysqli_num_rows($check_duplicate_result)!=0){
			echo "You have already registered for this course";
		}
		else if($C_amountnow>=$C_amountlimit){
			echo "this course is full!";
		}
		else if($S_credit+$C_credit>30){
			echo "You have too much credits!";
		}
		else if(mysqli_num_rows($check_duplicate_name_result)!=0){
			echo "You already have the course with the same name!";
		}
		else if($flag==1){
			echo "You already have a different course in this time!";

		}

		else{
			$add_class_query = "INSERT INTO register(student_id,course_id) VALUES ('$stu_key','$C_id')";
			$add_class_query_result =mysqli_query($conn, $add_class_query) or die('MySQL query error');
			echo "class added!";
		}
	}
?>

