<?php
defined ( '_VALID_MOS' ) or die ( 'Restricted access' );

# In thống kê giải đặc biệt theo tháng
function xsMonthSpecial($year, $dataList, $length = 5) {
	$data = array ();
	if (is_array ( $dataList ))
		foreach ( $dataList as $k => $item ) {
			$key = $item->year . $item->month . $item->date;
			$data [$key] = $item;
		}
	$tet = fnGetTet ( $year );
	?>
<div class="table-l">
<table class="tbl-xs">
	<tbody>
		<tr>
			<td colspan="13" class="top">Thống kê giải đặc biệt năm <?php
	echo $year;
	?></td>
		</tr>
		<tr>
			<th class="first"><img src="images/v2011/sample/t-date-mounth.gif"
				alt="" width="57" height="46"></th>
			<th>12</th>
			<th>11</th>
			<th>10</th>
			<th>9</th>
			<th>8</th>
			<th>7</th>
			<th>6</th>
			<th>5</th>
			<th>4</th>
			<th>3</th>
			<th>2</th>
			<th class="last">1</th>
		</tr>
<?php
	for($d = 1; $d < 32; $d ++) {
		echo "<tr>";
		echo "<td class=\"t-cen first\">$d</td>";
		for($m = 12; $m > 0; $m --) {
			$key = sprintf ( '%d%02d%02d', $year, $m, $d );
			$cell = '';
			$class = '';
			$title = '';
			$date = fnGetDate ( $d, $m, $year );
			$title = sprintf ( '%02d-%02d-%d', $d, $m, $year );
			
			if ($m == 1)
				$class = 'last ';
			if ($date == 'Sun')
				$class = $class . "bg-yelow ";
			if (isset ( $data [$key] )) {
				$cell = $data [$key]->special;
				$cell = substr ( $cell, strlen ( $cell ) - $length, $length );
			}
			if (in_array ( $title, $tet )) {
				$class = $class . "tet ";
			}
			echo "<td class=\"$class\" style=\"min-width:30px;\" title=\"$title\">" . $cell . "</td>";
		} //end for $m
		echo "</tr>";
	} //end for $d
	?>        
    	</tbody>
</table>
</div>
<?php
} // func: xsMonthSpecial
# In thống kê giải đặc biệt theo tuần
function xsWeekSpecial($year, $dataList, $length = 5) {
	$data = array ();
	$dateList = array ('Thứ Hai' => '2', 'Thứ Ba' => '3', 'Thứ Tư' => '4', 'Thứ Năm' => '5', 'Thứ Sáu' => '6', 'Thứ Bảy' => '7', 'Chủ Nhật' => '8' );
	if (is_array ( $dataList ))
		foreach ( $dataList as $k => $item ) {
			$week = fnGetWeek ( $item->date, $item->month, $item->year );
			$key = sprintf ( '%d%02d%02d', $item->year, $week, $dateList [$item->day] );
			$data [$key] = $item;
		}
	$tet = fnGetTet ( $year );
	?>
<div class="table-l">
<table class="tbl-xs">
	<tbody>
		<tr>
			<td colspan="8" class="top">Thống kê giải đặc biệt năm <?php
	echo $year;
	?></td>
		</tr>
		<tr>
			<th class="first"><img src="images/v2011/sample/t-date-week.gif"
				alt="" width="57" height="46"></th>
			<th>T2</th>
			<th>T3</th>
			<th>T4</th>
			<th>T5</th>
			<th>T6</th>
			<th>T7</th>
			<th class="last">CN</th>
		</tr>
<?php
	for($w = 1; $w < 54; $w ++) {
		echo "<tr>";
		echo "<td class=\"t-cen first\">$w</td>";
		for($d = 2; $d < 9; $d ++) {
			$key = sprintf ( '%d%02d%02d', $year, $w, $d );
			$cell = '';
			$class = '';
			$title = ''; #fnGetDateWeek($w,$d,$year);
			if ($d == 1)
				$class = $class . 'last ';
			if (isset ( $data [$key] )) {
				$item = $data [$key];
				$date = fnGetDate ( $item->date, $item->month, $item->year );
				if ($date == 'Sun')
					$class = $class . "bg-yelow ";
				$cell = $item->special;
				$cell = substr ( $cell, strlen ( $cell ) - $length, $length );
				$title = sprintf ( '%02d-%02d-%d', $item->date, $item->month, $item->year );
			}
			if (in_array ( $title, $tet )) {
				$class = $class . "tet ";
			}
			echo "<td class=\"$class\" style=\"min-width:30px;\" title=\"$title\">" . $cell . "</td>";
		} //end for $m
		echo "</tr>";
	} //end for $d
	?>        
    	</tbody>
</table>
</div>
<?php
} //end func xsWeekSpecial
# In danh sách chọn tỉnh
function xsPrintSelect($contain, $index, $data) {
	echo "<select name=\"$contain\" id=\"$contain\">";
	if (is_array ( $data ))
		foreach ( $data as $item ) {
			$name = explode ( ' ', $item->name );
			unset ( $name [0], $name [1] );
			$name = implode ( ' ', $name );
			$select = ($item->id == $index) ? ' selected="selected"' : '';
			echo '<option value="' . $item->id . '" ' . $select . '>&nbsp;&nbsp;';
			echo $name;
			echo '</option>';
		}
	echo "</select>";
}
# In lịch mở thưởng
function xsPrintCalendar($data) {
	?>
<table class="tbl-list">
	<tbody>
		<tr>
			<th>Lịch mở thưởng</th>
			<th>Miền Bắc</th>
			<th>Miền Trung</th>
			<th class="last">Miền Nam</th>
		</tr>
    <?php
	foreach ( $data as $k => $item ) {
		$northern = $item->northern;
		$southern = $item->southern;
		$central = $item->central;
		$northern = implode ( '<br/>', explode ( ';', $northern ) );
		$southern = implode ( '<br/>', explode ( ';', $southern ) );
		$central = implode ( '<br/>', explode ( ';', $central ) );
		?>
    <tr <?php
		if ($k % 2 == 0)
			echo 'class="bg-blue"'?>>
			<td class="first"><strong
				<?php
		if ($k % 2 == 0)
			echo 'class="provide"'?>><?php
		echo $item->date?></strong></td>
			<td><?php
		echo $northern?></td>
			<td><?php
		echo $central?></td>
			<td class="last"><?php
		echo $southern?></td>
		</tr>
    <?php
	} //end foreach 	?>  
    </tbody>
</table>
<?php
}
# In kết quả xổ số
function xsPrintResult($data, $contain = 0) {
	if (is_object ( $data ))
		$data = ( array ) $data;
	$classDiv = array ('Miền Bắc' => 'sxmb', 'Miền Nam' => 'sxmn', 'Miền Trung' => 'sxmt' );
	$classTable = array ('Miền Bắc' => 'tbl-sxmb', 'Miền Nam' => 'tbl-list', 'Miền Trung' => 'tbl-list' );
	$classRow = array ('Miền Bắc' => 'yelow', 'Miền Nam' => 'bg-blue', 'Miền Trung' => 'bg-blue' );
	$classLeft = array ('Miền Bắc' => 'n-l', 'Miền Nam' => 'n-l1', 'Miền Trung' => 'n-l2' );
	$classRight = array ('Miền Bắc' => 'n-r', 'Miền Nam' => 'n-r1', 'Miền Trung' => 'n-r2' );
	$rowspan = array ('Miền Bắc' => 9, 'Miền Nam' => 10, 'Miền Trung' => 10 );
	$numberRow = array ('Miền Bắc' => 8, 'Miền Nam' => 9, 'Miền Trung' => 9 ); //số giải
	$plus = array ('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nice' ); //thống kê
	$award = array ('special', 'first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth' ); //giải thưởng
	$awardName = array ('Giải đặc biệt', 'Giải nhất', 'Giải nhì', 'Giải ba', 'Giải tư', 'Giải năm', 'Giải sáu', 'Giải bảy', 'Giải tám' );
	
	if ($contain) :
		?>
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<div class="northern"><span
	class="<?php
		echo $classLeft [$data ['region']];
		?>"> <span
	class="<?php
		echo $classRight [$data ['region']];
		?>"><?php
		echo $data ['name'];
		?></span>
</span></div>
</div>
<div class="pr"><strong>Ngày <?php
		echo $data ['date'] . '/' . $data ['month'] . '/' . $data ['year'];
		?></strong>(<em><?php
		echo $data ['day'];
		?></em>)
</div>
</div>
</div>
</div>
</div>

	<?php 
endif;
	?>
<div class="<?php
	echo $classDiv [$data ['region']];
	?>">
<table class="<?php
	echo $classTable [$data ['region']];
	?>">
	<tbody>
		<tr>
			<th class="giai">Giải</th>
			<th class="kq t-cen" id="result">Kết quả</th>
			<td rowspan="<?php
	echo $rowspan [$data ['region']];
	?>"
				class="spacenone">
			<table>
				<tbody>
					<tr>
						<th>Đầu</th>
						<th class="last">Đuôi</th>
					</tr>
<?php
	foreach ( $plus as $p => $item ) {
		$class = $p % 2 ? 'bggray' : '';
		echo "<tr class=\"$class\">";
		echo "<td>$p</td>";
		echo "<td class=\"last\">$data[$item]</td>";
		echo "</tr>";
	} //end foreach 	?>           
            </tbody>
			</table>
			</td>
		</tr>
		<tr>
			<td class="yelow">Giải đặc biệt</td>
			<td class="t-cen yelow"><strong><?php
	echo $data ['special'];
	?></strong></td>
		</tr>    
<?php
	for($r = 1; $r < $numberRow [$data ['region']]; $r ++) {
		$class = $r % 2 ? $classRow [$data ['region']] : 'xs';
		echo "<tr class=\"$class\">";
		echo "<td class=" . $classRow [$data ['region']] . "\">" . $awardName [$r] . "</td>";
		echo "<td class=\"t-cen\"><strong>" . $data [$award [$r]] . "</strong></td>";
		echo "</tr>";
	}
	?>    
</tbody>
</table>
</div>
<?php
} //end xsPrintResult
//In đồng hồ đếm ngược
function xsPrintClock($region, $data = '') {
	$time = array ('Miền Bắc' => '19h15', 'Miền Trung' => '17h15', 'Miền Nam' => '16h15' );
	$list = array ('Miền Bắc' => 'xsmb', 'Miền Trung' => 'xsmt', 'Miền Nam' => 'xsmn' );
	?>
<div class="xs" id="<?php
	echo $list [$region];
	?>">
<div class="form-update">
<p><strong class="status">Thời gian đến giờ quay số mở thưởng còn lại</strong></p>
<div class="deatil-datetime clearfix">
<div class="hour" id="<?php
	echo $list [$region];
	?>-hour">00</div>
<div class="minis" id="<?php
	echo $list [$region];
	?>-min">00</div>
<div class="seconds" id="<?php
	echo $list [$region];
	?>-sec">00</div>
</div>
<p class="txt">Hiện tại chưa đến giờ quay số mở thưởng.<br>
            Xổ số <?php
	echo $region;
	?> sẽ bắt đầu quay số mở thưởng vào khoảng <?php
	echo $time [$region];
	?> hàng ngày.<br>
Bạn vui lòng chờ trong ít phút.<br>
Chúng tôi sẽ tường thuật trực tiếp ngay khi bắt đầu quay giải đầu tiên.
</p>
</div>
</div>
<?php
} //xsPrintClock
//Tính thời gian đến lúc mở thưởng
function xsOpenTime() {
	$now = time ();
	$time = array ('19:16:16', '17:16:16', '16:16:16' );
	$time [0] = strtotime ( $time [0] );
	$time [1] = strtotime ( $time [1] );
	$time [2] = strtotime ( $time [2] );
	$time [0] = ($time [0] - $now) * 1000;
	$time [1] = ($time [1] - $now) * 1000;
	$time [2] = ($time [2] - $now) * 1000;
	return $time;
}
//Tính thời gian delay cập nhật dữ liệu của Client
function xsUpdateTime() {
	$time = 270000;
	$xsmb = array ('19:10:00', '19:50:00' );
	$xsmt = array ('17:10:00', '17:50:00' );
	$xsmn = array ('16:10:00', '16:50:00' );
	$date = date ( 'H:i:s' );
	if ($date < $xsmb [1] && $date > $xsmb [0]) {
		$time = 15000;
		return $time;
	}
	if ($date < $xsmt [1] && $date > $xsmt [0]) {
		$time = 15000;
		return $time;
	}
	if ($date < $xsmn [1] && $date > $xsmn [0]) {
		$time = 15000;
		return $time;
	}
	return $time;
}
//In bảng kết quả quay số
function xsPrintLive($region, $data) {
	if (is_array ( $data )) {
		$col = count ( $data );
		$award = array ('Miền Bắc' => array ('seventh', 'sixth', 'fifth', 'fourth', 'third', 'second', 'first', 'special' ), 'Miền Trung' => array ('eighth', 'seventh', 'sixth', 'fifth', 'fourth', 'third', 'second', 'first', 'special' ), 'Miền Nam' => array ('eighth', 'seventh', 'sixth', 'fifth', 'fourth', 'third', 'second', 'first', 'special' ) );
		$index = array ('Miền Bắc' => array ('Giải bảy', 'Giải sáu', 'Giải năm', 'Giải tư', 'Giải ba', 'Giải hai', 'Giải nhất', 'Giải đặc biệt' ), 'Miền Trung' => array ('Giải tám', 'Giải bảy', 'Giải sáu', 'Giải năm', 'Giải tư', 'Giải ba', 'Giải hai', 'Giải nhất', 'Giải đặc biệt' ), 'Miền Nam' => array ('Giải tám', 'Giải bảy', 'Giải sáu', 'Giải năm', 'Giải tư', 'Giải ba', 'Giải hai', 'Giải nhất', 'Giải đặc biệt' ) );
		?>

<table class="tbl-sxmb">
	<tbody>
		<tr>
			<th></th>
<?php
		foreach ( $data as $c => $item ) {
			$class = 't-cen ';
			if ($c == $col - 1)
				$class = $class . 'last ';
			{
				$name = $item ['name'];
				echo "<th class=\"$class\">$name</th>";
			}
		}
		?>    
	</tr>
<?php
		foreach ( $index [$region] as $r => $name ) {
			$class = '';
			if ($r % 2 == 1)
				$class = 'yelow';
			echo "<tr class=\"$class\">";
			echo "<td class=\"first\"><strong>$name</strong></td>";
			foreach ( $data as $c => $item ) {
				$class = 't-cen ';
				if ($c == $col - 1)
					$class = $class . 'last ';
				if (isset ( $item [$award [$region] [$r]] ) && $item [$award [$region] [$r]] != '') {
					$val = $item [$award [$region] [$r]];
					if ($region != 'Miền Bắc')
						$val = str_replace ( '-', '<br/>', $val );
					echo "<td class=\"$class\">$val</td>";
				} else {
					echo "<td class=\"$class\"><img src=\"images/v2011/sample/img-rotating.gif\" width=\"16\" height=\"16\">Vui lòng đợi...</td>";
				}
			}
			echo "</tr>";
		} //foreach index
		?> 	
</tbody>
</table>

<?php
	} //if
} // end xsPrintLive


class JXoso {
	
	/* Xem kết quả
	 * task = default */
	function showDefault($data, $calendar) {
		
		$path = 'index.php?option=com_xoso';
		?>
<script type="text/javascript" src="images/v2011/jscripts/JQ.xoso.js"></script>
<div class="tab-Lottery">
<ul class="clearfix">
	<li class="active"><a href="<?php
		echo sefRelToAbs ( $path );
		?>"><span>Xem
	Kết quả</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $path . '&task=live' );
		?>"><span>Tường
	thuật trực tiếp</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $path . '&task=open' );
		?>"><span>Lịch
	mở thưởng</span></a></li>
	<li class="last"><a
		href="<?php
		echo sefRelToAbs ( $path . '&task=statistics&from=2010&to=2011&name=1&view=month' );
		?>">
	<span>Thống kê</span> </a></li>
</ul>
</div>
<div class="banner-zs"><a href="<?php
		echo $path?>"><img
	src="images/v2011/sample/xoso.gif" alt=""></a></div>
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<div class="northern"><span class="n-l"><span class="n-r n-ror">Miền Bắc<a
	name="mienbac">&nbsp;</a></span></span> <span class="select-c"> <select
	name="xsctmb" id="xsctmb">
	<option value="1">&nbsp;Xổ số Miền Bắc</option>
</select> </span></div>
</div>
<div class="pr space1"><label>Ngày</label> <input type="text"
	value="<?php
		echo $data ['MB']->date . '/' . $data ['MB']->month . '/' . $data ['MB']->year;
		?>"
	class="txtdateor" id="xstextmb"> <img class="imgdate"
	src="images/v2011/cal.gif" alt="" id="pickerMienBac"> <span
	class="btnview"><input type="submit" value="Xem" id="xsclickmb"></span>
</div>
</div>
</div>
</div>
</div>
<?php
		xsPrintResult ( $data ['MB'], 0 );
		?>
<div class="bgyelow"><strong>Xổ số Miền Bắc</strong>: mở thưởng tất cả
các ngày trong tuần, trừ ngày lễ.</div>
<div class="banner-xs"><a href="<?php
		echo $path?>"><img
	src="images/v2011/sample/xs1.gif" alt="" width="670" height="60"></a></div>
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<div class="northern"><span class="n-l1"><span class="n-r1 n-r1or">Miền
Nam<a name="miennam">&nbsp;</a></span></span> <span class="select-c1">
                        <?php
		xsPrintSelect ( 'xsctmn', 2, $data ['TMN'] );
		?>
                        </span></div>
</div>
<div class="pr space1"><label>Ngày</label> <input type="text"
	value="<?php
		echo $data ['MN']->date . '/' . $data ['MN']->month . '/' . $data ['MN']->year;
		?>"
	class="txtdateor" id="xstextmn"> <img class="imgdate"
	src="images/v2011/cal.gif" alt="" id="pickerMienNam"> <span
	class="btnview"><input type="submit" value="Xem" id="xsclickmn"></span>
</div>
</div>
</div>
</div>
</div>
<?php
		xsPrintResult ( $data ['MN'], 0 );
		?>
<div class="bggray" id="xsttmn"><strong>Status</strong>: Đang quay</div>
<div class="banner-xs"><a href="<?php
		echo $path?>"><img
	src="images/v2011/sample/xs2.gif" alt="" width="670" height="60"></a></div>
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<div class="northern"><span class="n-l2"><span class="n-r2 n-r2or">Miền
Trung<a name="mientrung">&nbsp;</a></span></span> <span
	class="select-c2">
                        <?php
		xsPrintSelect ( 'xsctmt', 15, $data ['TMT'] );
		?>
                        </span></div>
</div>
<div class="pr space1"><label>Ngày</label> <input type="text"
	value="<?php
		echo $data ['MT']->date . '/' . $data ['MT']->month . '/' . $data ['MT']->year;
		?>"
	class="txtdateor" id="xstextmt"> <img class="imgdate"
	src="images/v2011/cal.gif" alt="" id="pickerMienTrung"> <span
	class="btnview"><input type="submit" value="Xem" id="xsclickmt"></span>
</div>
</div>
</div>
</div>
</div>
<?php
		xsPrintResult ( $data ['MT'], 0 );
		?>
<div class="bggray" id="xsttmt"><strong>Status</strong>: Đang quay</div>
<div class="presentation">
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl"><span class="dial">Lịch quay Xổ số
mở thưởng<a name="lichmothuong"></a></span></div>
</div>
</div>
</div>
<div class="xs">
		<?php
		xsPrintCalendar ( $calendar );
		?>
    </div>
</div>

<?php
	
	} //showResult
	

	/* Tường thuật trực tuyến
	 * task = live 
	 * mode = view || update 
	 * update chỉ send bảng kết quả về client */
	function showLive($mode, $data, $calendar) {
		
		$path = 'index.php?option=com_xoso';
		
		$time = xsOpenTime ();
		
		if ($mode == 'view') {
			
			?>

<script type="text/javascript" language="javascript">

	timeOut = 5000;	
	timer = setTimeout("update()",timeOut);	
	
	Tmb  = <?php
			echo $time [0];
			?>;
	Tmt  = <?php
			echo $time [1];
			?>;
	Tmn  = <?php
			echo $time [2];
			?>;

	ajax = 'ajax.php?option=com_xoso&task=live';
	
	jQuery(document).ready(function() {
	});

	function update(){
		clearTimeout(timer);
		jQuery.post(ajax,{mode:'update'},ajaxLoad,"application/text");
	}
	
	function ajaxLoad(result)
	{
		result  = result.split("%");
		timeOut = parseInt(result[0]);
		Tmb = parseInt(result[1]); result[4] = parseInt(result[4]);
		Tmt = parseInt(result[2]); result[5] = parseInt(result[5]);
		Tmn = parseInt(result[3]); result[6] = parseInt(result[6]);
		timer = setTimeout("update()",timeOut);
		if(result[4]) jQuery('#xsmb .status').html('Đã kết thúc quay mở thưởng, mời bạn xem kết quả ở trên.').css("color","red"); else 
					  jQuery('#xsmb .status').html('Thời gian đến giờ quay số mở thưởng còn lại').css("color","#333");
		if(result[5]) jQuery('#xsmt .status').html('Đã kết thúc quay mở thưởng, mời bạn xem kết quả ở trên.').css("color","red"); else 
					  jQuery('#xsmt .status').html('Thời gian đến giờ quay số mở thưởng còn lại').css("color","#333");
		if(result[6]) jQuery('#xsmn .status').html('Đã kết thúc quay mở thưởng, mời bạn xem kết quả ở trên.').css("color","red"); else 
					  jQuery('#xsmn .status').html('Thời gian đến giờ quay số mở thưởng còn lại').css("color","#333");
		if(result[7]=='none') return;
		data = result[7].split("###");
		if(result[4]) jQuery("#xsmb-result").html(data[0]); //jQuery("#xsmb-result").html(data[0]);
		if(result[5]) jQuery("#xsmt-result").html(data[1]); //jQuery("#xsmt-result").html(data[1]);
		if(result[6]) jQuery("#xsmn-result").html(data[2]); //jQuery("#xsmn-result").html(data[2]);
	}
	
	function countDown(time)
	{
		time = time - 1000;
		
		var Xhour = Math.floor(time / (1000*60*60));
		var Xmins = Math.floor(time / (1000*60));
		var Xsecs = Math.floor(time / 1000);
		var Xmin  = Xmins - Xhour*60;
		var Xsec  = Xsecs - Xmins*60;
		
		if(Xhour < 0) { Xmin = '00'; } else if (Xmin < 10) { Xmin = '0' + Xmin; }
		if(Xhour < 0) { Xsec = '00'; } else if (Xsec < 10) { Xsec = '0' + Xsec; }
		if(Xhour < 0) { Xhour= '00'; } else if (Xhour< 10) { Xhour= '0' + Xhour;}
		
		return Object({hour:Xhour,min:Xmin,sec:Xsec,time:time});
	}
	
	function updateTimer()
	{
		var mb = countDown(Tmb);	Tmb = mb.time;		
		var mt = countDown(Tmt);	Tmt = mt.time;
		var mn = countDown(Tmn);	Tmn = mn.time; 
		
		document.getElementById("xsmb-hour").innerHTML = mb.hour;
		document.getElementById("xsmb-min" ).innerHTML = mb.min;
		document.getElementById("xsmb-sec" ).innerHTML = mb.sec;
		document.getElementById("xsmt-hour").innerHTML = mt.hour;
		document.getElementById("xsmt-min" ).innerHTML = mt.min;
		document.getElementById("xsmt-sec" ).innerHTML = mt.sec;
		document.getElementById("xsmn-hour").innerHTML = mn.hour;
		document.getElementById("xsmn-min" ).innerHTML = mn.min;
		document.getElementById("xsmn-sec" ).innerHTML = mn.sec;
	}
	interval = setInterval('updateTimer()', 1000 );
</script>

<div class="tab-Lottery">
<ul class="clearfix">
	<li><a href="<?php
			echo sefRelToAbs ( $path );
			?>"><span>Xem Kết quả</span></a></li>
	<li class="active"><a
		href="<?php
			echo sefRelToAbs ( $path . '&task=live' );
			?>"><span>Tường
	thuật trực tiếp</span></a></li>
	<li><a href="<?php
			echo sefRelToAbs ( $path . '&task=open' );
			?>"><span>Lịch
	mở thưởng</span></a></li>
	<li class="last"><a
		href="<?php
			echo sefRelToAbs ( $path . '&task=statistics&from=2010&to=2011&name=1&view=month' );
			?>">
	<span>Thống kê</span></a></li>
</ul>
</div>
<div class="banner-zs"><a href="#"><img
	src="images/v2011/sample/xoso.gif" alt=""></a></div>
<div id="xstt-kq">
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<ul class="nav-per clearfix tabs">
	<li class="first"><a class="active" href="#xsmb-result">Miền Bắc</a></li>
	<li><a href="#xsmt-result">Miền Trung</a></li>
	<li><a href="#xsmn-result">Miền Nam</a></li>
</ul>
</div>
<div class="pr"><strong><i>
                    <?php
			$date = date ( 'd-m-Y' );
			echo fnGetDateEx ( $date, 'vi' ) . ', ngày ' . $date;
			?>
                    </i></strong></div>
</div>
</div>
</div>
</div>
<div class="content-tab xs" id="xsmb-result">
    	<?php
			xsPrintLive ( 'Miền Bắc', $data ['MB'] )?>
     </div>
     	<?php
			if ($mode == 'update')
				echo "###"?>
    <div class="content-tab xs" id="xsmt-result">
    	<?php
			xsPrintLive ( 'Miền Trung', $data ['MT'] )?>
    </div>
    	<?php
			if ($mode == 'update')
				echo "###"?>
	<div class="content-tab xs" id="xsmn-result">
    	<?php
			xsPrintLive ( 'Miền Nam', $data ['MN'] )?>
    </div>
</div>
<div id="xstt-live">
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<ul class="nav-per clearfix tabs">
	<li class="first"><a class="active" href="#xsmb-live">Miền Bắc</a></li>
	<li><a href="#xsmt-live">Miền Trung</a></li>
	<li><a href="#xsmn-live">Miền Nam</a></li>
</ul>
</div>
<div class="pr"><strong><i>
					<?php
			$date = date ( 'd-m-Y' );
			echo fnGetDateEx ( $date, 'vi' ) . ', ngày ' . $date;
			?>                    
                    </i></strong></div>
</div>
</div>
</div>
</div>
<div class="container-tabs">
<div id="xsmb-live" class="content-tab">
		<?php
			xsPrintClock ( 'Miền Bắc' );
			?>
        </div>
<div id="xsmt-live" class="content-tab">
        <?php
			xsPrintClock ( 'Miền Trung' );
			?>
        </div>
<div id="xsmn-live" class="content-tab">
        <?php
			xsPrintClock ( 'Miền Nam' );
			?>
        </div>
</div>
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl"><span class="dial">Lịch quay Xổ số
mở thưởng</span></div>
</div>
</div>
</div>
<div class="xs">
		<?php
			xsPrintCalendar ( $calendar );
			?>
    </div>
</div>
<script type="text/javascript">
/* HỆ THỐNG TAB */
function changeBlock(container)
{
	$(container+" .content-tab").hide();
	$(container+" ul.tabs li:first").addClass("active").show();
	$(container+" .content-tab:first").show();
	
	$(container+" ul.tabs li a").click(function() {
		$(container+" ul.tabs li a").removeClass("active");
		$(this).addClass("active");
		$(container+" .content-tab").hide();
		var active = $(this).attr("href");
		$(active).show();
		return false;
	});
}

changeBlock('#xstt-kq');
changeBlock('#xstt-live');

</script>

<?php
		
		} // if(mode=view)
		

		if ($mode == 'update') :
			//xsPrintResult($data['MB'][0]);
			xsPrintLive ( 'Miền Bắc', $data ['MB'] );
			echo "###";
			xsPrintLive ( 'Miền Trung', $data ['MT'] );
			echo "###";
			xsPrintLive ( 'Miền Nam', $data ['MN'] );
		
	endif;
	
	} //showLive
	

	/* Thống kê
	 * task = statistics; view = tuần / tháng; name = giải xổ số; length = chiều dài số hiển thị*/
	function showStat($view, $name, $fromYear, $toYear, $data, $nameList, $calendar, $length) {
		$pathBase = 'index.php?option=com_xoso';
		$pathModule = $pathBase . '&task=statistics&from=' . $fromYear . '&to=' . $toYear . '&name=' . $name;
		?>

<div class="tab-Lottery">
<ul class="clearfix">
	<li><a href="<?php
		echo sefRelToAbs ( $pathBase );
		?>"><span>Xem Kết quả</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $pathBase . '&task=live' );
		?>"><span>Tường
	thuật trực tiếp</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $pathBase . '&task=open' );
		?>"><span>Lịch
	mở thưởng</span></a></li>
	<li class="active last"><a
		href="<?php
		echo sefRelToAbs ( $pathModule . '&view=month' );
		?>"><span>Thống
	kê</span></a></li>
</ul>
</div>
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl clearfix">
<div class="pl">
<ul class="nav-tk clearfix">
	<li class="item1"><a
		href="<?php
		echo sefRelToAbs ( $pathModule . '&view=month' );
		?>"
		class="<?php
		echo $length == 5 && $view == 'month' ? 'active' : '';
		?> pngFix">Giải
	ĐB theo tháng</a></li>
	<li class="item2"><a
		href="<?php
		echo sefRelToAbs ( $pathModule . '&view=week' );
		?>"
		class="<?php
		echo $length == 5 && $view == 'week' ? 'active' : '';
		?> pngFix">Giải
	ĐB theo tuần</a></li>
	<li class="item3"><a
		href="<?php
		echo sefRelToAbs ( $pathModule . '&view=month&length=2' );
		?>"
		class="<?php
		echo $length == 2 && $view == 'month' ? 'active' : '';
		?> pngFix">2
	số cuối giải ĐB theo tháng</a></li>
	<li class="item4"><a
		href="<?php
		echo sefRelToAbs ( $pathModule . '&view=week&length=2' );
		?>"
		class="<?php
		echo $length == 2 && $view == 'week' ? 'active' : '';
		?> pngFix">2
	số cuối giải ĐB theo tuần</a></li>
</ul>
</div>
</div>
</div>
</div>
</div>
<div class="content-tab">
<div class="date-tk clearfix">
<form name="xsFrom" method="get"
	action="<?php
		echo sefRelToAbs ( 'index.php' );
		?>"><input type="hidden"
	name="option" value="com_xoso"> <input type="hidden" name="task"
	value="statistics"> <span>Từ năm</span> <select name="from"
	class="fromyear">
			<?php
		$curYear = intval ( date ( 'Y' ) );
		for($year = 2007; $year < $curYear + 1; $year ++) {
			$select = ($year == $fromYear) ? ' selected="selected"' : '';
			echo '<option' . $select . '>';
			echo $year;
			echo '</option>';
		}
		?>        
        </select> <span>Đến năm</span> <select name="to" class="toyear">
			<?php
		for($year = 2007; $year < $curYear + 1; $year ++) {
			$select = ($year == $toYear) ? ' selected="selected"' : '';
			echo '<option' . $select . '>';
			echo $year;
			echo '</option>';
		}
		?> 
        </select> <select name="name" id="name" class="xsmb">
			<?php
		if (is_array ( $nameList ))
			foreach ( $nameList as $item ) {
				$title = explode ( ' ', $item->name );
				unset ( $title [0], $title [1] );
				$title = implode ( ' ', $title );
				$select = ($item->id == $name) ? ' selected="selected"' : '';
				echo '<option value="' . $item->id . '" ' . $select . '>&nbsp;&nbsp;';
				echo $title;
				echo '</option>';
			}
		?>
        </select> <input type="hidden" name="view"
	value="<?php
		echo $view;
		?>"> <input type="hidden" name="length"
	value="<?php
		echo $length;
		?>"> <span class="btn-yelow"><input
	type="submit" value="Xem"></span></form>
</div>
<div class="table-wap">
<div class="table-scoll">
<div class="table-w">
				<?php
		for($year = intval ( $toYear ); $year >= $fromYear; $year --) {
			if ($view == 'month')
				xsMonthSpecial ( $year, $data, $length );
			if ($view == 'week')
				xsWeekSpecial ( $year, $data, $length );
		}
		?>
            </div>
</div>
<div class="table-note">
<p class="notebg"><span class="date1">Ngày tết</span><span class="date2">Chủ
nhật</span><span class="date3">Ngày thường</span></p>
<p class="previewpage"><a href="#">Xem Toàn Trang</a></p>
</div>
</div>
</div>
<div class="banner-sx"><a href="#"><img src="images/v2011/sample/sx.gif"
	alt="" height="42" width="670"></a></div>
<div class="presentation present">
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl"><span class="dial">Lịch quay Xổ số
mở thưởng</span></div>
</div>
</div>
</div>
<div class="xs">
		<?php
		xsPrintCalendar ( $calendar );
		?>
    </div>
</div>
<?php
	}
	
	/* Lịch mở thưởng
	 * task = open */
	function showOpen($calendar) {
		$path = 'index.php?option=com_xoso';
		?>
<div class="tab-Lottery">
<ul class="clearfix">
	<li><a href="<?php
		echo sefRelToAbs ( $path );
		?>"><span>Xem Kết quả</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $path . '&task=live' );
		?>"><span>Tường
	thuật trực tiếp</span></a></li>
	<li class="active"><a
		href="<?php
		echo sefRelToAbs ( $path . '&task=open' );
		?>"><span>Lịch mở
	thưởng</span></a></li>
	<li class="last"><a
		href="<?php
		echo sefRelToAbs ( $path . '&task=statistics&from=2010&to=2011&name=1&view=month' );
		?>"><span>Thống
	kê</span></a></li>
</ul>
</div>
<div class="presentation present">
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl"><span class="dial">Lịch quay Xổ số
mở thưởng</span></div>
</div>
</div>
</div>
<div class="xs">
		<?php
		xsPrintCalendar ( $calendar );
		?>
    </div>
</div>
<?php
	
	} //showOpen
	

	/* Hiển thị kết quả theo ngày / tỉnh
	 * task = view */
	function showView($mode, $data, $calendar) {
		$path = 'index.php?option=com_xoso';
		?>
<form style="display: none" name="xs" action="#"><input type="hidden"
	name="date" id="date"
	value="<?php
		if (isset ( $_REQUEST ['date'] ))
			echo $_REQUEST ['date'];
		?>" />
</form>
<div class="tab-Lottery">
<ul class="clearfix">
	<li class="active"><a href="<?php
		echo sefRelToAbs ( $path );
		?>"><span>Xem
	Kết quả</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $path . '&task=live' );
		?>"><span>Tường
	thuật trực tiếp</span></a></li>
	<li><a href="<?php
		echo sefRelToAbs ( $path . '&task=open' );
		?>"><span>Lịch
	mở thưởng</span></a></li>
	<li class="last"><a
		href="<?php
		echo sefRelToAbs ( $path . '&task=statistics&from=2010&to=2011&name=1&view=month' );
		?>"><span>Thống
	kê</span></a></li>
</ul>
</div>
<div class="banner-zs"><a href="#"><img
	src="images/v2011/sample/xoso.gif" alt=""></a></div>
<?php
		foreach ( $data as $k => $item ) {
			xsPrintResult ( $item, 1 );
			?>
<div class="banner-xs"><a href="#"><img
	src="images/v2011/sample/xs1.gif" alt="" width="670" height="60"></a></div>
<?php
		} //end foreach
		?>
<div class="presentation">
<div class="tab-Province">
<div class="tab-Provincel">
<div class="tab-Provincer">
<div class="list-province provincerl"><span class="dial">Lịch quay Xổ số
mở thưởng</span></div>
</div>
</div>
</div>
<div class="xs">
		<?php
		xsPrintCalendar ( $calendar );
		?>
    </div>
</div>
<?php
	} //showView


} //JXoso


/* Các hàm xử lý ngày tháng 
 * Và chuyển đổi Âm lịch <=> Dương lịch 
 */
function jdFromDate($dd, $mm, $yy) {
	$a = floor ( (14 - $mm) / 12 );
	$y = $yy + 4800 - $a;
	$m = $mm + 12 * $a - 3;
	$jd = $dd + floor ( (153 * $m + 2) / 5 ) + 365 * $y + floor ( $y / 4 ) - floor ( $y / 100 ) + floor ( $y / 400 ) - 32045;
	if ($jd < 2299161) {
		$jd = $dd + floor ( (153 * $m + 2) / 5 ) + 365 * $y + floor ( $y / 4 ) - 32083;
	}
	return $jd;
}
function jdToDate($jd) {
	if ($jd > 2299160) { // After 5/10/1582, Gregorian calendar
		$a = $jd + 32044;
		$b = floor ( (4 * $a + 3) / 146097 );
		$c = $a - floor ( ($b * 146097) / 4 );
	} else {
		$b = 0;
		$c = $jd + 32082;
	}
	$d = floor ( (4 * $c + 3) / 1461 );
	$e = $c - floor ( (1461 * $d) / 4 );
	$m = floor ( (5 * $e + 2) / 153 );
	$day = $e - floor ( (153 * $m + 2) / 5 ) + 1;
	$month = $m + 3 - 12 * floor ( $m / 10 );
	$year = $b * 100 + $d - 4800 + floor ( $m / 10 );
	//echo "day = $day, month = $month, year = $year\n";
	return array ($day, $month, $year );
}
function getNewMoonDay($k, $timeZone) {
	$T = $k / 1236.85; // Time in Julian centuries from 1900 January 0.5
	$T2 = $T * $T;
	$T3 = $T2 * $T;
	$dr = M_PI / 180;
	$Jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $T2 - 0.000000155 * $T3;
	$Jd1 = $Jd1 + 0.00033 * sin ( (166.56 + 132.87 * $T - 0.009173 * $T2) * $dr ); // Mean new moon
	$M = 359.2242 + 29.10535608 * $k - 0.0000333 * $T2 - 0.00000347 * $T3; // Sun's mean anomaly
	$Mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $T2 + 0.00001236 * $T3; // Moon's mean anomaly
	$F = 21.2964 + 390.67050646 * $k - 0.0016528 * $T2 - 0.00000239 * $T3; // Moon's argument of latitude
	$C1 = (0.1734 - 0.000393 * $T) * sin ( $M * $dr ) + 0.0021 * sin ( 2 * $dr * $M );
	$C1 = $C1 - 0.4068 * sin ( $Mpr * $dr ) + 0.0161 * sin ( $dr * 2 * $Mpr );
	$C1 = $C1 - 0.0004 * sin ( $dr * 3 * $Mpr );
	$C1 = $C1 + 0.0104 * sin ( $dr * 2 * $F ) - 0.0051 * sin ( $dr * ($M + $Mpr) );
	$C1 = $C1 - 0.0074 * sin ( $dr * ($M - $Mpr) ) + 0.0004 * sin ( $dr * (2 * $F + $M) );
	$C1 = $C1 - 0.0004 * sin ( $dr * (2 * $F - $M) ) - 0.0006 * sin ( $dr * (2 * $F + $Mpr) );
	$C1 = $C1 + 0.0010 * sin ( $dr * (2 * $F - $Mpr) ) + 0.0005 * sin ( $dr * (2 * $Mpr + $M) );
	if ($T < - 11) {
		$deltat = 0.001 + 0.000839 * $T + 0.0002261 * $T2 - 0.00000845 * $T3 - 0.000000081 * $T * $T3;
	} else {
		$deltat = - 0.000278 + 0.000265 * $T + 0.000262 * $T2;
	}
	;
	$JdNew = $Jd1 + $C1 - $deltat;
	//echo "JdNew = $JdNew\n";
	return floor ( $JdNew + 0.5 + $timeZone / 24 );
}
function getSunLongitude($jdn, $timeZone) {
	$T = ($jdn - 2451545.5 - $timeZone / 24) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
	$T2 = $T * $T;
	$dr = M_PI / 180; // degree to radian
	$M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2; // mean anomaly, degree
	$L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2; // mean longitude, degree
	$DL = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin ( $dr * $M );
	$DL = $DL + (0.019993 - 0.000101 * $T) * sin ( $dr * 2 * $M ) + 0.000290 * sin ( $dr * 3 * $M );
	$L = $L0 + $DL; // true longitude, degree
	//echo "\ndr = $dr, M = $M, T = $T, DL = $DL, L = $L, L0 = $L0\n";
	// obtain apparent longitude by correcting for nutation and aberration
	$omega = 125.04 - 1934.136 * $T;
	$L = $L - 0.00569 - 0.00478 * sin ( $omega * $dr );
	$L = $L * $dr;
	$L = $L - M_PI * 2 * (floor ( $L / (M_PI * 2) )); // Normalize to (0, 2*PI)
	return floor ( $L / M_PI * 6 );
}
function getLunarMonth11($yy, $timeZone) {
	$off = jdFromDate ( 31, 12, $yy ) - 2415021;
	$k = floor ( $off / 29.530588853 );
	$nm = getNewMoonDay ( $k, $timeZone );
	$sunLong = getSunLongitude ( $nm, $timeZone ); // sun longitude at local midnight
	if ($sunLong >= 9) {
		$nm = getNewMoonDay ( $k - 1, $timeZone );
	}
	return $nm;
}
function getLeapMonthOffset($a11, $timeZone) {
	$k = floor ( ($a11 - 2415021.076998695) / 29.530588853 + 0.5 );
	$last = 0;
	$i = 1; // We start with the month following lunar month 11
	$arc = getSunLongitude ( getNewMoonDay ( $k + $i, $timeZone ), $timeZone );
	do {
		$last = $arc;
		$i = $i + 1;
		$arc = getSunLongitude ( getNewMoonDay ( $k + $i, $timeZone ), $timeZone );
	} while ( $arc != $last && $i < 14 );
	return $i - 1;
}
/* Dương lịch -> Âm lịch
Comvert solar date dd/mm/yyyy to the corresponding lunar date 
convertSolar2Lunar($dd, $mm, $yy, 7.0);
*/
function convertSolar2Lunar($dd, $mm, $yy, $timeZone) {
	$dayNumber = jdFromDate ( $dd, $mm, $yy );
	$k = floor ( ($dayNumber - 2415021.076998695) / 29.530588853 );
	$monthStart = getNewMoonDay ( $k + 1, $timeZone );
	if ($monthStart > $dayNumber) {
		$monthStart = getNewMoonDay ( $k, $timeZone );
	}
	$a11 = getLunarMonth11 ( $yy, $timeZone );
	$b11 = $a11;
	if ($a11 >= $monthStart) {
		$lunarYear = $yy;
		$a11 = getLunarMonth11 ( $yy - 1, $timeZone );
	} else {
		$lunarYear = $yy + 1;
		$b11 = getLunarMonth11 ( $yy + 1, $timeZone );
	}
	$lunarDay = $dayNumber - $monthStart + 1;
	$diff = floor ( ($monthStart - $a11) / 29 );
	$lunarLeap = 0;
	$lunarMonth = $diff + 11;
	if ($b11 - $a11 > 365) {
		$leapMonthDiff = getLeapMonthOffset ( $a11, $timeZone );
		if ($diff >= $leapMonthDiff) {
			$lunarMonth = $diff + 10;
			if ($diff == $leapMonthDiff) {
				$lunarLeap = 1;
			}
		}
	}
	if ($lunarMonth > 12) {
		$lunarMonth = $lunarMonth - 12;
	}
	if ($lunarMonth >= 11 && $diff < 4) {
		$lunarYear -= 1;
	}
	return array ($lunarDay, $lunarMonth, $lunarYear, $lunarLeap );
}
/* Âm lịch => Dương lịch
Convert a lunar date to the corresponding solar date 
convertLunar2Solar($dd, $mm, $yy, 0, 7.0);
*/
function convertLunar2Solar($lunarDay, $lunarMonth, $lunarYear, $lunarLeap, $timeZone) {
	if ($lunarMonth < 11) {
		$a11 = getLunarMonth11 ( $lunarYear - 1, $timeZone );
		$b11 = getLunarMonth11 ( $lunarYear, $timeZone );
	} else {
		$a11 = getLunarMonth11 ( $lunarYear, $timeZone );
		$b11 = getLunarMonth11 ( $lunarYear + 1, $timeZone );
	}
	$k = floor ( 0.5 + ($a11 - 2415021.076998695) / 29.530588853 );
	$off = $lunarMonth - 11;
	if ($off < 0) {
		$off += 12;
	}
	if ($b11 - $a11 > 365) {
		$leapOff = getLeapMonthOffset ( $a11, $timeZone );
		$leapMonth = $leapOff - 2;
		if ($leapMonth < 0) {
			$leapMonth += 12;
		}
		if ($lunarLeap != 0 && $lunarMonth != $leapMonth) {
			return array (0, 0, 0 );
		} else if ($lunarLeap != 0 || $off >= $leapOff) {
			$off += 1;
		}
	}
	$monthStart = getNewMoonDay ( $k + $off, $timeZone );
	return jdToDate ( $monthStart + $lunarDay - 1 );
}
function fnGetLunar($day, $month, $year) {
	return convertSolar2Lunar ( $day, $month, $year, 7.0 );
}
function fnGetLunarEx($date) {
	$date = str_replace ( '-', '/', $date );
	$date = explode ( '/', $date );
	if (strlen ( $date [0] ) == 4) {
		$day = $date [2];
		$month = $date [1];
		$year = $date [0];
	
	}
	if (strlen ( $date [2] ) == 4) {
		$day = $date [0];
		$month = $date [1];
		$year = $date [2];
	
	}
	return getLunar ( $day, $month, $year );
}
function fnGetSolar($lunarDay, $lunarMonth, $lunarYear) {
	return convertLunar2Solar ( $lunarDay, $lunarMonth, $lunarYear, 0, 7.0 );
}
function fnGetSolarEx($date) {
	$date = str_replace ( '-', '/', $date );
	$date = explode ( '/', $date );
	if (strlen ( $date [0] ) == 4) {
		$day = $date [2];
		$month = $date [1];
		$year = $date [0];
	
	}
	if (strlen ( $date [2] ) == 4) {
		$day = $date [0];
		$month = $date [1];
		$year = $date [2];
	
	}
	return getSolar ( $day, $month, $year );
}
function fnGetDate($day, $month, $year, $language = 'en') {
	$lang ['en'] = array ("Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun", "am", "pm", ":" );
	$lang ['vi'] = array ("Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy", "Chủ nhật", "sáng", "chiều", ":" );
	$date = date ( "D", mktime ( 0, 0, 0, $month, $day, $year ) );
	return str_replace ( $lang ['en'], $lang [$language], $date );
}
function fnGetDateEx($date, $lang = 'en') {
	$date = str_replace ( '-', '/', $date );
	$date = explode ( '/', $date );
	if (strlen ( $date [0] ) == 4) {
		$day = $date [2];
		$month = $date [1];
		$year = $date [0];
	
	}
	if (strlen ( $date [2] ) == 4) {
		$day = $date [0];
		$month = $date [1];
		$year = $date [2];
	
	}
	return fnGetDate ( $day, $month, $year, $lang );
}
function fnGetWeek($day, $month, $year) {
	$week = date ( 'W', mktime ( 0, 0, 0, $month, $day, $year ) );
	$week = intval ( $week ) + 1;
	if ($week > 53)
		$week = 1;
	if (intval ( $month ) == 1 && $week == 53)
		$week = 1;
	return $week;
}
function fnGetWeekEx($date) {
	$date = str_replace ( '-', '/', $date );
	$date = explode ( '/', $date );
	if (strlen ( $date [0] ) == 4) {
		$day = $date [2];
		$month = $date [1];
		$year = $date [0];
	
	}
	if (strlen ( $date [2] ) == 4) {
		$day = $date [0];
		$month = $date [1];
		$year = $date [2];
	
	}
	return fnGetWeek ( $day, $month, $year );
}
function fnGetTet($year) {
	$solar = array ();
	$d01 = fnGetSolar ( '01', '01', $year );
	$d30 = $d01;
	$d30 [0] = $d01 [0] - 1;
	$d02 = fnGetSolar ( '02', '01', $year );
	$d03 = fnGetSolar ( '03', '01', $year );
	$solar [0] = sprintf ( '%02d-%02d-%d', $d30 [0], $d30 [1], $d30 [2] );
	$solar [1] = sprintf ( '%02d-%02d-%d', $d01 [0], $d01 [1], $d01 [2] );
	$solar [2] = sprintf ( '%02d-%02d-%d', $d02 [0], $d02 [1], $d02 [2] );
	$solar [3] = sprintf ( '%02d-%02d-%d', $d03 [0], $d03 [1], $d03 [2] );
	return $solar;
}
# Tính số ngày trong một tuần
function getDaysInWeek($weekNumber, $year) {
	# Count from '0104' because January 4th is always in week 1
	# (according to ISO 8601).
	$time = strtotime ( $year . '0104 +' . ($weekNumber - 1) . ' weeks' );
	# Get the time of the first day of the week
	$mondayTime = strtotime ( '-' . (date ( 'w', $time ) - 1) . ' days', $time );
	# Get the times of days 0 -> 6
	$dayTimes = array ();
	for($i = 0; $i < 7; ++ $i) {
		$dayTimes [] = strtotime ( '+' . $i . ' days', $mondayTime );
	}
	# Return timestamps for mon-sun.
	return $dayTimes;
}
# Tính ngày tháng từ Tuần & Thứ = 2-8
function fnGetDateWeek($week, $day, $year) {
	$week = ( int ) $week;
	$day = ( int ) $day;
	$year = ( int ) $year;
	$list = array ("Mon" => 2, "Tue" => 3, "Wed" => 4, "Thu" => 5, "Fri" => 6, "Sat" => 7, "Sun" => 8 );
	# Tính $time của ngày 1/1 vì luôn là tuần 1
	$time = strtotime ( $year . '0101 +' . ($week - 2) . ' weeks' );
	# Tính thứ của ngày 01/01 dạng chữ - Fri
	$date = date ( "D", mktime ( 0, 0, 0, 1, 1, $year ) );
	# Tính thứ của ngày 01/01 dạng số - 6
	$date = $list [$date];
	# Tính ngày tháng của ngày hiện tại.
	$date = strtotime ( '+' . ($day - $date) . ' days', $time );
	return strftime ( '%d-%m-%Y', $date );
}
?>