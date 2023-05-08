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
		border: solid 1px;
		border-collapse: collapse;
		padding: auto;
	}

	tr,th,td {
		justify-content: center;
		text-align: center;
		margin: auto;
		border: solid 2px;
		border-collapse: collapse;
		padding: 5px;
	}
</style>


<div class="container-fluid p-3 bg-primary text-white text-center mb-5">
  <h3>IECS課程檢索系統</h3>
</div>



<center>
    <form name="form_StudentNumber" method="post" action="ez_action.php">
    Please insert studnet number: <input name="StudentAccount">
    <input type="submit" value="送出">
    </form>
        
    <h4 class="mt-5" style="font-weight: bold">説明</h4>
    <table>
        <tr>
            <td style="font-size: 18px; font-weight: bold;" >注意事項</th>
        </tr>
        <tr>
            <td>請先輸入學號登記後，可以看選課目錄，加選是選課系統上要再確認您的學號，退選是可以在學生的課表處理，謝謝。</td>
        </tr>
        <tr>
            <td style="font-size: 18px; font-weight: bold;" >個人資訊</th>
        </tr>
        <tr>
            <td>課表、預選資料、考程表、缺考、補考等個人資訊，請至【逢甲人資訊服務入口】查詢。</th>
        </tr>
    </table>

</center>