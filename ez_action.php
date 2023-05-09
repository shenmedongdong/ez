<head>
  <title>Bootstrap 5 Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
	table, thead{
		justify-content: center;
		text-align: center;
		margin: auto;
		border: solid 2px;
		border-collapse: collapse;
		padding: auto;
	}

	tr, th,td {
		justify-content: center;
		text-align: center;
		margin: auto;
		border: solid 2px;
		border-collapse: collapse;
		padding: 5px;
	}
</style>

<div class="container-fluid p-3 bg-primary text-white text-center mb-3">
	<h3>IECS課程檢索系統</h3>
</div>

<?php
	error_reporting(0);
	
	//連接 data server
	$dbhost = '127.0.0.1';
	$dbuser = 'gigang_user';
	$dbpass = 'test1234';
	$dbname = 'ez';
	$conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
	mysqli_query($conn, "SET NAMES 'utf8'");
	mysqli_select_db($conn, $dbname);
	
	//讀
	if(isset($_POST['StudentAccount'])) {
		$StudentAccount=$_POST["StudentAccount"];

		// StudentAccount(input)은 account_id, student와 class를 join해서 input에 해당하는 class_id랑 student_id를 찾음
		$stu_class_query = "SELECT * FROM student
							LEFT JOIN class ON student.class_id = class.class_id
							WHERE account_id = '$StudentAccount'";
		$stu_class_query_result = mysqli_query($conn, $stu_class_query) or die('MySQL query error');
		$stu_class_arr = mysqli_fetch_array($stu_class_query_result);
			
		$stu_class = $stu_class_arr['class_id'];
		$stu_key = $stu_class_arr['student_id'];
		//

		// course table에서 class_id가 stu_class(input의 class_id)와 같고 必修課인 수업을 SELECT --- input의 class에 따라 要選必修課
		$S_require_query = "SELECT * FROM course WHERE class_id = '$stu_class' AND require_elective = '必修'";
		$S_require_query_result = mysqli_query($conn, $S_require_query) or die('MySQL query error');
		//

		// register table에 input의 student_id가 있는지 없는지 확인
		$register_query = "SELECT * FROM register
							LEFT JOIN course ON register.course_id = course.course_id
							WHERE student_id='$stu_key'";
		$register_query_result = mysqli_query($conn, $register_query) or die('MySQL query error'); //없음
		//

		//만약에 input의 student_id가 register에 없다면 stu_key랑 require_temp['course_id'] 추가 --- stu_key=input(學號), require_temp['course_id']=61항
		if(mysqli_num_rows($register_query_result)==0){
			while($require_temp = mysqli_fetch_array($S_require_query_result)){
				$add_query = "INSERT INTO register(student_id,course_id) VALUES ('$stu_key','" . $require_temp['course_id'] . "')";
				$add_query_result = mysqli_query($conn, $add_query) or die('MySQL query error');
			}
		}
	}	
?>

<a href="ez_index.php" class="btn btn-primary mb-2">重新查詢</a>

<form name="form_timetable" method="post" action="ez_scheduler.php" >
Please insert student number: <input name="TimeTableNumber" value="<?php echo $StudentAccount;?>" required>
<input type="submit" value="Timetable">
</form>

<form name="form3" method="post" action="ez_action.php">
Please insert student number: <input name="studentid" value="<?php echo $StudentAccount;?>"required>
Please insert course number: <input name="addcourseid"required>
<input type="submit" value="Add" name="add">
</form>

<form name="form_search" method="post" action="ez_action.php" style="margin-bottom: 30px" >
Please insert course name or number: <input name="SectionNumber">
<input type="submit" value="Search">
</form>

<?php

	//만약에 input값이 null이 아니라면, course全部列出來(選課目錄), 처음에는 echo 테이블 헤드(th)
	if($StudentAccount!=NULL){
	$gigang_temp_query = "SELECT * FROM course";
	$gigang_temp_query_result =  mysqli_query($conn, $gigang_temp_query) or die('MySQL query error');
	$gigang_temp_arr = mysqli_fetch_array($gigang_temp_query_result);
	
	$count_course_query = "SELECT count(course_id) AS cnt_course FROM course";
	$count_course_query_result =  mysqli_query($conn, $count_course_query) or die('MySQL query error');
	$count_course_query_arr = mysqli_fetch_array($count_course_query_result);
	
	echo "IECS課程 : " . $count_course_query_arr['cnt_course'] . "筆" . "<br>";
	echo "<br>";

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
	//만약에 input값이 null이 아니라면, course全部列出來(選課目錄), 처음에는 echo 테이블 데이터(td)
	while($row = mysqli_fetch_array($gigang_temp_query_result)){
		echo "<tr>";
		echo "<td>" .$row['section_id'] . "</td>";
		echo "<td>" .$row['course_name'] . "</td>";
		echo "<td>" .$row['credits'] . "</td>";
		echo "<td>" .$row['require_elective'] . "</td>";
		echo "<td>" .$row['department'] . "</td>";
		echo "<td>" .$row['amount_now'] . "/".$row['amount_limit'] . "</td>";
		
		$I_want_to_go_home= $row['course_id'];
		
		// time_table + course
		$course_time_query = "SELECT * FROM time_table
							LEFT JOIN course ON time_table.course_id = course.course_id
							WHERE time_table.course_id = '$I_want_to_go_home'";

		$course_time_query_result = mysqli_query($conn, $course_time_query) or die('MySQL query error');

		//time_table의 time_date, time_start, time_end 列出來
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
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>



<?php


	
	//course search
	// 課程代碼, 課程名字 -> 課程
	if(isset($_POST['SectionNumber'])) {
		$SectionNumber=$_POST["SectionNumber"];
	
		if($SectionNumber==null){
			$gigang_temp_query = "SELECT * FROM course";
			$gigang_temp_query_result =  mysqli_query($conn, $gigang_temp_query) or die('MySQL query error');
			echo "<table>";
			echo "<thead><tr><th>code</th><th>Name</th><th>Credit</th><th>Type</th><th>Department</th><th>Quota</th><th>Time</th></tr></thead>";
			echo "<tbody>";
				while($row = mysqli_fetch_array($gigang_temp_query_result)){
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
		}
		//if
		$SECTION_NUMBER = "SELECT * FROM course WHERE section_id = '$SectionNumber'";
		$SECTION_NUMBER_QUERY =  mysqli_query($conn, $SECTION_NUMBER) or die('MySQL query error');
		if(mysqli_num_rows($SECTION_NUMBER_QUERY)!=0){

			echo "<table>";
			echo "<thead><tr><th>code</th><th>Name</th><th>Credit</th><th>Type</th><th>Department</th><th>Quota</th><th>Time</th></tr></thead>";
			echo "<tbody>";
				while($row = mysqli_fetch_array($SECTION_NUMBER_QUERY)){
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
			}
			else{

			
		//else if
		$COURSE_NAME = "SELECT * FROM course WHERE course_name = '$SectionNumber'";
		$COURSE_NAME_QUERY = mysqli_query($conn, $COURSE_NAME) or die('MySQL query error');
		
			echo "<table>";
			echo "<thead><tr><th>code</th><th>Name</th><th>Credit</th><th>Type</th><th>Department</th><th>Quota</th><th>Time</th></tr></thead>";
			echo "<tbody>";
				while($row = mysqli_fetch_array($COURSE_NAME_QUERY)){
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
			}
	}
	//add course
	if(isset($_POST['addcourseid']) && isset($_POST['studentid'] ) && isset($_POST['add'])){

		$addcourseid=$_POST["addcourseid"];
		$StudentAccount=$_POST["studentid"];
		//$test_full_amount = "INSERT INTO register(student)"
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

