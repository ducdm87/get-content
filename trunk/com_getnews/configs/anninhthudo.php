<?php
// duyet tung category. Danh sach bai viet theo moi ngay. ngay duoc xac dinh la ngay cua bai viet cuoi cung trong danh sach
// link run: http://domain.com/index.php?option=com_getnewsvov&task=getnews&host=anninhthudo
// start auto_increment	:	175.124.353
// STATE: FINISH
/**
 * Lui ngay toi da den ngay nay.
 */
$date_started	=	'01/04/2007';
/**
 * *	thoi gian cho moi lan chay lay bai
 */
$time_exp	=	15;
/**
 * - true: lấy lại bài viết
 * - false: bỏ qua bài viết đã lấy
 */
$get_existing	= false;
/**
 * - true: lấy nhiều category. sử dụng cho lần chạy đầu tiên. lấy vét cạn
 * - false: chỉ lấy bài viết mới của category chạy sớm nhất. khi đặt cronjob
 */
$get_multicat		= true;

$getold				= true;
/**
 * : 	đường dẫn đến thư mục ảnh
 */
$path_image		=	'images/antd175';
/**
 * địa chỉ tới nơi chứa ảnhvd
 */
$link_image		=	'/images/antd175';
/**
 * 
 */
$SiteID 		=	'antd175';